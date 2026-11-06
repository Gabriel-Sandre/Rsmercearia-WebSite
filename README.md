# Rsmercearia — site de mercearia (projeto de TCC)

Site de catálogo e pedidos de uma mercearia de bairro, feito em **PHP + MySQL + Bootstrap** como
trabalho de conclusão do ensino médio técnico. Está aqui como registro do começo da minha trajetória:
foi o primeiro sistema completo que escrevi — banco, back-end e telas.

> Projeto antigo, mantido público por transparência do percurso. Para ver meu trabalho atual, veja
> [helpdesk-aspnet-mvc](https://github.com/Gabriel-Sandre/helpdesk-aspnet-mvc) (.NET) e
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

**Com Docker** (sobe o PHP e o MariaDB com o banco já importado):

```bash
docker compose up -d
```

Abra `http://localhost:8000` e crie uma conta na tela de login.

**Com XAMPP/WAMP:**

1. Crie o banco e importe `Site/backend/sql/Rsmercearia.sql`
2. Sirva a pasta `Site/` pelo Apache (ou `php -S localhost:8000 -t Site`)
3. Abra `http://localhost:8000`

A conexão lê `DB_HOST`, `DB_USER`, `DB_PASSWORD` e `DB_NAME` do ambiente; sem elas, usa o padrão do
XAMPP (`root` sem senha em `localhost`).

## Banco de dados

Três tabelas: `Categorias`, `Items` (produto, preço, imagem e categoria) e `Users` (cliente, com a
senha gravada em hash). O arquivo `Rsmercearia.sql` cria tudo e já popula o catálogo de exemplo.

## Limitações conhecidas

Sei hoje o que faria diferente — e é por isso que o projeto continua aqui:

- **Permissão de administrador por e-mail fixo no código** (`admin@gmail.com`), em vez de um campo
  de papel no banco.
- **Duas formas de acesso ao banco**: o catálogo usa PDO e o login usa `mysqli` (ambos com
  consultas parametrizadas).
- **Bibliotecas de terceiros versionadas junto do projeto** (Bootstrap e jQuery), em vez de
  gerenciador de pacotes.
- **Poucos testes**: só os de ponta a ponta do login e das páginas protegidas
  (`tests/seguranca.sh`, rodando no GitHub Actions).

Esses pontos estão resolvidos nos projetos que vieram depois: configuração fora do código,
autorização por papel, testes automatizados e integração contínua.

## Correções de segurança (2026)

Revisitando o código, encontrei e corrigi falhas da versão original do TCC:

- **Acesso ao painel de administração sem senha.** O login gravava o e-mail na sessão antes de
  conferir a senha, e o painel só checava esse e-mail. Agora a sessão só é preenchida depois do
  `password_verify`, e o ID de sessão é renovado no login.
- **SQL injection** no login e no cadastro: o e-mail era concatenado na consulta. Hoje as duas usam
  consulta parametrizada.
- **Páginas protegidas enviadas mesmo sem login.** O `header("Location: ...")` não era seguido de
  `exit`, então o HTML da página ia junto do redirecionamento.
- **XSS armazenado**: o nome do cadastro era impresso sem escape no menu de todas as páginas.
- **Mensagens de login** deixaram de dizer se o e-mail existe.
- **Credenciais do banco** passaram para variáveis de ambiente, e o erro de conexão não expõe mais
  detalhes do servidor.

Também reduzi as imagens do site de 8,3 MB para 0,4 MB e removi os source maps do Bootstrap.
