<?php
require_once __DIR__ . '/config/conexao.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/carrinho.php';

if (empty($_SESSION['usuario_id'])) {
    flash('erro', 'Entre na sua conta para finalizar o pedido.');
    header('Location: sign_in.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valido()) {
    flash('erro', 'Requisição inválida. Tente novamente.');
    header('Location: carrinho.php');
    exit;
}

$carrinho = $_SESSION['carrinho'] ?? [];

if (empty($carrinho)) {
    flash('erro', 'Seu carrinho está vazio.');
    header('Location: carrinho.php');
    exit;
}

$usuarioId = (int) $_SESSION['usuario_id'];
$transacaoAberta = false;

try {
    $conexao->begin_transaction();
    $transacaoAberta = true;

    // 1. Valida os produtos e consulta os preços diretamente no banco
    $linhas = [];

    foreach ($carrinho as $produtoId => $qtd) {
        $produtoId = (int) $produtoId;
        $qtd = (int) $qtd;

        if ($qtd < 1) {
            throw new RuntimeException('Quantidade de produto inválida.');
        }

        $stmt = $conexao->prepare(
            "SELECT NOME, PRECO, ESTOQUE, ATIVO
             FROM PRODUTOS
             WHERE ID = ?
             FOR UPDATE"
        );

        $stmt->bind_param('i', $produtoId);
        $stmt->execute();

        $produto = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$produto || (int) $produto['ATIVO'] !== 1) {
            throw new RuntimeException(
                'Um dos produtos não está mais disponível.'
            );
        }

        if ((int) $produto['ESTOQUE'] < $qtd) {
            throw new RuntimeException(
                'Estoque insuficiente para "' . $produto['NOME'] . '".'
            );
        }

        $preco = (float) $produto['PRECO'];

        $linhas[] = [
            'id' => $produtoId,
            'nome' => $produto['NOME'],
            'qtd' => $qtd,
            'preco' => $preco,
            'subtotal' => $preco * $qtd
        ];
    }

    // 2. Registra o pedido sem armazenar dados de entrega
    $stmt = $conexao->prepare(
        "INSERT INTO PEDIDOS (USUARIO_ID, STATUS)
         VALUES (?, 'pendente')"
    );

    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();

    $pedidoId = (int) $conexao->insert_id;
    $stmt->close();

    // 3. Registra os itens do pedido
    $stmtItem = $conexao->prepare(
        "INSERT INTO PEDIDOS_ITENS
         (PEDIDO_ID, PRODUTO_ID, QUANTIDADE, PRECO_UNIT)
         VALUES (?, ?, ?, ?)"
    );

    $stmtEstoque = $conexao->prepare(
        "UPDATE PRODUTOS
         SET ESTOQUE = ESTOQUE - ?
         WHERE ID = ? AND ESTOQUE >= ?"
    );

    foreach ($linhas as $l) {
        $produtoId = $l['id'];
        $quantidade = $l['qtd'];
        $precoUnitario = $l['preco'];

        $stmtItem->bind_param(
            'iiid',
            $pedidoId,
            $produtoId,
            $quantidade,
            $precoUnitario
        );
        $stmtItem->execute();

        $stmtEstoque->bind_param(
            'iii', $quantidade, $produtoId, $quantidade
        );
        $stmtEstoque->execute();
    }

    $stmtItem->close();
    $stmtEstoque->close();

    // 4. Confirma o registro no banco
    $conexao->commit();
    $transacaoAberta = false;

    // 5. Limpa o carrinho
    unset($_SESSION['carrinho']);

    // 6. Monta a mensagem para o WhatsApp
   $mensagem = "Ol\u{00E1}! Gostaria de confirmar a inten\u{00E7}\u{00E3}o de compra do pedido #{$pedidoId}.\n\n";
    $total = 0;

    foreach ($linhas as $l) {
        $mensagem .= $l['nome'] . "\n";
        $mensagem .= "Quantidade: {$l['qtd']} x R$ ";
        $mensagem .= number_format($l['preco'], 2, ',', '.');
        $mensagem .= " = R$ ";
        $mensagem .= number_format($l['subtotal'], 2, ',', '.');
        $mensagem .= "\n\n";

        $total += $l['subtotal'];
    }

    $mensagem .= "Total: R$ " . number_format($total, 2, ',', '.');

    $mensagem .= "\n\nAguardo confirma\u{00E7}\u{00E3}o. Obrigado!";
    // Número fictício para demonstração acadêmica
    // Substitua pelo número real da loja quando necessário.
    $whatsappLoja = '5500000000000';

    $linkWhatsApp = 'https://wa.me/' . $whatsappLoja
        . '?text=' . rawurlencode($mensagem);

    header('Location: ' . $linkWhatsApp);
    exit;

} catch (Throwable $ex) {
    if ($transacaoAberta) {
        $conexao->rollback();
    }

    $mensagemErro = $ex instanceof RuntimeException
        ? $ex->getMessage()
        : 'Não foi possível finalizar o pedido. Tente novamente.';

    flash('erro', $mensagemErro);

    header('Location: carrinho.php');
    exit;
}
