# Projeto Picanha no Alho

Sistema web em **PHP + MySQL** rodando em **Docker**, com cadastro, login e controle de sessão de usuários. O projeto ainda está em desenvolvimento.

## Tecnologias

- PHP 8.2 (Apache)
- MySQL 8.0
- phpMyAdmin
- Docker / Docker Compose

---

## Como rodar o projeto

### 1. Pré-requisitos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) instalado e **aberto**
- [VSCode](https://code.visualstudio.com/) (opcional, mas recomendado)

### 2. Passo a passo

1. Clone o repositório e abra a pasta no VSCode.
2. No terminal do VSCode, dentro da pasta do projeto, rode:

```bash
docker-compose up -d
```

Na primeira vez, o Docker baixa as imagens e cria o banco automaticamente (leva alguns minutos).

### 3. Acessos

| O quê | Endereço | Login |
|---|---|---|
| Site | http://localhost | – |
| phpMyAdmin (banco) | http://localhost:8081 | usuário `root` / senha `root` |

### Usuário de teste (admin)

| E-mail | Senha |
|---|---|
| `admin@loja.com` | `123456` |

> Apenas para desenvolvimento. No primeiro login a senha é convertida para hash automaticamente.

---

## Comandos úteis

```bash
docker-compose up -d        # iniciar o projeto
docker-compose down         # parar o projeto (apaga o banco!)
docker-compose logs -f      # ver os logs
docker-compose ps           # ver o que está rodando
```

### Resetar o banco de dados

Os arquivos da pasta `banco/` só são executados quando o banco é **criado do zero**. Se você alterou os `.sql` e quer aplicar as mudanças:

```bash
docker-compose down
docker-compose up -d
```

> Isso apaga todos os dados do banco e recria tudo a partir dos arquivos `.sql`.

---

## Estrutura de pastas

```
Projeto-picanha_no_alho/
├── assets/
│   ├── css/style.css        # estilos (usado no cadastro)
│   └── js/script.js         # scripts (ainda vazio)
├── banco/
│   ├── 01_estrutura.sql     # criação das tabelas
│   └── 02_dados.sql         # dados iniciais (categorias, produtos, admin)
├── config/
│   └── conexao.php          # conexão com o MySQL
├── cadastro.php             # formulário de cadastro
├── cadastro_salvar.php      # grava o novo usuário no banco
├── sing_in.php              # tela de login
├── recebe.php               # valida e-mail/senha e abre a sessão
├── logout.php               # encerra a sessão
├── index.php                # página inicial
├── docker-compose.yml       # serviços: web, db e phpmyadmin
└── Dockerfile               # PHP 8.2 + Apache + mysqli/pdo_mysql
```

---

## Banco de dados

Nome do banco: `ProjetoDados`

| Tabela | Para que serve |
|---|---|
| `USUARIOS` | Dados pessoais, endereço e tipo (`cliente` ou `admin`) |
| `CATEGORIAS` | Categorias dos produtos |
| `PRODUTOS` | Produtos, preço, estoque e imagem |
| `PEDIDOS` | Pedidos feitos pelos usuários (`pendente`, `atendido`, `cancelado`) |
| `PEDIDOS_ITENS` | Itens de cada pedido |

Relações principais: `PRODUTOS → CATEGORIAS`, `PEDIDOS → USUARIOS`, `PEDIDOS_ITENS → PEDIDOS e PRODUTOS`.

---

## O que já funciona

- [x] Ambiente Docker (site, banco e phpMyAdmin)
- [x] Criação automática das tabelas e dos dados iniciais
- [x] Cadastro de usuário (senha salva com hash)
- [x] Login com sessão e logout
- [x] Página inicial que muda conforme o usuário está logado ou não

## O que falta fazer

- [ ] Painel administrativo (`admin/`) – o link já aparece para admins, mas a página ainda não existe
- [ ] CRUD de usuários e de uma segunda tabela (incluir, alterar, excluir com confirmação)
- [ ] Mensagens de confirmação (incluído, alterado, excluído)
- [ ] Listagem de produtos e pedidos
- [ ] Pasta `uploads/` para imagens dos produtos

---

## Problemas comuns

**Porta 80 ou 3306 já em uso**
Feche outros servidores (XAMPP, WAMP, outro MySQL) ou mude a porta no `docker-compose.yml`.

**Site não abre**
Confira se o Docker Desktop está aberto e rode `docker-compose ps` para ver se os 3 serviços estão ativos.

**Erro de conexão com o banco logo após subir**
O MySQL demora um pouco para iniciar. Aguarde cerca de 30 segundos e recarregue a página.

**Alterei o `.sql` e nada mudou**
Veja a seção [Resetar o banco de dados](#resetar-o-banco-de-dados).