================================================================================
MILKA MICS — LOJA DE ARTESANATO
Documentação dos arquivos e suas funções
================================================================================

ÍNDICE
------
1. Visão geral do projeto
2. Estrutura de pastas
3. Arquivos de configuração (config/)
4. Arquivos de layout (includes/)
5. Arquivos públicos (raiz do projeto)
6. Arquivos do painel administrativo (admin/)
7. Arquivos de estilos e scripts (assets/)
8. Banco de dados (database.sql)
9. Como rodar o projeto
10. Credenciais padrão


================================================================================
1. VISÃO GERAL DO PROJETO
================================================================================

Tecnologias utilizadas:
  - PHP 8.x (linguagem do back-end)
  - MySQL / MariaDB (banco de dados)
  - PDO (acesso seguro ao banco com prepared statements)
  - HTML5 + CSS3 + JavaScript (front-end)
  - Google Fonts: Playfair Display + Poppins

Funcionalidades principais:
  - Catálogo de produtos (brincos, cerâmica, crochê)
  - Produto pode pertencer a várias categorias ao mesmo tempo (N:N)
  - Carrinho de compras com sessão
  - Cadastro e login de usuários (com celular e endereço)
  - Finalização de pedido com baixa automática de estoque
  - Painel administrativo completo:
      * CRUD de produtos
      * CRUD de usuários (com promoção a admin)
      * Gestão de pedidos (alteração de status)
      * Dashboard com estatísticas


================================================================================
2. ESTRUTURA DE PASTAS
================================================================================

milka-mics/
├── admin/          → painel administrativo (só admins acessam)
├── assets/         → arquivos estáticos (CSS, JS)
│   ├── css/
│   └── js/
├── config/         → configurações do sistema e conexão com banco
├── includes/       → pedaços reutilizáveis (header, footer, funções)
├── uploads/        → imagens dos produtos enviadas pelo admin
├── index.php       → página inicial
├── ... (outros arquivos .php da loja)
└── database.sql    → script de criação do banco


================================================================================
3. ARQUIVOS DE CONFIGURAÇÃO (config/)
================================================================================

config/database.php
-------------------
Função: criar a conexão PDO com o MySQL.
Contém as constantes DB_HOST, DB_NAME, DB_USER e DB_PASS.
Se a conexão falhar, mostra uma mensagem de erro e encerra a execução.

Onde mexer: se você trocar de máquina ou definir senha no MySQL,
            atualize as credenciais aqui.

config/config.php
-----------------
Função: inicialização geral do sistema.
  - Inicia a sessão PHP
  - Define a constante APP_ROOT (caminho raiz do projeto)
  - Define a constante UPLOAD_DIR (pasta de imagens)
  - Detecta automaticamente a BASE_URL (funciona em qualquer subpasta)
  - Carrega o database.php e o functions.php

Esse arquivo é incluído por TODOS os outros arquivos .php.
Você nunca precisa editá-lo, exceto se mover o projeto para produção.


================================================================================
4. ARQUIVOS DE LAYOUT (includes/)
================================================================================

includes/functions.php
----------------------
Função: funções utilitárias usadas em todo o sistema.

Principais funções:
  - e()               → escapa HTML (evita XSS)
  - url()             → gera URL absoluta correta a partir da raiz do projeto
  - moeda()           → formata número como R$ 0,00
  - redirecionar()    → redireciona para outra página
  - set_flash()       → grava mensagem temporária na sessão
  - mostrar_flash()   → exibe e apaga as mensagens temporárias
  - usuario_logado()  → verifica se há usuário logado
  - usuario_atual()   → retorna os dados do usuário logado
  - is_admin()        → verifica se o usuário logado é admin
  - exigir_login()    → força login (bloqueia acesso)
  - exigir_admin()    → força ser admin (bloqueia acesso)
  - carrinho()        → retorna o array do carrinho
  - carrinho_qtd_total() → soma de itens no carrinho
  - carrinho_limpar() → esvazia o carrinho
  - produto_imagem_url() → retorna URL da imagem ou null
  - placeholder_inicial() → primeira letra do nome (usado como ícone)
  - categorias_do_produto() → retorna categorias de um produto (N:N)
  - salvar_categorias_produto() → sincroniza categorias de um produto
  - formatar_celular() → formata (11) 91234-5678
  - validar_celular() → valida DDD + número
  - total_admins()    → conta quantos admins existem no sistema

includes/header.php
-------------------
Função: topo de todas as páginas públicas da loja.
Contém:
  - <head> com meta tags, Google Fonts e link do CSS
  - Barra verde oliva com logo, slogan centralizado, menu e carrinho

Se quiser mudar o menu, o logo ou o slogan, é aqui.

includes/footer.php
-------------------
Função: rodapé de todas as páginas públicas.
Contém:
  - Colunas com sobre a loja, categorias e atendimento
  - Linha final com copyright
  - Carrega o script.js


================================================================================
5. ARQUIVOS PÚBLICOS (raiz)
================================================================================

index.php
---------
Página inicial da loja.
  - Busca os 8 produtos marcados como destaque
  - Busca os 4 produtos mais recentes (novidades)
  - Exibe tudo em grade

produtos.php
------------
Listagem de produtos com filtros.
  - Aceita parâmetros via URL:
      ?categoria=brincos    → filtra por categoria
      ?busca=argola         → pesquisa por nome/descrição
      ?ordem=menor          → ordena por preço
  - Usa a tabela produto_categoria (N:N), então um produto em várias
    categorias aparece em todas elas.

produto.php
-----------
Página individual de um produto.
  - Recebe ?id=XX na URL
  - Mostra imagem, nome, descrição, preço, estoque
  - Formulário para adicionar ao carrinho
  - Exibe todas as categorias em que o produto está
  - Mostra até 4 produtos relacionados da mesma categoria

carrinho.php
------------
Carrinho de compras baseado em sessão.
  - Adicionar item (via POST)
  - Atualizar quantidades
  - Remover item
  - Esvaziar carrinho
  - Resumo com subtotal e total

finalizar.php
-------------
Finalização do pedido.
  - Exige login
  - Mostra os dados do usuário (nome, e-mail, celular, endereço)
  - Ao confirmar:
      * Cria o registro em pedidos
      * Cria os itens em pedido_itens
      * Dá baixa no estoque
      * Limpa o carrinho
  - Usa transação SQL (rollBack em caso de erro)

pedidos.php
-----------
Lista os pedidos do usuário logado com seus itens.

cadastro.php
------------
Formulário de cadastro de novo cliente.
  - Coleta: nome, e-mail, celular, endereço, senha
  - Valida tudo no servidor
  - Verifica se o e-mail já existe
  - Salva senha com password_hash()
  - Faz login automático após cadastro

login.php
---------
Formulário de login.
  - Verifica email + senha no banco
  - Usa password_verify()
  - Redireciona admin para /admin/dashboard.php
  - Redireciona cliente para a home

logout.php
----------
Encerra a sessão do usuário e volta para a home.

instalar.php
------------
ARQUIVO TEMPORÁRIO — deve ser APAGADO após o primeiro uso.
Cria (ou atualiza) o usuário administrador padrão no banco.


================================================================================
6. PAINEL ADMINISTRATIVO (admin/)
================================================================================

Todos os arquivos da pasta admin/ começam exigindo que o usuário esteja
logado E seja do tipo 'admin'. Caso contrário, redirecionam para o login.

admin/_header.php
-----------------
Topo do painel administrativo.
  - Chama exigir_admin()
  - Exibe a barra verde oliva com o logo
  - Exibe a barra escura de navegação:
      Dashboard | Produtos | Pedidos | Usuários

admin/_footer.php
-----------------
Rodapé simples do painel.

admin/dashboard.php
-------------------
Visão geral do sistema:
  - Quantos produtos ativos
  - Quantos pedidos
  - Quantos clientes
  - Quantos usuários totais
  - Faturamento total (soma de pedidos não cancelados)
  - Últimos 8 pedidos

admin/produtos.php
------------------
Lista todos os produtos cadastrados em tabela.
  - Mostra imagem, nome, categorias (com "+" entre elas), preço,
    estoque e status
  - Botões: Editar / Excluir
  - Botão "+ Novo produto"

admin/produto_form.php
----------------------
Formulário de cadastro/edição de produto.
  - Nome, descrição, preço, estoque
  - Checkboxes de categorias (permite marcar várias)
  - Upload de imagem (JPG/PNG/WEBP/GIF até 4MB)
  - Checkbox destaque
  - Checkbox ativo
  - Ao salvar: insere/atualiza produto + sincroniza a tabela
    produto_categoria (N:N)

admin/produto_excluir.php
-------------------------
Exclui um produto.
  - Se o produto já foi vendido (tem pedidos), apenas o desativa
    (preserva o histórico)
  - Senão, apaga do banco e remove a imagem do disco

admin/pedidos.php
-----------------
Lista todos os pedidos feitos.
  - Mostra cliente, contato, endereço, data, total
  - Select para alterar o status:
      pendente / pago / enviado / cancelado

admin/usuarios.php
------------------
Lista todos os usuários cadastrados.
  - Filtros por tipo (Cliente / Admin)
  - Busca por nome, e-mail ou celular
  - Botões: Editar / Excluir
  - Botão "+ Novo usuário"

admin/usuario_form.php
----------------------
Formulário de cadastro/edição de usuário.
  - Nome, e-mail, celular, endereço
  - Select: tipo (cliente ou admin)
  - Campo senha (opcional ao editar — em branco mantém a atual)
  - Proteções:
      * Não deixa rebaixar a própria conta de admin
      * Não deixa rebaixar o último admin do sistema
      * Verifica e-mail duplicado

admin/usuario_excluir.php
-------------------------
Exclui um usuário.
  - Impede excluir a si próprio
  - Impede excluir o último admin do sistema
  - Remove em cascata os pedidos vinculados (ON DELETE CASCADE)


================================================================================
7. ARQUIVOS DE ESTILOS E SCRIPTS (assets/)
================================================================================

assets/css/style.css
--------------------
Folha de estilos principal.

Cores (definidas em :root no topo):
  --oliva         #556B2F   cor principal
  --oliva-escuro  #3C4A21
  --oliva-claro   #7C9350
  --oliva-suave   #E9EEDD
  --creme         #FAF8F3   fundo do site
  --terracota     #C0784B   cor de destaque/acentos

Estrutura:
  - Reset e tipografia global
  - Cabeçalho (.topo, .logo, .menu, .topo__slogan)
  - Hero (opcional, hoje não está em uso)
  - Grade de produtos (.grade-produtos, .card)
  - Filtros (.filtros, .chip, .busca)
  - Detalhe do produto (.produto-detalhe)
  - Formulários (.form-box, .campo)
  - Alertas (.alerta-sucesso/erro/info)
  - Tabelas (.tabela)
  - Resumo de valores (.resumo)
  - Cartões de estatística (.stat)
  - Rodapé (.rodape)
  - Responsividade no final (@media max-width 900px e 760px)

Se quiser mudar a cor principal do site, basta editar --oliva aqui.

assets/js/script.js
-------------------
JavaScript do site.
  - Abre/fecha o menu mobile
  - Confirmação antes de excluir (usa atributo data-confirmar)
  - Preview de imagem no formulário de produto
  - Auto-oculta alertas após 6 segundos


================================================================================
8. BANCO DE DADOS (database.sql)
================================================================================

Script único que cria todo o banco. Execute UMA VEZ no phpMyAdmin ou Adminer.

Tabelas:

categorias
----------
Categorias fixas dos produtos.
  id, nome, slug
  Contém: Brincos, Cerâmica, Crochê

produtos
--------
Produtos da loja.
  id, categoria_id, nome, descricao, preco, estoque, imagem,
  destaque, ativo, criado_em
  A coluna categoria_id guarda a "categoria principal" do produto.

produto_categoria
-----------------
Tabela de ligação N:N (muitos-para-muitos).
  produto_id, categoria_id
  Permite que um produto pertença a várias categorias.
  Ex.: Brinco de Coração em Crochê pertence a Brincos E Crochê.

usuarios
--------
Clientes e administradores.
  id, nome, email, celular, endereco, senha, tipo, criado_em
  tipo: 'cliente' ou 'admin'

pedidos
-------
Pedidos realizados.
  id, usuario_id, total, status, criado_em
  status: pendente / pago / enviado / cancelado

pedido_itens
------------
Itens de cada pedido.
  id, pedido_id, produto_id, quantidade, preco_unitario
  Guarda o preço no momento da compra (não muda depois).


================================================================================
9. COMO RODAR O PROJETO
================================================================================

Pré-requisitos:
  - Laragon (ou outro servidor local com Apache + PHP + MySQL)
  - VS Code para editar
  - Navegador

Passo a passo:

  1. Coloque a pasta do projeto em:  E:\laragon\www\milka-mics\
     (crie a pasta uploads/ vazia dentro)

  2. Inicie o Laragon e clique em "Start All"

  3. Abra o phpMyAdmin ou Adminer

  4. Importe o arquivo database.sql

  5. Acesse:  http://localhost/milka-mics/instalar.php
     Isso cria o admin padrão

  6. APAGUE o arquivo instalar.php

  7. Acesse:  http://localhost/milka-mics/

  8. Faça login com as credenciais da seção 10


================================================================================
10. CREDENCIAIS PADRÃO
================================================================================

Admin do site:
  E-mail:  admin@milkamics.com
  Senha:   admin123

MySQL (localhost):
  Usuário:  root
  Senha:    root  (ou vazio, dependendo da sua instalação)

Banco de dados:
  Nome: milka_mics

================================================================================
FIM DA DOCUMENTAÇÃO
================================================================================