<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuarioLogado = isset($_SESSION['usuario_id']);
$nomeUsuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$tipoUsuario = strtolower(trim($_SESSION['usuario_tipo'] ?? ''));

$nomeUsuario = htmlspecialchars(
    $nomeUsuario,
    ENT_QUOTES,
    'UTF-8'
);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Milka Mics | Artesanato feito à mão</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #fffaf7;
            color: #333;
            min-height: 100vh;
        }

        header {
            background: #fff;
            padding: 20px 6%;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
        }

        .logo {
            color: #a34f70;
            font-size: 25px;
            font-weight: bold;
            text-decoration: none;
        }

        .menu {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .menu a {
            color: #444;
            text-decoration: none;
            font-size: 15px;
            transition: color 0.2s;
        }

        .menu a:hover {
            color: #a34f70;
        }

        .menu .botao {
            background: #a34f70;
            color: #fff;
            padding: 11px 18px;
            border-radius: 6px;
        }

        .menu .botao:hover {
            background: #873d5b;
            color: #fff;
        }

        main {
            min-height: 70vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 60px 20px;
        }

        main h1 {
            color: #a34f70;
            font-size: clamp(30px, 5vw, 46px);
            margin-bottom: 18px;
        }

        main p {
            color: #666;
            font-size: 18px;
            line-height: 1.7;
            margin-bottom: 28px;
        }

        .destaque {
            max-width: 650px;
        }

        .acoes {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .acoes a {
            display: inline-block;
            padding: 13px 22px;
            background: #a34f70;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
        }

        .acoes a.secundario {
            background: #fff;
            color: #a34f70;
            border: 1px solid #a34f70;
        }

        footer {
            text-align: center;
            padding: 22px;
            background: #fff;
            color: #777;
            font-size: 14px;
        }

        @media (max-width: 600px) {
            nav {
                flex-direction: column;
            }

            .menu {
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<header>
    <nav>
        <a class="logo" href="index.php">Milka Mics</a>

        <div class="menu">
            <a href="index.php">Início</a>

            <?php if ($usuarioLogado): ?>

                <span>Olá, <?= $nomeUsuario ?>!</span>

                <?php if ($tipoUsuario === 'admin'): ?>
                    <a href="admin.php">Painel administrativo</a>
                <?php endif; ?>

                <a class="botao" href="logout.php">Sair</a>

            <?php else: ?>

                <a href="sing_in.php">Entrar</a>
                <a class="botao" href="cadastro.php">Cadastrar</a>

            <?php endif; ?>
        </div>
    </nav>
</header>

<main>
    <div class="destaque">
        <h1>Bem-vindo à Milka Mics!</h1>

        <p>
            Artesanato feito à mão, com carinho e criatividade
            em cada detalhe.
        </p>

        <?php if (!$usuarioLogado): ?>
            <div class="acoes">
                <a href="cadastro.php">Criar minha conta</a>
                <a class="secundario" href="sing_in.php">Fazer login</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<footer>
    <p>&copy; <?= date('Y') ?> Milka Mics. Todos os direitos reservados.</p>
</footer>

</body>
</html>
