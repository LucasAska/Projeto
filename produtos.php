<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/conexao.php';
require_once __DIR__ . '/config/carrinho.php';

// Busca as categorias cadastradas
$categorias = $conexao->query(
    "SELECT ID, NOME, SLUG FROM CATEGORIAS ORDER BY NOME"
);

if (!$categorias) {
    die("Erro ao buscar categorias: " . $conexao->error);
}

// Recebe os filtros da página
$slugCategoria = trim($_GET['categoria'] ?? '');
$busca = trim($_GET['busca'] ?? '');
$ordem = $_GET['ordem'] ?? 'recentes';

// Consulta inicial dos produtos ativos
$sql = "SELECT
            p.ID,
            p.NOME,
            p.DESCRICAO,
            p.PRECO,
            p.IMAGEM,
            c.NOME AS CATEGORIA,
            c.SLUG AS CATEGORIA_SLUG
        FROM PRODUTOS p
        INNER JOIN CATEGORIAS c
            ON c.ID = p.CATEGORIA_ID
        WHERE p.ATIVO = 1";

$parametros = [];
$tipos = "";

// Filtro por categoria
if ($slugCategoria !== '') {
    $sql .= " AND c.SLUG = ?";
    $parametros[] = $slugCategoria;
    $tipos .= "s";
}

// Pesquisa por nome ou descrição
if ($busca !== '') {
    $sql .= " AND (p.NOME LIKE ? OR p.DESCRICAO LIKE ?)";
    $parametros[] = "%{$busca}%";
    $parametros[] = "%{$busca}%";
    $tipos .= "ss";
}

// Ordenação
switch ($ordem) {
    case 'menor':
        $sql .= " ORDER BY p.PRECO ASC";
        break;

    case 'maior':
        $sql .= " ORDER BY p.PRECO DESC";
        break;

    case 'nome':
        $sql .= " ORDER BY p.NOME ASC";
        break;

    default:
        $sql .= " ORDER BY p.CRIADO_EM DESC";
        break;
}

// Prepara e executa a consulta
$stmt = $conexao->prepare($sql);

if (!$stmt) {
    die("Erro ao preparar consulta: " . $conexao->error);
}

if (!empty($parametros)) {
    $stmt->bind_param($tipos, ...$parametros);
}

$stmt->execute();
$resultado = $stmt->get_result();

$produtos = [];

while ($produto = $resultado->fetch_assoc()) {
    $produtos[] = $produto;
}

// Título da categoria selecionada
$tituloAtual = "Todos os produtos";

while ($categoria = $categorias->fetch_assoc()) {
    if ($categoria['SLUG'] === $slugCategoria) {
        $tituloAtual = $categoria['NOME'];
    }
}



$usuarioLogado = isset($_SESSION['usuario_id']);
$nomeUsuario = htmlspecialchars(
    $_SESSION['usuario_nome'] ?? 'Usuário',
    ENT_QUOTES,
    'UTF-8'
);

$tipoUsuario = strtolower(trim($_SESSION['usuario_tipo'] ?? ''));

// Funções simples de apresentação
function escapar($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function formatarPreco($valor) {
    return 'R$ ' . number_format((float)$valor, 2, ',', '.');
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= escapar($tituloAtual) ?> | Milka Mics</title>

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
            max-width: 1200px;
            margin: 0 auto;
            padding: 45px 20px 60px;
        }

        .cabecalho-catalogo {
            text-align: center;
            margin-bottom: 35px;
        }

        .cabecalho-catalogo h1 {
            color: #a34f70;
            font-size: 34px;
            margin-bottom: 10px;
        }

        .cabecalho-catalogo p {
            color: #777;
        }

        .filtros {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 30px;
        }

        .categorias {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .chip {
            padding: 9px 16px;
            border: 1px solid #e1d4d9;
            border-radius: 20px;
            color: #873d5b;
            text-decoration: none;
            font-size: 14px;
        }

        .chip.ativo,
        .chip:hover {
            background: #a34f70;
            color: #fff;
        }

        .busca {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .busca input,
        .ordenacao select {
            padding: 10px 12px;
            border: 1px solid #e1d4d9;
            border-radius: 6px;
            font-size: 14px;
        }

        .btn-principal {
            padding: 10px 17px;
            background: #a34f70;
            color: #fff;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-principal:hover {
            background: #873d5b;
        }

        .ordenacao {
            margin-bottom: 25px;
            text-align: right;
        }

        .contador {
            color: #777;
            font-size: 14px;
            margin-bottom: 18px;
        }

        .grade-produtos {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
            gap: 25px;
        }

        .card-produto {
            background: #fff;
            border: 1px solid #f0e6e9;
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card-produto:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 18px rgba(0, 0, 0, 0.09);
        }

        .imagem-produto {
            height: 220px;
            background: #fff4f7;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .imagem-produto img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .sem-imagem {
            color: #a34f70;
            font-size: 48px;
            font-weight: bold;
        }

        .info-produto {
            padding: 18px;
        }

        .categoria-produto {
            color: #a34f70;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .nome-produto {
            font-size: 18px;
            margin: 9px 0;
        }

        .descricao-produto {
            color: #777;
            font-size: 13px;
            line-height: 1.5;
            min-height: 40px;
        }

        .preco-produto {
            display: block;
            color: #873d5b;
            font-size: 20px;
            font-weight: bold;
            margin: 15px 0;
        }

        .mensagem-vazia {
            text-align: center;
            padding: 55px 20px;
            background: #fff;
            border-radius: 10px;
            color: #777;
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

            .filtros {
                flex-direction: column;
                align-items: stretch;
            }

            .busca input {
                flex: 1;
                min-width: 0;
            }

            .ordenacao {
                text-align: left;
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
            <a href="carrinho.php">Carrinho (<?= carrinho_quantidade_total() ?>)</a>

            <?php if ($usuarioLogado): ?>
                <span>Olá, <?= $nomeUsuario ?>!</span>
                <a href="meus_pedidos.php">Meus pedidos</a>

                <?php if ($tipoUsuario === 'admin'): ?>
                    <a href="admin/index.php">Painel administrativo</a>
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

    <div class="cabecalho-catalogo">
        <h1><?= escapar($tituloAtual) ?></h1>
        <p>Conheça nossas peças artesanais feitas com carinho.</p>
    </div>

    <div class="filtros">

        <div class="categorias">
            <a class="chip <?= $slugCategoria === '' ? 'ativo' : '' ?>"
               href="produtos.php">Todos</a>

            <?php
            $categorias->data_seek(0);
            while ($categoria = $categorias->fetch_assoc()):
            ?>
                <a class="chip <?= $slugCategoria === $categoria['SLUG'] ? 'ativo' : '' ?>"
                   href="produtos.php?categoria=<?= urlencode($categoria['SLUG']) ?>">
                    <?= escapar($categoria['NOME']) ?>
                </a>
            <?php endwhile; ?>
        </div>

        <form class="busca" method="get" action="produtos.php">
            <?php if ($slugCategoria !== ''): ?>
                <input type="hidden" name="categoria" value="<?= escapar($slugCategoria) ?>">
            <?php endif; ?>

            <input
                type="text"
                name="busca"
                placeholder="Buscar produto..."
                value="<?= escapar($busca) ?>"
            >

            <button class="btn-principal" type="submit">Buscar</button>
        </form>

    </div>

    <div class="ordenacao">
        <form method="get" action="produtos.php">
            <?php if ($slugCategoria !== ''): ?>
                <input type="hidden" name="categoria" value="<?= escapar($slugCategoria) ?>">
            <?php endif; ?>

            <?php if ($busca !== ''): ?>
                <input type="hidden" name="busca" value="<?= escapar($busca) ?>">
            <?php endif; ?>

            <label for="ordem">Ordenar por: </label>

            <select name="ordem" id="ordem" onchange="this.form.submit()">
                <option value="recentes" <?= $ordem === 'recentes' ? 'selected' : '' ?>>Mais recentes</option>
                <option value="menor" <?= $ordem === 'menor' ? 'selected' : '' ?>>Menor preço</option>
                <option value="maior" <?= $ordem === 'maior' ? 'selected' : '' ?>>Maior preço</option>
                <option value="nome" <?= $ordem === 'nome' ? 'selected' : '' ?>>Nome A-Z</option>
            </select>
        </form>
    </div>

    <p class="contador">
        <?= count($produtos) ?> produto(s) encontrado(s)
    </p>

    <?php if (empty($produtos)): ?>

        <div class="mensagem-vazia">
            <h2>Nenhum produto encontrado</h2>
            <p>Tente mudar os filtros ou pesquisar outro termo.</p>
        </div>

    <?php else: ?>

        <div class="grade-produtos">

            <?php foreach ($produtos as $p): ?>

                <article class="card-produto">

                    
<?php
$nomeArquivoImagem = basename($p['IMAGEM'] ?? '');
$caminhoImagem = __DIR__ . '/assets/images/produtos/' . $nomeArquivoImagem;
$urlImagem = 'assets/images/produtos/' . rawurlencode($nomeArquivoImagem);
?>

<div class="imagem-produto">

    <?php if ($nomeArquivoImagem !== '' && is_file($caminhoImagem)): ?>
        <img
            src="<?= escapar($urlImagem) ?>"
            alt="<?= escapar($p['NOME']) ?>"
        >
    <?php else: ?>
        <span class="sem-imagem">
            <?= escapar(mb_substr($p['NOME'], 0, 1)) ?>
        </span>
    <?php endif; ?>

</div>


                    <div class="info-produto">

                        <span class="categoria-produto">
                            <?= escapar($p['CATEGORIA']) ?>
                        </span>

                        <h2 class="nome-produto">
                            <?= escapar($p['NOME']) ?>
                        </h2>

                        <p class="descricao-produto">
                            <?= escapar($p['DESCRICAO']) ?>
                        </p>

                        <span class="preco-produto">
                            <?= formatarPreco($p['PRECO']) ?>
                        </span>

                        <a
                            class="btn-principal"
                            href="produto.php?id=<?= (int)$p['ID'] ?>"
                        >
                            Ver detalhes
                        </a>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</main>

<footer>
    <p>&copy; <?= date('Y') ?> Milka Mics. Todos os direitos reservados.</p>
</footer>

</body>
</html>
