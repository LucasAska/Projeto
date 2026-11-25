<?php
require_once __DIR__ . "/config/conexao.php";
require_once __DIR__ . "/config/verifica_acesso.php";

$tipoUsuario = strtolower(trim($_SESSION['usuario_tipo'] ?? ''));



ini_set('display_errors', 0);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once __DIR__ . '/config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cadastro.php');
    exit;
}

$nome        = trim($_POST['nome'] ?? '');
$telefone    = trim($_POST['telefone'] ?? '');
$email       = trim($_POST['email'] ?? '');
$senha       = $_POST['senha'] ?? '';
$cep         = trim($_POST['cep'] ?? '');
$logradouro  = trim($_POST['logradouro'] ?? '');
$numero      = trim($_POST['numero'] ?? '');
$complemento = trim($_POST['complemento'] ?? '');
$bairro      = trim($_POST['bairro'] ?? '');
$cidade      = trim($_POST['cidade'] ?? '');
$uf          = strtoupper(trim($_POST['uf'] ?? ''));


if ($tipoUsuario === 'admin'){
    $tipo        = $_POST['tipo'] ?? '';
}
else{
    $tipo = 'cliente';
}






if (
    $nome === '' || $telefone === '' || $email === '' ||
    $senha === '' || $cep === '' || $logradouro === '' ||
    $numero === '' || $bairro === '' || $cidade === '' ||
    $uf === ''
) {
    exit('Preencha todos os campos obrigatórios.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit('Informe um endereço de e-mail válido.');
}

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

try {
    $sql = "INSERT INTO USUARIOS (
        NOME, TELEFONE, EMAIL, SENHA, CEP, LOGRADOURO,
        NUMERO, COMPLEMENTO, BAIRRO, CIDADE, UF, TIPO
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conexao->prepare($sql);

    $stmt->bind_param(
        "ssssssssssss",
        $nome,
        $telefone,
        $email,
        $senhaHash,
        $cep,
        $logradouro,
        $numero,
        $complemento,
        $bairro,
        $cidade,
        $uf,
        $tipo
    );

    $stmt->execute();

    echo "<!DOCTYPE html>
    <html lang='pt-BR'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Cadastro realizado | Milka Mics</title>
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
                box-shadow: 0 2px 10px rgba(0,0,0,.08);
            }

            .logo {
                color: #a34f70;
                font-size: 25px;
                font-weight: bold;
                text-decoration: none;
            }

            main {
                flex: 1;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 40px 20px;
            }

            .card {
                background: #fff;
                width: 100%;
                max-width: 480px;
                padding: 40px 30px;
                text-align: center;
                border-radius: 12px;
                box-shadow: 0 2px 15px rgba(0,0,0,.08);
            }

            .icone {
                width: 65px;
                height: 65px;
                margin: 0 auto 20px;
                border-radius: 50%;
                background: #f8e8ef;
                color: #a34f70;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 32px;
            }

            h1 {
                color: #a34f70;
                font-size: 27px;
                margin-bottom: 15px;
            }

            p {
                color: #666;
                font-size: 16px;
                line-height: 1.7;
                margin-bottom: 12px;
            }

            .botao {
                display: inline-block;
                margin-top: 18px;
                padding: 13px 25px;
                background: #a34f70;
                color: #fff;
                text-decoration: none;
                border-radius: 6px;
            }

            .botao:hover {
                background: #873d5b;
            }

            .rodape {
                text-align: center;
                padding: 22px;
                background: #fff;
                color: #777;
                font-size: 14px;
            }

            @media (max-width: 600px) {
                .card {
                    padding: 30px 20px;
                }

                h1 {
                    font-size: 23px;
                }
            }
        </style>
        <link rel='stylesheet' href='assets/css/cabecalho.css'>
    </head>
    <body>
        <header class='site-header'>
            <nav class='site-nav'>
                <div class='site-logo-area'>
                    <a class='site-logo' href='index.php'>
                        <img src='assets/images/logo-milka-cabecalho.png' alt='Milka Mics'>
                    </a>
                </div>
            </nav>
        </header>

        <main>
            <section class='card'>
                <div class='icone'>&#10003;</div>
                <h1>Cadastro realizado!</h1>
                <p>Sua conta foi criada com sucesso.</p>
                <p>Agora você já pode fazer login e aproveitar a Milka Mics.</p>
                <a class='botao' href='sign_in.php'>Fazer login</a>
            </section>
        </main>

        <footer class='rodape'>
            &copy; " . date('Y') . " Milka Mics. Todos os direitos reservados.
        </footer>
    </body>
    </html>";

} catch (mysqli_sql_exception $e) {
    if ($e->getCode() == 1062) {
        echo "Este e-mail já está cadastrado. ";
        echo "<a href='cadastro.php'>Voltar ao cadastro</a>";
    } else {
        error_log('Erro no cadastro: ' . $e->getMessage());
        http_response_code(500);
        echo "Não foi possível concluir o cadastro. Tente novamente.";
    }
}
?>
