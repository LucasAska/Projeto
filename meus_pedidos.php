<?php
require_once __DIR__ . '/config/conexao.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/carrinho.php';

if (empty($_SESSION['usuario_id'])) {
    flash('erro', 'Entre na sua conta para ver seus pedidos.');
    header('Location: sing_in.php');
    exit;
}

$usuarioId   = (int) $_SESSION['usuario_id'];
$nomeUsuario = e($_SESSION['usuario_nome'] ?? '');
$tipoUsuario = strtolower(trim($_SESSION['usuario_tipo'] ?? ''));
$qtdItens    = carrinho_quantidade_total();

$rotulosStatus = [
    'pendente'  => 'Aguardando atendimento',
    'atendido'  => 'Atendido',
    'cancelado' => 'Cancelado',
];

// Só os pedidos do cliente logado, do mais recente para o mais antigo
$stmt = $conexao->prepare("SELECT ID, STATUS, CRIADO_EM FROM PEDIDOS WHERE USUARIO_ID = ? ORDER BY ID DESC");
$stmt->bind_param('i', $usuarioId);
$stmt->execute();
$pedidos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Itens e total de cada pedido
$stmtItens = $conexao->prepare(
    "SELECT i.QUANTIDADE, i.PRECO_UNIT, p.NOME
     FROM PEDIDOS_ITENS i
     INNER JOIN PRODUTOS p ON p.ID = i.PRODUTO_ID
     WHERE i.PEDIDO_ID = ?"
);

foreach ($pedidos as $indice => $pedido) {
    $pedidoId = (int) $pedido['ID'];
    $stmtItens->bind_param('i', $pedidoId);
    $stmtItens->execute();
    $itens = $stmtItens->get_result()->fetch_all(MYSQLI_ASSOC);

    $total = 0.0;
    foreach ($itens as $item) {
        $total += (float) $item['PRECO_UNIT'] * (int) $item['QUANTIDADE'];
    }

    $pedidos[$indice]['itens'] = $itens;
    $pedidos[$indice]['total'] = $total;
}
$stmtItens->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus pedidos | Milka Mics</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; background: #fffaf7; color: #333; min-height: 100vh; }
        header { background: #fff; padding: 20px 6%; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08); }
        nav { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; }
        .logo { color: #a34f70; font-size: 25px; font-weight: bold; text-decoration: none; }
        .menu { display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
        .menu a { color: #444; text-decoration: none; font-size: 15px; }
        .menu a:hover { color: #a34f70; }
        .menu .botao { background: #a34f70; color: #fff; padding: 11px 18px; border-radius: 6px; }
        main { max-width: 900px; margin: 0 auto; padding: 40px 20px; }
        h1 { color: #a34f70; margin-bottom: 24px; }
        .btn { display: inline-block; background: #a34f70; color: #fff; padding: 13px 22px; border-radius: 6px; text-decoration: none; font-size: 15px; }
        .alerta { padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; }
        .alerta-sucesso { background: #d4edda; color: #155724; }
        .alerta-erro { background: #f8d7da; color: #721c24; }
        .vazio { color: #777; font-size: 16px; margin-bottom: 20px; }
        .pedido { background: #fff; border: 1px solid #f0e6e9; border-radius: 12px; padding: 20px; margin-bottom: 18px; }
        .cabecalho-pedido { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 12px; }
        .data { color: #777; font-size: 13px; margin-left: 10px; }
        .status { padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: bold; }
        .status-pendente { background: #fff3cd; color: #856404; }
        .status-atendido { background: #d4edda; color: #155724; }
        .status-cancelado { background: #f8d7da; color: #721c24; }
        .itens { list-style: none; border-top: 1px solid #f0e6e9; padding-top: 10px; }
        .itens li { display: flex; justify-content: space-between; padding: 6px 0; font-size: 15px; }
        .total { text-align: right; font-weight: bold; color: #a34f70; margin-top: 10px; font-size: 16px; }
        footer { text-align: center; padding: 22px; background: #fff; color: #777; font-size: 14px; margin-top: 40px; }
    </style>
    <link rel="stylesheet" href="assets/css/cabecalho.css">
</head>
<body>

<?php require __DIR__ . '/includes/cabecalho.php'; ?>

<main>
    <h1>Meus pedidos</h1>

    <?php exibir_flash(); ?>

    <?php if (empty($pedidos)): ?>
        <p class="vazio">Você ainda não fez nenhum pedido.</p>
        <a class="btn" href="produtos.php">Ver produtos</a>
    <?php else: ?>

        <?php foreach ($pedidos as $pedido): ?>
            <section class="pedido">
                <div class="cabecalho-pedido">
                    <div>
                        <strong>Pedido #<?= (int) $pedido['ID'] ?></strong>
                        <span class="data"><?= date('d/m/Y H:i', strtotime($pedido['CRIADO_EM'])) ?></span>
                    </div>

                    <span class="status status-<?= e($pedido['STATUS']) ?>">
                        <?= e($rotulosStatus[$pedido['STATUS']] ?? $pedido['STATUS']) ?>
                    </span>
                </div>

                <ul class="itens">
                    <?php foreach ($pedido['itens'] as $item): ?>
                        <li>
                            <span><?= (int) $item['QUANTIDADE'] ?>x <?= e($item['NOME']) ?></span>
                            <span>R$ <?= number_format((float) $item['PRECO_UNIT'] * (int) $item['QUANTIDADE'], 2, ',', '.') ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="total">Total: R$ <?= number_format($pedido['total'], 2, ',', '.') ?></div>
            </section>
        <?php endforeach; ?>

    <?php endif; ?>
</main>

<footer>
    <p>&copy; <?= date('Y') ?> Milka Mics. Todos os direitos reservados.</p>
</footer>

</body>
</html>