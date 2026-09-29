<?php
require_once __DIR__ . '/../config/config.php';
exigir_admin();

$id = (int)($_GET['id'] ?? 0);

$st = $pdo->prepare('SELECT * FROM produtos WHERE id = ?');
$st->execute([$id]);
$produto = $st->fetch();

if (!$produto) {
    set_flash('erro', 'Produto não encontrado.');
    redirecionar('admin/produtos.php');
}

$st = $pdo->prepare('SELECT COUNT(*) FROM pedido_itens WHERE produto_id = ?');
$st->execute([$id]);

if ((int)$st->fetchColumn() > 0) {
    $pdo->prepare('UPDATE produtos SET ativo = 0 WHERE id = ?')->execute([$id]);
    set_flash('info', 'O produto possui pedidos vinculados e foi apenas desativado.');
} else {
    if ($produto['imagem'] && is_file(UPLOAD_DIR . $produto['imagem'])) {
        @unlink(UPLOAD_DIR . $produto['imagem']);
    }
    $pdo->prepare('DELETE FROM produtos WHERE id = ?')->execute([$id]);
    set_flash('sucesso', 'Produto excluído com sucesso.');
}

redirecionar('admin/produtos.php');