<?php
require_once __DIR__ . '/config/conexao.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/carrinho.php';

$detalhes    = carrinho_detalhes($conexao);
$logado      = !empty($_SESSION['usuario_id']);
$nomeUsuario = e($_SESSION['usuario_nome'] ?? '');
$tipoUsuario = strtolower(trim($_SESSION['usuario_tipo'] ?? ''));
$qtdItens    = carrinho_quantidade_total();
$token       = csrf_token();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho | Milka Mics</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; background: #fffaf7; color: #333; min-height: 100vh; display: flex; flex-direction: column;}
        header { background: #fff; padding: 20px 6%; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08); }
        nav { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; }
       .logo { display: flex; align-items: center; text-decoration: none; }
       .logo img { height: 85px; width: auto; max-width: 220px; object-fit: contain; display: block; }
        .menu { display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
        .menu a { color: #444; text-decoration: none; font-size: 15px; }
        .menu a:hover { color: #a34f70; }
        .menu .botao { background: #a34f70; color: #fff; padding: 11px 18px; border-radius: 6px; }
        main { flex: 1; width: 100%; max-width: 900px; margin: 0 auto; padding: 40px 20px; }
        h1 { color: #a34f70; margin-bottom: 24px; }
        .btn { display: inline-block; background: #a34f70; color: #fff; border: none; padding: 13px 22px; border-radius: 6px; text-decoration: none; font-size: 15px; cursor: pointer; }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .btn-secundario { background: #fff; color: #a34f70; border: 1px solid #a34f70; }
        .item { display: grid; grid-template-columns: 90px 1fr 110px 120px; gap: 16px; align-items: center; background: #fff; border: 1px solid #f0e6e9; border-radius: 12px; padding: 14px; margin-bottom: 14px; }
        .item img, .sem-foto { width: 90px; height: 90px; object-fit: cover; border-radius: 8px; background: #fff4f7; }
        .sem-foto { display: flex; align-items: center; justify-content: center; color: #b9a3ad; font-size: 12px; }
        .info { display: flex; flex-direction: column; gap: 4px; }
        .info span { color: #777; font-size: 14px; }
        .info .aviso { color: #c92a2a; }
        .item input { width: 100%; height: 40px; padding: 0 10px; border: 1px solid #d9c8cf; border-radius: 6px; font-size: 15px; }
        .subtotal { font-weight: bold; color: #a34f70; text-align: right; }
        .acoes { display: flex; justify-content: flex-end; margin-top: 10px; }
        .resumo { display: flex; justify-content: flex-end; align-items: center; gap: 20px; margin-top: 24px; font-size: 18px; flex-wrap: wrap; }
        .alerta { padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; }
        .alerta-sucesso { background: #d4edda; color: #155724; }
        .alerta-erro { background: #f8d7da; color: #721c24; }
        .vazio { color: #777; font-size: 16px; margin-bottom: 20px; }
        footer { text-align: center; padding: 22px; background: #fff; color: #777; font-size: 14px; margin-top: auto; }
        @media (max-width: 600px) {
            .item { grid-template-columns: 70px 1fr; }
            .item img, .sem-foto { width: 70px; height: 70px; }
            .item .qtd, .item .subtotal { grid-column: 2; }
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
            <a href="produtos.php">Produtos</a>
            <a href="carrinho.php">Carrinho (<?= $qtdItens ?>)</a>

            <?php if ($logado): ?>
                <span>Olá, <?= $nomeUsuario ?>!</span>
                <a href="meus_pedidos.php">Meus pedidos</a>
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
    <h1>Seu carrinho</h1>

    <?php exibir_flash(); ?>

    <?php if (empty($detalhes['linhas'])): ?>
        <p class="vazio">Seu carrinho está vazio.</p>
        <a class="btn" href="produtos.php">Ver produtos</a>
    <?php else: ?>

        <form method="post" action="carrinho_acao.php">
            <input type="hidden" name="csrf" value="<?= e($token) ?>">
            <input type="hidden" name="acao" value="atualizar">

            <?php foreach ($detalhes['linhas'] as $item): ?>
                <div class="item">
                    <?php if ($item['foto']): ?>
                        <img src="<?= e($item['foto']) ?>" alt="<?= e($item['nome']) ?>">
                    <?php else: ?>
                        <div class="sem-foto">Sem foto</div>
                    <?php endif; ?>

                    <div class="info">
                        <strong><?= e($item['nome']) ?></strong>
                        <span>R$ <?= number_format($item['preco'], 2, ',', '.') ?> cada</span>
                        <?php if (!$item['disponivel']): ?>
                            <span class="aviso">Indisponível no momento. Remova este item para finalizar.</span>
                        <?php endif; ?>
                    </div>

                    <div class="qtd">
                        <input type="number"
                               name="quantidade[<?= (int) $item['id'] ?>]"
                               min="0"
                               max="<?= max(1, (int) $item['estoque']) ?>"
                               value="<?= (int) $item['qtd'] ?>"
                               aria-label="Quantidade">
                    </div>

                    <div class="subtotal">R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></div>
                </div>
            <?php endforeach; ?>

            <div class="acoes">
                <button type="submit" class="btn btn-secundario">Atualizar carrinho</button>
            </div>
        </form>

        <p style="color:#777; font-size:14px; margin-top:8px;">Coloque 0 na quantidade para remover o item.</p>

        <div class="resumo">
            <strong>Total: R$ <?= number_format($detalhes['total'], 2, ',', '.') ?></strong>

            <?php if ($logado): ?>
                <form method="post" action="finalizar.php">
                    <input type="hidden" name="csrf" value="<?= e($token) ?>">
                    <button type="submit" class="btn" <?= $detalhes['tudo_disponivel'] ? '' : 'disabled' ?>>
                        Finalizar pedido
                    </button>
                </form>
            <?php else: ?>
                <a class="btn" href="sing_in.php">Entre para finalizar</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>

<footer>
    <p>&copy; <?= date('Y') ?> Milka Mics. Todos os direitos reservados.</p>
</footer>

</body>
</html>
