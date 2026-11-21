<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/conexao.php';
require_once __DIR__ . '/config/carrinho.php';

// Recebe e valida o ID do produto pela URL
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    http_response_code(404);
    $produto = null;
} else {
    // Busca o produto ativo pelo ID
    $sql = "SELECT
                p.ID,
                p.NOME,
                p.DESCRICAO,
                p.PRECO,
                p.IMAGEM,
                p.ESTOQUE,
                c.NOME AS CATEGORIA
            FROM PRODUTOS p
            INNER JOIN CATEGORIAS c
                ON c.ID = p.CATEGORIA_ID
            WHERE p.ID = ?
              AND p.ATIVO = 1
            LIMIT 1";

    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        die("Erro ao preparar consulta: " . $conexao->error);
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $produto = $resultado->fetch_assoc();

    if (!$produto) {
        http_response_code(404);
    }

    $stmt->close();
}

// Funções de apresentação
function escaparDetalhes($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function formatarPrecoDetalhes($valor) {
    return 'R$ ' . number_format((float)$valor, 2, ',', '.');
}

// Dados do usuário
$usuarioLogado = isset($_SESSION['usuario_id']);

$nomeUsuario = escaparDetalhes(
    $_SESSION['usuario_nome'] ?? 'Usuário'
);

$tipoUsuario = strtolower(
    trim($_SESSION['usuario_tipo'] ?? '')
);

// Configuração da imagem
$nomeArquivoImagem = basename($produto['IMAGEM'] ?? '');

$caminhoImagem = __DIR__
    . '/assets/images/produtos/'
    . $nomeArquivoImagem;

$urlImagem = 'assets/images/produtos/'
    . rawurlencode($nomeArquivoImagem);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= $produto
            ? escaparDetalhes($produto['NOME']) . ' | Milka Mics'
            : 'Produto não encontrado | Milka Mics'
        ?>
    </title>

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

        main {
            max-width: 1100px;
            margin: 0 auto;
            padding: 45px 20px 60px;
        }

        .voltar {
            display: inline-block;
            color: #a34f70;
            text-decoration: none;
            margin-bottom: 25px;
            font-size: 15px;
        }

        .voltar:hover {
            text-decoration: underline;
        }

        .detalhes-produto {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 45px;
            align-items: start;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #f0e6e9;
        }

        .imagem-detalhes {
            width: 100%;
            height: 420px;
            background: #fff4f7;
            border-radius: 10px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .imagem-detalhes img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .sem-imagem {
            color: #a34f70;
            font-size: 70px;
            font-weight: bold;
        }

        .informacoes-produto {
            padding: 10px 0;
        }

        .categoria {
            display: inline-block;
            color: #a34f70;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 15px;
        }

        .informacoes-produto h1 {
            color: #333;
            font-size: 32px;
            margin-bottom: 20px;
        }

        .preco {
            display: block;
            color: #873d5b;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 25px;
        }

        .titulo-descricao {
            color: #444;
            font-size: 17px;
            margin-bottom: 10px;
        }

        .descricao {
            color: #777;
            font-size: 15px;
            line-height: 1.8;
            white-space: pre-line;
            margin-bottom: 30px;
        }

        .btn-principal {
            display: inline-block;
            padding: 12px 22px;
            background: #a34f70;
            color: #fff;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            font-size: 15px;
        }

        .btn-principal:hover {
            background: #873d5b;
        }

        .mensagem-vazia {
            text-align: center;
            padding: 65px 20px;
            background: #fff;
            border-radius: 10px;
            color: #777;
        }

        .mensagem-vazia h1 {
            color: #a34f70;
            margin-bottom: 15px;
        }

        .mensagem-vazia p {
            margin-bottom: 25px;
        }

        footer {
            text-align: center;
            padding: 22px;
            background: #fff;
            color: #777;
            font-size: 14px;
        }

        @media (max-width: 700px) {
            nav {
                flex-direction: column;
            }

            .menu {
                justify-content: center;
            }

            .detalhes-produto {
                grid-template-columns: 1fr;
                gap: 25px;
                padding: 20px;
            }

            .imagem-detalhes {
                height: 300px;
            }

            .informacoes-produto h1 {
                font-size: 26px;
            }

            .preco {
                font-size: 24px;
            }
        }
    </style>
    <link rel="stylesheet" href="assets/css/cabecalho.css">
</head>

<body>

<?php require __DIR__ . '/includes/cabecalho.php'; ?>

<main>

    <?php if (!$produto): ?>

        <div class="mensagem-vazia">
            <h1>Produto não encontrado</h1>

            <p>
                Esse produto não existe ou não está mais disponível.
            </p>

            <a class="btn-principal" href="produtos.php">
                Voltar ao catálogo
            </a>
        </div>

    <?php else: ?>

        <a class="voltar" href="produtos.php">
            &larr; Voltar ao catálogo
        </a>

        <section class="detalhes-produto">

            <div class="imagem-detalhes">

                <?php if (
                    $nomeArquivoImagem !== ''
                    && is_file($caminhoImagem)
                ): ?>

                    <img
                        src="<?= escaparDetalhes($urlImagem) ?>"
                        alt="<?= escaparDetalhes($produto['NOME']) ?>"
                    >

                <?php else: ?>

                    <span class="sem-imagem">
                        <?= escaparDetalhes(
                            mb_substr($produto['NOME'], 0, 1)
                        ) ?>
                    </span>

                <?php endif; ?>

            </div>

            <div class="informacoes-produto">

                <span class="categoria">
                    <?= escaparDetalhes($produto['CATEGORIA']) ?>
                </span>

                <h1>
                    <?= escaparDetalhes($produto['NOME']) ?>
                </h1>

                <span class="preco">
                    <?= formatarPrecoDetalhes($produto['PRECO']) ?>
                </span>

                <h2 class="titulo-descricao">
                    Descrição do produto
                </h2>

                <p class="descricao">
                    <?= escaparDetalhes(
                        $produto['DESCRICAO'] ?: 'Descrição não disponível.'
                    ) ?>
                </p>

                <?php $aviso = estoque_aviso((int) $produto['ESTOQUE']); if ($aviso !== '' && (int) $produto['ESTOQUE'] > 0): ?>
                    <p style="color:#c92a2a; font-weight:bold; margin:10px 0;"><?= htmlspecialchars($aviso, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <?php if ((int) $produto['ESTOQUE'] > 0): ?>
                    <form method="post" action="carrinho_acao.php" class="form-compra"
                          style="display:flex; gap:10px; align-items:center; margin:20px 0; flex-wrap:wrap;">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="acao" value="adicionar">
                        <input type="hidden" name="produto_id" value="<?= (int) $produto['ID'] ?>">
                        <input type="number" name="quantidade" value="1" min="1"
                               max="<?= (int) $produto['ESTOQUE'] ?>"
                               style="width:90px; height:44px; padding:0 10px; border:1px solid #d9c8cf; border-radius:6px;">
                        <button type="submit" class="btn-principal">Adicionar ao carrinho</button>
                    </form>
                <?php else: ?>
                    <p style="color:#c92a2a; margin:20px 0;">Produto esgotado.</p>
                <?php endif; ?>

                <a class="btn-principal" href="produtos.php">
                    Voltar aos produtos
                </a>

            </div>

        </section>

    <?php endif; ?>

</main>

<footer>
    <p>
        &copy; <?= date('Y') ?> Milka Mics.
        Todos os direitos reservados.
    </p>
</footer>

</body>
</html>
