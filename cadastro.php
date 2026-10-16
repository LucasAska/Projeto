<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro | Milka Mics</title>

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
            color: #a34f70;
            font-size: 25px;
            font-weight: bold;
            text-decoration: none;
        }

        main {
            flex: 1;
            padding: 40px 20px;
        }

        .container {
            max-width: 750px;
            margin: 0 auto;
        }

        .cabecalho {
            text-align: center;
            margin-bottom: 28px;
        }

        .cabecalho h1 {
            color: #a34f70;
            font-size: 30px;
            margin-bottom: 10px;
        }

        .cabecalho p {
            color: #777;
            font-size: 15px;
            line-height: 1.6;
        }

        .card {
            background: #fff;
            padding: 32px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #f0e6e9;
        }

        .secao-titulo {
            font-size: 17px;
            font-weight: bold;
            color: #a34f70;
            padding-bottom: 12px;
            margin-bottom: 22px;
            border-bottom: 1px solid #eee2e7;
        }

        .grade {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .campo {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .campo.completo {
            grid-column: 1 / -1;
        }

        label {
            font-size: 14px;
            font-weight: 600;
            color: #444;
        }

        input {
            width: 100%;
            min-width: 0;
            height: 44px;
            padding: 0 13px;
            border: 1px solid #e1d4d9;
            border-radius: 6px;
            background: #fff;
            color: #333;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input:focus {
            border-color: #a34f70;
            box-shadow: 0 0 0 3px rgba(163, 79, 112, 0.12);
        }

        input::placeholder {
            color: #aaa;
        }

        .acoes {
            display: flex;
            gap: 12px;
            margin-top: 5px;
        }

        button,
        .voltar {
            min-height: 44px;
            padding: 0 22px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        button {
            flex: 1;
            border: none;
            background: #a34f70;
            color: #fff;
        }

        button:hover {
            background: #873d5b;
        }

        .voltar {
            border: 1px solid #a34f70;
            background: #fff;
            color: #a34f70;
        }

        .voltar:hover {
            background: #fff4f7;
        }

        footer {
            background: #fff;
            text-align: center;
            padding: 22px;
            color: #777;
            font-size: 13px;
        }

        @media (max-width: 600px) {
            main {
                padding: 28px 14px;
            }

            .card {
                padding: 23px 18px;
            }

            .cabecalho h1 {
                font-size: 25px;
            }

            .grade {
                grid-template-columns: 1fr;
                gap: 17px;
            }

            .campo.completo {
                grid-column: auto;
            }

            .acoes {
                flex-direction: column;
            }

            .voltar {
                width: 100%;
            }
        }
    </style>
</head>

<body>

<header>
    <a class="logo" href="index.php">Milka Mics</a>
</header>

<main>
    <div class="container">

        <div class="cabecalho">
            <h1>Crie sua conta</h1>
            <p>Preencha seus dados para fazer parte da Milka Mics.</p>
        </div>

        <section class="card">

            <form action="cadastro_salvar.php" method="POST">

                <h2 class="secao-titulo">Dados pessoais</h2>

                <div class="grade">

                    <div class="campo completo">
                        <label for="nome">Nome completo</label>
                        <input type="text" id="nome" name="nome"
                               maxlength="100"
                               placeholder="Digite seu nome completo" required>
                    </div>

                    <div class="campo">
                        <label for="telefone">Telefone</label>
                        <input type="tel" id="telefone" name="telefone"
                               maxlength="20"
                               placeholder="(11) 99999-9999" required>
                    </div>

                    <div class="campo">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email"
                               maxlength="150"
                               placeholder="seuemail@exemplo.com" required>
                    </div>

                    <div class="campo completo">
                        <label for="senha">Senha</label>
                        <input type="password" id="senha" name="senha"
                               maxlength="255"
                               placeholder="Crie uma senha" required>
                    </div>

                </div>

                <h2 class="secao-titulo">Endereço</h2>

                <div class="grade">

                    <div class="campo">
                        <label for="cep">CEP</label>
                        <input type="text" id="cep" name="cep"
                               maxlength="9"
                               placeholder="00000-000" required>
                    </div>

                    <div class="campo">
                        <label for="uf">Estado (UF)</label>
                        <input type="text" id="uf" name="uf"
                               maxlength="2"
                               placeholder="Ex.: SP" required>
                    </div>

                    <div class="campo completo">
                        <label for="logradouro">Rua ou avenida</label>
                        <input type="text" id="logradouro" name="logradouro"
                               maxlength="255"
                               placeholder="Nome da rua ou avenida" required>
                    </div>

                    <div class="campo">
                        <label for="numero">Número</label>
                        <input type="text" id="numero" name="numero"
                               maxlength="20"
                               placeholder="Número do imóvel" required>
                    </div>

                    <div class="campo">
                        <label for="complemento">Complemento</label>
                        <input type="text" id="complemento" name="complemento"
                               maxlength="100"
                               placeholder="Apartamento, bloco etc.">
                    </div>

                    <div class="campo">
                        <label for="bairro">Bairro</label>
                        <input type="text" id="bairro" name="bairro"
                               maxlength="100"
                               placeholder="Seu bairro" required>
                    </div>

                    <div class="campo">
                        <label for="cidade">Cidade</label>
                        <input type="text" id="cidade" name="cidade"
                               maxlength="100"
                               placeholder="Sua cidade" required>
                    </div>

                </div>

                <div class="acoes">
                    <a href="index.php" class="voltar">Voltar</a>
                    <button type="submit">Criar minha conta</button>
                </div>

            </form>

        </section>

    </div>
</main>

<footer>
    &copy; 2026 Milka Mics. Todos os direitos reservados.
</footer>

</body>
</html>
