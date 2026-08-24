# Plataforma Web de Serviços Freelance

> **Licenciatura em Engenharia Informática e Computação (LEIC @ FEUP)**  
> **Unidade Curricular:** Linguagens e Tecnologias Web (LTW)  
> **Autor(es):** Luís Martins

---

## 📌 Sobre o Projeto

Este projeto consiste numa aplicação web dinâmica desenvolvida de raiz para a gestão e publicação de serviços *freelance*. A plataforma liga clientes que procuram soluções digitais a profissionais qualificados.

A aplicação implementa um sistema completo de gestão de utilizadores com três papéis principais (**Administrador**, **Cliente** e **Freelancer**), protegendo dados contra vulnerabilidades web comuns (SQL Injection, XSS, CSRF).

---

## 🛠️ Tecnologias Utilizadas

- **Backend:** PHP 8 (PDO para consultas seguras à base de dados)
- **Base de Dados:** SQLite
- **Frontend:** HTML5, CSS3 (Design Responsivo), JavaScript Vanilla
- **Segurança:** Sanitização de inputs, Hash de passwords (`password_hash`), Gestão de sessões.

---

## ✨ Funcionalidades Principais

- **Autenticação e Perfis:**
  - Registo e login de utilizadores.
  - Dashboards personalizados consoante o papel (`admin_dashboard`, `client_homepage`, `freelancer_homepage`).
- **Gestão de Serviços:**
  - Publicação de propostas de trabalho por clientes.
  - Candidatura a projetos por parte dos freelancers.
- **Painel de Administração:**
  - Gestão de utilizadores, moderação de propostas e relatórios da plataforma.

---

## 📁 Estrutura do Repositório

- `index.php` — Ponto de entrada e controlador de encaminhamento baseado na sessão do utilizador.
- `database/` — Scripts SQL e ligação à base de dados SQLite (`db.php`).
- `pages/` — Páginas da aplicação divididas por módulos (`auth`, `home`, `services`).
- `includes/` — Componentes reutilizáveis (header, footer, navegação).
- `assets/` — Ficheiros estáticos de CSS, JavaScript e imagens.

---

## 🚀 Como Executar

1. Iniciar o servidor embutido do PHP na raiz do projeto:
   ```bash
   php -S localhost:8000
   ```
2. Abrir o navegador em `http://localhost:8000`.
