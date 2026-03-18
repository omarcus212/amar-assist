# 🛒 Sistema de Gestão de Produtos e Usuários

> ⏱️ **Tempo de desenvolvimento:** Aprox. 20 horas.

Sistema completo para gerenciamento de produtos e usuários, desenvolvido com arquitetura SPA (Single Page Application) utilizando **Laravel** como API RESTful e **Vue.js** no frontend. O ambiente de banco de dados é containerizado com **Docker**.

## 🚀 Tecnologias Utilizadas

<div style="display: inline_block">
  <img align="center" alt="Laravel" height="30" width="40" src="https://raw.githubusercontent.com/devicons/devicon/master/icons/laravel/laravel-plain.svg">
  <img align="center" alt="Vue" height="30" width="40" src="https://raw.githubusercontent.com/devicons/devicon/master/icons/vuejs/vuejs-original.svg">
  <img align="center" alt="MySQL" height="30" width="40" src="https://raw.githubusercontent.com/devicons/devicon/master/icons/mysql/mysql-original.svg">
  <img align="center" alt="Docker" height="30" width="40" src="https://raw.githubusercontent.com/devicons/devicon/master/icons/docker/docker-original.svg">
</div>
<br>

- **Backend:** Laravel 11 (API REST)
- **Frontend:** Vue.js 3 + Vite
- **Banco de Dados:** MySQL 8.0 (via Docker)
- **Autenticação:** Laravel Sanctum (Token Bearer)
- **Estilização:** CSS Scoped (Componentes customizados)

## 📋 Funcionalidades Principais

### 🔐 Autenticação e Segurança

- Login com Token JWT (Sanctum).
- Proteção de rotas no Frontend e Backend.
- Logout seguro.

### 📦 Gerenciamento de Produtos

- **CRUD Completo:** Criar, Listar, Editar e Inativar.
- **Upload de Imagens:** Suporte para múltiplas imagens (Galeria).
- **Regras de Negócio:**
  - Preço de venda deve ser no mínimo o Custo + 10%.
  - Validação de tipos de arquivo (JPG/PNG).
- **Filtros:** Busca por título, status e faixa de preço.
- **Paginação:** Listagem otimizada.

### 👥 Gerenciamento de Usuários

- Criação e edição de usuários para acesso ao sistema.
- Listagem e inativação de usuários.

---

## 🔧 Guia de Instalação e Configuração

Siga os passos abaixo para rodar o projeto localmente.

### 1. Clonar o Repositório

```bash
git clone https://github.com/omarcus212/amar-assist
cd amar-assist
```

### 2.1. Configurar o ambiente

Crie a .env na raiz de api:

```bash
cp .env.example .env
# Copie o arquivo de ambiente

# Configure as credenciais do banco no .env:
# DB_HOST=127.0.0.1
# DB_PORT=3307
# DB_DATABASE=product_db
# DB_USERNAME=root
# DB_PASSWORD=root

```

Edite o arquivo api/.env do projeto com as credenciais do banco que serão usadas no docker-compose.yml:

```bash
# api/.env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=nome_do_banco
DB_USERNAME=usuario
DB_PASSWORD=senha
```

### 2. Configure o Docker (MySQL)

```bash
# Suba o container do MySQL
docker-compose up -d

# Aguarde 15 segundos para o MySQL inicializar
```

### 3. Configure o Backend (Laravel)

```bash
# Entre na pasta da API
cd api

# Instale as dependências
composer install

# Gere a chave da aplicação
php artisan key:generate

# Crie o link simbólico para storage
php artisan storage:link

# Execute as migrations
php artisan migrate

# Execute os seeders (cria usuário padrão)
php artisan db:seed

# Inicie o servidor
php artisan serve
```

**API estará rodando em:** http://localhost:8000

### 4. Configure o Frontend (Vue)

```bash
# Abra outro terminal e entre na pasta frontend
cd frontend

# Instale as dependências
npm install

# Inicie o servidor de desenvolvimento
npm run dev
```

**Frontend estará rodando em:** http://localhost:5173

## 👤 Usuário Padrão

Após rodar os seeders, um usuário administrador será criado:

- **Email**: admin@admin.com
- **Senha**: admin123

## 🗄️ Estrutura do Banco de Dados

### Tabela `users`

- Gerenciamento de usuários do sistema
- Campo `active` para inativação

### Tabela `products`

- Cadastro de produtos
- Campos: título, preço, custo, descrição
- Relacionamento com usuário criador/editor
- Soft delete implementado

### Tabela `product_images`

- Múltiplas imagens por produto
- Campo `order` para ordenação

### Tabela `product_logs`

- Log de auditoria de todas as operações
- Registra ações: created, updated, deleted, etc.
- Armazena dados antigos e novos em JSON

## ✅ Validações Implementadas

### Produto

- ✅ Preço de venda deve ser no mínimo custo + 10%
- ✅ Descrição aceita apenas tags HTML: `<p>`, `<br>`, `<b>`, `<strong>`
- ✅ Upload de imagens apenas JPG e PNG
- ✅ Múltiplas imagens por produto

### Segurança

- ✅ Autenticação via Laravel Sanctum
- ✅ Proteção CSRF
- ✅ Validação de inputs
- ✅ Sanitização de HTML

## 🔧 Comandos Úteis

### Docker (MySQL)

```bash
# Ver logs do MySQL
docker-compose logs -f mysql

# Parar containers
docker-compose down

# Resetar banco de dados (apaga tudo)
docker-compose down -v

# Acessar MySQL via CLI
docker-compose exec mysql mysql -u root -proot product_db
```

### Laravel

```bash
# Resetar banco + seeders
php artisan migrate:fresh --seed

# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Rodar testes
php artisan test
```

### Vue

```bash
# Build para produção
npm run build

# Preview do build
npm run preview
```

## 📊 Acessar o Banco de Dados

**Via MySQL Workbench ou DBeaver:**

- Host: `127.0.0.1`
- Port: `3307`
- Username: `root`
- Password: `root`
- Database: `product_db`

**Via linha de comando:**

```bash
docker-compose exec mysql mysql -u root -proot product_db
```

## 🐛 Troubleshooting

### Erro: "Port 3307 already in use"

Mude a porta no `docker-compose.yml`:

```yaml
ports:
  - "3308:3306" # Use outra porta
```

### Erro: "Access denied for user"

Verifique se o `.env` tem as mesmas credenciais do `docker-compose.yml`.

### MySQL não inicia

```bash
docker-compose down -v
docker-compose up -d
# Aguarde 15 segundos
```

## 📝 Licença

Este projeto foi desenvolvido como teste técnico.

## 👨‍💻 Autor

Marcus Vinnicius

- Doc api: https://lunar-trinity-832575.docs.buildwithfern.com/amar-assist/auth/auth
- linkdin: https://www.linkedin.com/in/omarcus212/
- GitHub: https://github.com/omarcus212
- Email: marcusvinniciusouza@gmail.com
