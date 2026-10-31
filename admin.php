<?php
require_once __DIR__ . "/config/conexao.php";
require_once __DIR__ . "/config/verifica_acesso.php";

exigirAdmin($conexao);

$nomeAdmin = htmlspecialchars($_SESSION['usuario_nome'] ?? 'Administrador', ENT_QUOTES, 'UTF-8');

$totalUsuarios = 0;
$totalAdmins = 0;
$totalProdutos = 0;

$resUsuarios = $conexao->query("SELECT COUNT(*) AS total FROM USUARIOS");
if ($resUsuarios) {
    $totalUsuarios = (int) $resUsuarios->fetch_assoc()['total'];
}

$resAdmins = $conexao->query("SELECT COUNT(*) AS total FROM USUARIOS WHERE TIPO = 'admin'");
if ($resAdmins) {
    $totalAdmins = (int) $resAdmins->fetch_assoc()['total'];
}

$checkProd = $conexao->query("SHOW TABLES LIKE 'PRODUTOS'");
if ($checkProd && $checkProd->num_rows > 0) {
    $resProd = $conexao->query("SELECT COUNT(*) AS total FROM PRODUTOS");
    if ($resProd) {
        $totalProdutos = (int) $resProd->fetch_assoc()['total'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo | Milka Mics</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>

<header>
    <nav>
        <div class="logo-area">
            <a class="logo" href="index.php">Milka Mics</a>
            <span class="badge-admin">Admin</span>
        </div>

        <div class="menu">
            <a href="index.php">Ver Loja</a>
            <a href="admin_usuarios.php">Usuários</a>
            <a href="admin_produtos.php">Itens</a>
            <span>Olá, <?= $nomeAdmin ?>!</span>
            <a class="botao" href="logout.php">Sair</a>
        </div>
    </nav>
</header>

<main>
    <div class="container-admin">
        <div class="cabecalho">
            <h1>Painel Administrativo</h1>
            <p>Gerencie os cadastros de usuários e o catálogo de artesanatos da Milka Mics.</p>
        </div>

        <div class="resumo-grid">
            <div class="resumo-card">
                <span>Total de Contas</span>
                <strong><?= $totalUsuarios ?></strong>
            </div>
            <div class="resumo-card">
                <span>Administradores</span>
                <strong><?= $totalAdmins ?></strong>
            </div>
            <div class="resumo-card">
                <span>Itens no Catálogo</span>
                <strong><?= $totalProdutos ?></strong>
            </div>
        </div>

        <h2 class="secao-titulo">Módulos do Sistema</h2>

        <div class="modulos-grid">
            <div class="card modulo-card">
                <div>
                    <div class="modulo-icone">U</div>
                    <h2>Gerenciar Usuários</h2>
                    <p>Consulte os clientes registrados, altere permissões de acesso (Cliente / Admin) ou cadastre uma nova conta.</p>
                </div>
                <div class="acoes">
                    <a href="admin_usuarios.php" class="btn-primario">Ver Lista de Usuários</a>
                    <a href="cadastro.php" class="voltar">+ Novo Usuário</a>
                </div>
            </div>

            <div class="card modulo-card">
                <div>
                    <div class="modulo-icone">P</div>
                    <h2>Catálogo de Itens</h2>
                    <p>Adicione novas peças de artesanato à vitrine da loja, defina preços, descrições e acompanhe os itens.</p>
                </div>
                <div class="acoes">
                    <a href="admin_produtos.php" class="btn-primario">Gerenciar Itens</a>
                </div>
            </div>
        </div>
    </div>
</main>

<footer>
    <p>&copy; <?= date('Y') ?> Milka Mics. Todos os direitos reservados.</p>
</footer>

</body>
</html>