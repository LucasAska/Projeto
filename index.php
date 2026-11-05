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
    display: flex;
    align-items: center;
    text-decoration: none;
}

.logo img {
    height: 85px;
    width: auto;
    object-fit: contain;
    display: block;
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
    width: 100%;
    padding: 0;
    min-height: auto;
}

.banner-crossfade {
    position: relative;
    width: 100%;
    height: 450px;
    overflow: hidden;
    background: #f5e8ec;
}

.banner-imagem {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    opacity: 0;
    animation: troca-imagem 18s infinite;
}

.banner-imagem-1 {
    background-image: url('assets/images/banners/banner1.jpg');
    animation-delay: 0s;
}

.banner-imagem-2 {
    background-image: url('assets/images/banners/banner2.jpg');
    animation-delay: 6s;
}

.banner-imagem-3 {
    background-image: url('assets/images/banners/banner3.jpg');
    animation-delay: 12s;
}

@keyframes troca-imagem {
    0%, 30% {
        opacity: 1;
    }
    33%, 100% {
        opacity: 0;
    }
}

.banner-botao {
    position: absolute;
    z-index: 2;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    padding: 15px 38px;
    background: #a34f70;
    color: #fff;
    text-decoration: none;
    border-radius: 7px;
    font-size: 17px;
    font-weight: bold;
    transition: background 0.3s;
}

.banner-botao:hover {
    background: #873d5b;
}

@media (max-width: 600px) {
    .banner-crossfade {
        height: 300px;
    }
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
        <a class="logo" href="index.php">
    <img src="assets/images/logo-milka.png" alt="Milka Mics">
</a>

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
    <section class="banner-crossfade">

        <div class="banner-imagem banner-imagem-1"></div>
        <div class="banner-imagem banner-imagem-2"></div>
        <div class="banner-imagem banner-imagem-3"></div>

        <a href="produtos.php" class="banner-botao">
            Catálogo
        </a>

    </section>
</main>

<footer>
    <p>&copy; <?= date('Y') ?> Milka Mics. Todos os direitos reservados.</p>
</footer>

</body>
</html>
