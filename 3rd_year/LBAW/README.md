# Aplicação Web Full-Stack em Laravel 12

> **Licenciatura em Engenharia Informática e Computação (LEIC @ FEUP)**  
> **Unidade Curricular:** Laboratório de Bases de Dados e Aplicações Web (LBAW)  
> **Autor(es):** Luís Martins e Grupo LBAW

---

## 📌 Sobre o Projeto

Este projeto consistiu no desenvolvimento completo (*full-stack*) de uma plataforma web empresarial para a gestão colaborativa de projetos e tarefas, desenvolvida durante a unidade curricular de LBAW.

A aplicação adota a arquitetura **MVC (Model-View-Controller)** sobre a framework **Laravel 12**, combinada com uma base de dados relacional **PostgreSQL**, conteinerização com **Docker** e estilização moderna com **Tailwind CSS**.

---

## 🛠️ Tecnologias e Arquitetura

- **Framework Backend:** Laravel 12 (PHP 8)
- **Base de Dados:** PostgreSQL (Tabelas, Vistas, Triggers, Índices e Full-Text Search)
- **Frontend:** Blade Templating Engine, Tailwind CSS, JavaScript (Vite)
- **Conteinerização & Deploy:** Docker, Docker Compose
- **Segurança & Autenticação:** Policies do Laravel, Autenticação de Utilizadores, Sanitização XSS e Proteção CSRF.

---

## ✨ Funcionalidades Principais

- **Gestão de Projetos e Tarefas:**
  - Criação de projetos, fases, tarefas, prazos e atribuição de membros.
  - Acompanhamento de progresso com estatísticas e gráficos de analytics.
- **Controlo de Acessos Rígido (RBAC):**
  - Papéis de Administrador, Coordenador de Projeto e Membro.
  - Políticas de autorização (*Policies*) protegendo cada rota e ação.
- **Comunicação e Notificações:**
  - Fórum de discussão integrado por projeto.
  - Notificações de sistema e convites por e-mail.
- **Pesquisa Avançada:**
  - Pesquisa global sobre projetos e tarefas utilizando *Full-Text Search* do PostgreSQL.

---

## 📁 Estrutura do Repositório

- `app/` — Controllers, Models, Policies e Middleware.
- `resources/views/` — Vistas em Blade divididas por componentes, layouts e modais.
- `database/` — Migrações, Seeders e scripts SQL.
- `routes/` — Definição das rotas da aplicação (`web.php`).
- `docker-compose.yaml` — Configuração do ambiente Docker (PostgreSQL, pgAdmin).

---

## 🚀 Como Executar com Docker / Localmente

1. Clonar o repositório e copiar o ficheiro de ambiente:
   ```bash
   cp .env.example .env
   ```
2. Iniciar os contentores Docker (PostgreSQL):
   ```bash
   docker compose up -d
   ```
3. Instalar dependências de PHP e Node.js:
   ```bash
   composer install
   npm install
   ```
4. Executar migrações e seeders de teste na base de dados:
   ```bash
   php artisan migrate --seed
   ```
5. Iniciar o servidor de desenvolvimento:
   ```bash
   php artisan serve
   ```
