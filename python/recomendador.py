import pandas as pd
from sklearn.neighbors import NearestNeighbors
from sklearn.feature_extraction.text import TfidfVectorizer
import nltk
import sys
import os
import logging
import json
import pymysql
import random
from datetime import datetime

pymysql.install_as_MySQLdb()
logging.getLogger('nltk.downloader').setLevel(logging.ERROR)

try:
    nltk.data.find('corpora/stopwords')
except LookupError:
    nltk.download('stopwords', quiet=True)

stopwords_pt = nltk.corpus.stopwords.words('portuguese')

user_id = int(sys.argv[1])

user = 'root'
password = ''
host = '192.168.1.10'
database = 'lojashenri'
port = 3306

conexao_str = f"mysql+pymysql://{user}:{password}@{host}:{port}/{database}"

BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
output_txt_path = os.path.join(BASE_DIR, 'storage', 'app', f'recomendados_user_{user_id}.txt')

# Produtos com estoque
produtos_df = pd.read_sql("""
    SELECT id, nome, descricao, categoria, estoque
    FROM produtos
    WHERE estoque > 0
""", conexao_str)

produtos_df['texto'] = (
    produtos_df['nome'].fillna('') + ' ' +
    produtos_df['descricao'].fillna('') + ' ' +
    produtos_df['categoria'].fillna('')
)

vectorizer = TfidfVectorizer(stop_words=stopwords_pt, max_features=10000)
tfidf_matrix = vectorizer.fit_transform(produtos_df['texto'])

id_para_indice = {pid: idx for idx, pid in enumerate(produtos_df['id'].astype(int))}

# Interações do usuário
interacoes_df = pd.read_sql(f"""
    SELECT produto_id, tipo, created_at
    FROM user_interactions
    WHERE user_id = {user_id}
""", conexao_str)

# Avaliações feitas pelo usuário
avaliacoes_df = pd.read_sql(f"""
    SELECT produto_id, nota
    FROM avaliacoes
    WHERE user_id = {user_id}
""", conexao_str)

# Nota média global
media_avaliacoes_df = pd.read_sql("""
    SELECT produto_id, AVG(nota) AS media_nota
    FROM avaliacoes
    GROUP BY produto_id
""", conexao_str).set_index('produto_id')

# Peso base
peso_tipo = {'comprou': 3, 'visualizou': 1}
interacoes_df['peso_base'] = interacoes_df['tipo'].map(peso_tipo).fillna(0)

# Decaimento por tempo
now = datetime.now()
interacoes_df['dias'] = (now - pd.to_datetime(interacoes_df['created_at'])).dt.days.clip(lower=0).fillna(0)
interacoes_df['decaimento'] = interacoes_df['dias'].apply(lambda d: 1 / (1 + d))
interacoes_df['peso'] = interacoes_df['peso_base'] * interacoes_df['decaimento']

# Junta com nota dada pelo usuário
interacoes_df = interacoes_df.merge(avaliacoes_df, on='produto_id', how='left')

# Mais peso para nota alta do usuário
def ajuste_nota_usuario(nota):
    if pd.isna(nota):
        return 1.0
    elif nota <= 2:
        return 0.5
    elif nota == 3:
        return 1.0
    elif nota == 4:
        return 1.4
    elif nota >= 5:
        return 1.8

interacoes_df['ajuste_nota'] = interacoes_df['nota'].apply(ajuste_nota_usuario)
interacoes_df['peso'] *= interacoes_df['ajuste_nota']

# Soma peso final por produto
peso_por_produto = interacoes_df.groupby('produto_id')['peso'].sum().reset_index()

# Nota média global ajustada
def ajuste_media_global(media):
    if pd.isna(media):
        return 1.0
    elif media <= 2.0:
        return 0.4
    elif media <= 3.5:
        return 0.8
    elif media >= 4.7:
        return 1.7
    elif media >= 4.2:
        return 1.3
    else:
        return 1.0

peso_por_produto['media_nota'] = peso_por_produto['produto_id'].map(media_avaliacoes_df['media_nota'])
peso_por_produto['ajuste_media'] = peso_por_produto['media_nota'].apply(ajuste_media_global)
peso_por_produto['peso_final'] = peso_por_produto['peso'] * peso_por_produto['ajuste_media']
peso_por_produto = peso_por_produto.set_index('produto_id')['peso_final']

produtos_usuario = set(peso_por_produto.index)

# Treina modelo
model = NearestNeighbors(metric='cosine', algorithm='brute')
model.fit(tfidf_matrix)

produtos_proximos = []

if not peso_por_produto.empty:
    total_interagidos = len(produtos_usuario)
    for pid, peso in peso_por_produto.items():
        if pid not in id_para_indice:
            continue
        idx = id_para_indice[pid]
        n_vizinhos = min(total_interagidos + 9, tfidf_matrix.shape[0])
        distances, indices = model.kneighbors(tfidf_matrix[idx], n_neighbors=n_vizinhos)

        for sim_idx, dist in zip(indices[0][1:], distances[0][1:]):
            sim_id = int(produtos_df.iloc[sim_idx]['id'])
            if sim_id in produtos_usuario:
                continue
            if sim_id not in [p[0] for p in produtos_proximos]:
                # Aplica ajuste baseado na nota global do produto candidato
                media_sim = media_avaliacoes_df['media_nota'].get(sim_id, 3.0)
                bonus_sim = ajuste_media_global(media_sim)
                dist_ajustada = (dist / peso) / bonus_sim
                produtos_proximos.append((sim_id, dist_ajustada))
            if len(produtos_proximos) >= 30:
                break

# Ordena pela distância ajustada
filtrados = {}
for pid, dist in produtos_proximos:
    if pid not in filtrados or dist < filtrados[pid]:
        filtrados[pid] = dist

ordenados = sorted(filtrados.items(), key=lambda x: x[1])
recomendados = [pid for pid, _ in ordenados][:9]

# Preenche com aleatórios se faltar
produtos_disponiveis = set(produtos_df['id'].astype(int))
restantes = list(produtos_disponiveis - set(recomendados) - produtos_usuario)
random.shuffle(restantes)

while len(recomendados) < 9 and restantes:
    recomendados.append(restantes.pop())

# Salva resultado
with open(output_txt_path, 'w') as f:
    for pid in recomendados:
        f.write(f"{pid}\n")

print(json.dumps(recomendados))
