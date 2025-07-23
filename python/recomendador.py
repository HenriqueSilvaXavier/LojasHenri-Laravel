import pandas as pd
from sklearn.neighbors import NearestNeighbors
from sklearn.feature_extraction.text import TfidfVectorizer
import nltk
import sys
import json
import os
import logging
import numpy as np

# Silencia logs do nltk downloader
logging.getLogger('nltk.downloader').setLevel(logging.ERROR)

try:
    nltk.data.find('corpora/stopwords')
except LookupError:
    nltk.download('stopwords', quiet=True)

from nltk.corpus import stopwords
stopwords_pt = stopwords.words('portuguese')

# Define diretório base do projeto Laravel (um nível acima da pasta 'python')
BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
storage_dir = os.path.join(BASE_DIR, 'storage', 'app')

# Captura o user_id passado via CLI
user_id = int(sys.argv[1])

# Caminhos absolutos dos arquivos
interacoes_path = os.path.join(storage_dir, 'dados_interacoes.csv')
produtos_path = os.path.join(storage_dir, 'produtos_completos.csv')
output_txt_path = os.path.join(storage_dir, f'recomendados_user_{user_id}.txt')

# Carrega dados
try:
    dados = pd.read_csv(interacoes_path)
    produtos_df = pd.read_csv(produtos_path)
except:
    print(json.dumps([]))
    sys.exit(0)

# Prepara campo textual unificado
produtos_df['texto'] = (
    produtos_df['nome'].fillna('') + ' ' +
    produtos_df['descricao'].fillna('') + ' ' +
    produtos_df['categoria'].fillna('')
)

# Vetorização TF-IDF com stopwords em português
vectorizer = TfidfVectorizer(stop_words=stopwords_pt, max_features=10000)
tfidf_matrix = vectorizer.fit_transform(produtos_df['texto'])

# Mapeia IDs para índices na matriz
id_para_indice = {pid: idx for idx, pid in enumerate(produtos_df['id'].astype(int))}

# Filtra interações do usuário
dados_usuario = dados[dados['user_id'] == user_id]

if dados_usuario.empty:
    print(json.dumps([]))
    sys.exit(0)

# Mapear tipo de interação para peso
peso_tipo = {
    'comprou': 3,
    'visualizou': 1
}

# Soma de pesos por produto
dados_usuario['peso'] = dados_usuario['tipo'].map(peso_tipo).fillna(0)
peso_por_produto = dados_usuario.groupby('produto_id')['peso'].sum()

produtos_usuario = peso_por_produto.index.values
pesos_usuario = peso_por_produto.values

# Treina modelo de vizinhos
model_text = NearestNeighbors(metric='cosine', algorithm='brute')
model_text.fit(tfidf_matrix)

produtos_proximos = []

for pid, peso in zip(produtos_usuario, pesos_usuario):
    if pid not in id_para_indice:
        continue
    idx = id_para_indice[pid]
    distances, indices = model_text.kneighbors(tfidf_matrix[idx], n_neighbors=7)
    similares_idx = indices[0][1:]  # ignora ele mesmo
    similares_dist = distances[0][1:]
    for sim_idx, dist in zip(similares_idx, similares_dist):
        sim_id = int(produtos_df.iloc[sim_idx]['id'])
        produtos_proximos.append((sim_id, dist / peso))

# Remove produtos já vistos e mantém menor distância ponderada
produtos_filtrados = {}
for pid, dist_pond in produtos_proximos:
    if pid in produtos_usuario:
        continue
    if pid not in produtos_filtrados or dist_pond < produtos_filtrados[pid]:
        produtos_filtrados[pid] = dist_pond

# Ordena pelas menores distâncias ponderadas
produtos_ordenados = sorted(produtos_filtrados.items(), key=lambda x: x[1])

# Garante que o produto ainda existe no CSV
# Garante que o produto ainda existe no CSV E tem estoque > 0
produtos_df['estoque'] = produtos_df['estoque'].fillna(0).astype(int)
produtos_validos = set(produtos_df[produtos_df['estoque'] > 0]['id'].astype(int))
recomendados_filtrados = []

for pid, _ in produtos_ordenados:
    if pid in produtos_validos:
        recomendados_filtrados.append(pid)
    if len(recomendados_filtrados) == 9:
        break

# Salva recomendações em .txt
with open(output_txt_path, 'w') as f:
    for pid in recomendados_filtrados:
        f.write(f"{pid}\n")

# Também imprime os resultados em JSON (útil para debug ou uso via stdout)
print(json.dumps([int(x) for x in recomendados_filtrados]))
