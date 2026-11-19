<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Milka Mics</title>

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
            display: flex;
            flex-direction: column;
        }

        header {
            background: #fff;
            padding: 20px 6%;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

     .logo {
    display: flex;
    align-items: center;
    text-decoration: none;
}

.logo img {
    height: 85px;
    width: auto;
    max-width: 220px;
    object-fit: contain;
    display: block;
}

        main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 35px 20px;
        }

        .login {
            width: 100%;
            max-width: 390px;
            background: #fff;
            padding: 35px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #f0e6e9;
        }

        .login h1 {
            text-align: center;
            color: #a34f70;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .descricao {
            text-align: center;
            color: #777;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        .campo {
            margin-bottom: 19px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #e1d4d9;
            border-radius: 6px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input:focus {
            border-color: #a34f70;
            box-shadow: 0 0 0 3px rgba(163, 79, 112, 0.12);
        }

        .esqueceu {
            display: block;
            text-align: right;
            margin-top: -8px;
            margin-bottom: 22px;
            font-size: 13px;
        }

        a {
            color: #a34f70;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .botao {
            display: block;
            width: 100%;
            padding: 13px;
            background: #a34f70;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }

        .botao:hover {
            background: #873d5b;
        }

        .divisor {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 25px 0;
            color: #aaa;
            font-size: 12px;
        }

        .divisor::before,
        .divisor::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #eee2e7;
        }

        .cadastro {
            text-align: center;
            font-size: 14px;
            color: #666;
            line-height: 1.8;
        }

        .cadastro a {
            font-weight: bold;
        }

        .voltar {
            display: block;
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
        }

        .mensagem {
            padding: 12px;
            margin-bottom: 20px;
            background: #fff0f2;
            border: 1px solid #f0cbd5;
            color: #873d5b;
            border-radius: 6px;
            font-size: 13px;
            line-height: 1.5;
        }

        footer {
            background: #fff;
            padding: 22px;
            text-align: center;
            color: #777;
            font-size: 13px;
        }

        @media (max-width: 480px) {
            .login {
                padding: 28px 22px;
            }
        }
    </style>
</head>
<body>

<header>
    <a class="logo" href="index.php">
    <img src="assets/images/logo-milka.png" alt="Milka Mics">
</a>
</header>

<main>
    <section class="login">

        <h1>Bem-vindo de volta!</h1>
        <p class="descricao">
            Entre na sua conta para continuar comprando.
        </p>

        <?php if (isset($_GET['erro'])): ?>
            <div class="mensagem">
                <?php
                $erro = $_GET['erro'];

                if ($erro === 'credenciais') {
                    echo 'E-mail ou senha inválidos.';
                } elseif ($erro === 'campos') {
                    echo 'Preencha seu e-mail e sua senha.';
                } else {
                    echo 'Não foi possível entrar. Tente novamente.';
                }
                ?>
            </div>
        <?php endif; ?>

        <form method="post" action="recebe.php">

            <div class="campo">
                <label for="email">E-mail</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    placeholder="seuemail@exemplo.com"
                    autocomplete="email"
                    required
                >
            </div>

            <div class="campo">
                <label for="senha">Senha</label>
                <input
                    type="password"
                    name="senha"
                    id="senha"
                    placeholder="Digite sua senha"
                    autocomplete="current-password"
                    required
                >
            </div>

 

            <button class="botao" type="submit">Entrar</button>

        </form>

        <div class="divisor">OU</div>

        <p class="cadastro">
            Ainda não tem uma conta?<br>
            <a href="cadastro.php">Criar minha conta</a>
        </p>

        <a class="voltar" href="index.php">
            &larr; Voltar para a página inicial
        </a>

    </section>
</main>

<footer>
    &copy; 2026 Milka Mics. Todos os direitos reservados.
</footer>

</body>
</html>
