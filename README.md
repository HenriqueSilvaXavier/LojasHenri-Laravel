# 1. Passo a passo para rodar o projeto: 

## 1.1 Instalar o XAMPP

Instale o XAMPP [nesse link](https://www.apachefriends.org/pt_br/index.html) e depois de tê-lo instalado, certifique-se de rodar o Apache e o MySQL com o XAMPP aberto.

## 1.2 Comandos no terminal

No terminal Powershell, execute:

```bash
git clone https://github.com/HenriqueSilvaXavier/LojasHenri-Laravel.git
composer install
npm install
npm run build
```
## 1.3 Criação do .env

Em seguida, crie um .env com o conteúdo: 
```bash
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:Z60hkd2zoAbUyKEFEFeMbGACf6xBliBwGq9eRX1MpIk=
APP_DEBUG=true
APP_URL=http://localhost

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
# APP_MAINTENANCE_STORE=database

PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

HASH_DRIVER=bcrypt

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lojashenri
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync

CACHE_DRIVER=database

# CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"

```

Feito isso, execute:

```bash

```

## 1.4 Caso o projeto utilize alguma parte com Python e NLTK (como recomendações ou processamento de texto), siga os passos abaixo:


**Baixe os recursos necessários:**
Abra o terminal Python ou insira num script:

```python
import nltk
nltk.download('stopwords')
nltk.download('punkt')
```

Esses pacotes são importantes para análise de texto e remoção de palavras irrelevantes.

Se precisar de ajuda com o ambiente Python, avise!

# 2. Como acessar o site pelo celular

Para visualizar o site no seu celular ou em qualquer outros dispositivos, siga os seguintes passos:

## 2.1 Acesse o terminal do MySQL

No PowerShell ou CMD do seu PC, rode:

```bash
& "C:\xampp\mysql\bin\mysql.exe" -u root -p

(Digite a senha do root ou apenas pressione ENTER se não tiver senha.)
```

---

## 2.2 Verifique se o usuário root está liberado para o IP do celular

No terminal do MySQL, digite:

```bash
SELECT Host, User FROM mysql.user WHERE User = 'root';
```

Você deve ver:

| Host             | User |
|------------------|------|
| 192.168.1.10     | root |


---

## 2.3 Conceda permissão ao IP do seu celular

Se não aparecer, conceda o acesso com:

```bash
GRANT ALL PRIVILEGES ON *.* TO 'root'@'192.168.1.10' IDENTIFIED BY '' WITH GRANT OPTION;
FLUSH PRIVILEGES;

> Substitua 192.168.1.10 pelo IP do seu celular (você pode descobrir nas configurações de Wi-Fi).
```



---

## 2.4 (Se necessário) Recrie o usuário root com IP liberado

```bash
DROP USER 'root'@'192.168.1.10';
CREATE USER 'root'@'192.168.1.10' IDENTIFIED BY '';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'192.168.1.10' WITH GRANT OPTION;
FLUSH PRIVILEGES;
```

---

## ✅ 2.5 Permita que o MariaDB aceite conexões externas

🔧 Edite o arquivo my.ini

Local: C:\xampp\mysql\bin\my.ini

Procure por:

```dotenv
bind-address=127.0.0.1
```

E substitua por:

```dotenv
bind-address=0.0.0.0
```

> Isso permite conexões de fora da máquina.




---

🔁 Reinicie o MySQL no XAMPP

Abra o painel do XAMPP:

Clique em Stop no MySQL

Depois clique em Start



---

## ✅ 2.6 Configure o Laravel para usar o IP do PC

No arquivo .env do projeto Laravel (no celular ou onde ele estiver rodando):

```dotenv
DB_CONNECTION=mysql
DB_HOST=192.168.1.100   # IP do seu PC
DB_PORT=3306
DB_DATABASE=lojas_henri
DB_USERNAME=root
DB_PASSWORD=
```

> Descubra o IP do seu PC rodando ipconfig no terminal e pegando o "Endereço IPv4".




---

## ✅ 2.7 Libere a porta 3306 no Firewall (Windows)

1. Pressione Win + R → digite wf.msc → Enter


2. Vá em Regras de Entrada


3. Crie uma nova regra:

Tipo: Porta

Protocolo: TCP

Porta: 3306

Permitir conexão

Perfil: Domínio, Privado, Público

Nome: Liberar MySQL





---

## ✅ 2.8 Inicie o Laravel com acesso de rede

No terminal do Laravel (no PC ou celular via Termux), rode:

php artisan serve --host=0.0.0.0 --port=8000


---

## ✅ 2.9 Acesse o projeto Laravel pelo celular

No navegador do celular, acesse:

http://127.0.0.1:8000

> Substitua 127.0.0.1 pelo IP real do seu PC.


