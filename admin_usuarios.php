<?php
require_once __DIR__ . "/config/conexao.php";
require_once __DIR__ . "/config/verifica_acesso.php";
exigirAdmin($conexao);

$resultado = $conexao->query("SELECT id, NOME, EMAIL, TELEFONE, CIDADE, UF, TIPO, CRIADO_EM FROM USUARIOS ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Usuários | Milka Mics</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<header>
    <nav>
        <div class="logo-area">
            <a class="logo" href="index.php">Milka Mics</a>
            <span class="badge-admin">Admin</span>
        </div>
        <div class="menu">
            <a href="admin.php">&larr; Voltar ao Painel</a>
            <a href="logout.php" class="botao">Sair</a>
        </div>
    </nav>
</header>

<main>
    <div class="container-admin">
        <div class="cabecalho">
            <h1>Usuários Cadastrados</h1>
            <p>Gerencie as contas do sistema.</p>
        </div>

        <div class="card">
            <div class="topo-tabela">
                <h2 class="secao-titulo titulo-sem-borda">Lista de Contas</h2>
                <a href="cadastro.php" class="btn-primario">+ Novo Usuário</a>
            </div>

            <div class="tabela-responsiva">
                <table class="tabela-admin">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome / E-mail</th>
                            <th>Telefone</th>
                            <th>Cidade/UF</th>
                            <th>Tipo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        -- Coloca o código do php aqui para a consulta e exclussão de usuarios
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<footer>
    <p>&copy; <?= date('Y') ?> Milka Mics.</p>
</footer>

</body>
</html>


