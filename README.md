# Rsmercearia — site de mercearia (projeto de TCC)

Site de catálogo e pedidos de uma mercearia de bairro, feito em **PHP + MySQL + Bootstrap** como
trabalho de conclusão do ensino médio técnico. Está aqui como registro do começo da minha trajetória:
foi o primeiro sistema completo que escrevi — banco, back-end e telas.

> Projeto antigo, mantido público por transparência do percurso. Para ver meu trabalho atual, veja
> [ingressa](https://github.com/Gabriel-Sandre/ingressa) (.NET) e
> [askdoc-api](https://github.com/Gabriel-Sandre/askdoc-api) (Python).

## O que o site faz

- Cadastro e login de clientes, com senha protegida por `password_hash` (bcrypt)
- Catálogo de produtos com vitrine, navegação por categoria e busca por texto
- Página de produto, carrinho e perfil do usuário
- Área administrativa para acompanhar o catálogo

## Tecnologias

`PHP` `MySQL` `PDO` `Bootstrap 4` `jQuery` `HTML/CSS`

## Estrutura

```
Site/
├── index.php, categoria.php, busca.php, carrinho.php, perfil.php, login.php
├── administração.php        área administrativa
├── backend/
│   ├── conexao.php          conexão mysqli (páginas antigas)
│   ├── func/                db_conn.php (PDO) e funções de item e categoria
│   └── sql/Rsmercearia.sql  criação das tabelas e dados de exemplo
└── front/                   CSS, JS, imagens e bibliotecas
```

## Como rodar

Precisa de PHP e MySQL (XAMPP, WAMP ou Docker).

1. Crie o banco e importe `Site/backend/sql/Rsmercearia.sql`
2. Ajuste usuário e senha do banco em `Site/backend/conexao.php` e `Site/backend/func/db_conn.php`
3. Sirva a pasta `Site/` pelo Apache (ou `php -S localhost:8000 -t Site`)
4. Abra `http://localhost:8000`

## Banco de dados

Três tabelas: `Categorias`, `Items` (produto, preço, imagem e categoria) e `Users` (cliente, com a
senha gravada em hash). O arquivo `Rsmercearia.sql` cria tudo e já popula o catálogo de exemplo.

## Limitações conhecidas

Sei hoje o que faria diferente — e é por isso que o projeto continua aqui:

- **Permissão de administrador por e-mail fixo no código** (`admin@gmail.com`), em vez de um campo
  de papel no banco.
- **Credenciais do banco no código-fonte**, sem arquivo de configuração nem variáveis de ambiente.
- **Consultas mistas**: as funções de catálogo usam PDO com `prepare`, mas há páginas antigas com
  `mysqli` direto.
- **Bibliotecas de terceiros versionadas junto do projeto** (Bootstrap e jQuery), em vez de
  gerenciador de pacotes.
- **Sem testes automatizados e sem CI.**

Esses pontos estão resolvidos nos projetos que vieram depois: configuração fora do código,
autorização por papel, testes automatizados e integração contínua.
