<?php
require_once __DIR__ . '/config/conexao.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/carrinho.php';

if (empty($_SESSION['usuario_id'])) {
    flash('erro', 'Entre na sua conta para finalizar o pedido.');
    header('Location: sing_in.php');
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

try {
    $conexao->begin_transaction();

    // 1) Confere cada produto e trava a linha (FOR UPDATE) para outro pedido não pegar o mesmo estoque
    $linhas = [];
    foreach ($carrinho as $produtoId => $qtd) {
        $stmt = $conexao->prepare("SELECT NOME, PRECO, ESTOQUE, ATIVO FROM PRODUTOS WHERE ID = ? FOR UPDATE");
        $stmt->bind_param('i', $produtoId);
        $stmt->execute();
        $produto = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$produto || (int) $produto['ATIVO'] !== 1) {
            throw new RuntimeException('Um dos produtos não está mais disponível. Remova-o do carrinho.');
        }
        if ((int) $produto['ESTOQUE'] < (int) $qtd) {
            throw new RuntimeException('Estoque insuficiente para "' . $produto['NOME'] . '".');
        }

        // O preço vem do banco, nunca da sessão
        $linhas[] = [
            'id'    => (int) $produtoId,
            'qtd'   => (int) $qtd,
            'preco' => (float) $produto['PRECO'],
        ];
    }

    // 2) Cria o pedido
    $stmt = $conexao->prepare("INSERT INTO PEDIDOS (USUARIO_ID, STATUS) VALUES (?, 'pendente')");
    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();
    $pedidoId = (int) $conexao->insert_id;
    $stmt->close();

    // 3) Grava os itens e baixa o estoque
    $stmtItem    = $conexao->prepare("INSERT INTO PEDIDOS_ITENS (PEDIDO_ID, PRODUTO_ID, QUANTIDADE, PRECO_UNIT) VALUES (?, ?, ?, ?)");
    $stmtEstoque = $conexao->prepare("UPDATE PRODUTOS SET ESTOQUE = ESTOQUE - ? WHERE ID = ?");

    foreach ($linhas as $l) {
        $stmtItem->bind_param('iiid', $pedidoId, $l['id'], $l['qtd'], $l['preco']);
        $stmtItem->execute();

        $stmtEstoque->bind_param('ii', $l['qtd'], $l['id']);
        $stmtEstoque->execute();
    }

    $stmtItem->close();
    $stmtEstoque->close();

    $conexao->commit();

    unset($_SESSION['carrinho']);
    flash('sucesso', 'Pedido #' . $pedidoId . ' realizado com sucesso!');
} catch (Throwable $ex) {
    $conexao->rollback();

    $mensagem = $ex instanceof RuntimeException
        ? $ex->getMessage()
        : 'Não foi possível finalizar o pedido. Tente novamente.';
    flash('erro', $mensagem);
}

header('Location: carrinho.php');
exit;