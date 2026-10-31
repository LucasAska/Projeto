<?php
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../config/auth.php';
exigir_admin();

$resultado = $conexao->query(
    "SELECT ID, CATEGORIA_ID, NOME, PRECO, ESTOQUE, IMAGEM, ATIVO
     FROM PRODUTOS
     ORDER BY ID DESC"
);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Produtos</title>
</head>
<body>
    <h1>Produtos</h1>
    <?php exibir_flash(); ?>

    <p>
        <a href="../index.php">← Painel</a> |
        <a href="incluir.php">+ Novo produto</a>
    </p>

    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Imagem</th>
            <th>Nome</th>
            <th>Categoria (ID)</th>
            <th>Preço</th>
            <th>Estoque</th>
            <th>Ativo</th>
            <th>Ações</th>
        </tr>

        <?php if ($resultado->num_rows === 0): ?>
            <tr><td colspan="8">Nenhum produto cadastrado.</td></tr>
        <?php endif; ?>

        <?php while ($p = $resultado->fetch_assoc()): ?>
            <tr>
                <td><?= (int) $p['ID'] ?></td>
                <td>
                    <?php if (!empty($p['IMAGEM'])): ?>
                        <img src="../../uploads/produtos/<?= e($p['IMAGEM']) ?>" alt="" width="60">
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
                <td><?= e($p['NOME']) ?></td>
                <td><?= (int) $p['CATEGORIA_ID'] ?></td>
                <td>R$ <?= number_format((float) $p['PRECO'], 2, ',', '.') ?></td>
                <td><?= (int) $p['ESTOQUE'] ?></td>
                <td><?= $p['ATIVO'] ? 'Sim' : 'Não' ?></td>
                <td>
                    <a href="alterar.php?id=<?= (int) $p['ID'] ?>">Alterar</a> |
                    <a href="excluir.php?id=<?= (int) $p['ID'] ?>">Excluir</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>