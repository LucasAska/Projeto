<?php
require_once __DIR__ . '/config/conexao.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/carrinho.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valido()) {
    flash('erro', 'Requisição inválida. Tente novamente.');
    header('Location: carrinho.php');
    exit;
}

$acao = $_POST['acao'] ?? '';

// Volta para a página de onde veio, só se for uma página do próprio site
$voltar = $_POST['voltar'] ?? 'carrinho.php';
if (!is_string($voltar) || !preg_match('/^[a-z_]+\.php(\?id=\d+)?$/', $voltar)) {
    $voltar = 'carrinho.php';
}

if ($acao === 'adicionar') {
    $id   = (int) ($_POST['produto_id'] ?? 0);
    $qtd  = (int) ($_POST['quantidade'] ?? 1);
    $erro = carrinho_adicionar($conexao, $id, $qtd);

    if ($erro === '') {
        flash('sucesso', 'Produto adicionado ao carrinho.');
    } else {
        flash('erro', $erro);
    }
} elseif ($acao === 'atualizar') {
    $quantidades = is_array($_POST['quantidade'] ?? null) ? $_POST['quantidade'] : [];
    $erroFinal   = '';

    foreach ($quantidades as $id => $qtd) {
        $erro = carrinho_atualizar($conexao, (int) $id, (int) $qtd);
        if ($erro !== '') {
            $erroFinal = $erro;
        }
    }

    if ($erroFinal !== '') {
        flash('erro', $erroFinal);
    } else {
        flash('sucesso', 'Carrinho atualizado.');
    }
}

header('Location: ' . $voltar);
exit;