<?php
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../config/auth.php';
exigir_admin();

$resultado = $conexao->query(
    "SELECT p.ID, p.NOME, p.PRECO, p.ESTOQUE, p.IMAGEM, p.ATIVO, c.NOME AS CATEGORIA
     FROM PRODUTOS p
     LEFT JOIN CATEGORIAS c ON c.ID = p.CATEGORIA_ID
     ORDER BY p.ID DESC"
);

$titulo     = 'Itens';
$secaoAtual = 'itens';
require __DIR__ . '/../_topo.php';
?>

<div class="cabecalho">
    <h1>Itens do catálogo</h1>
    <p>Cadastre, altere ou remova as peças exibidas na vitrine.</p>
</div>

<div class="card">
    <div class="topo-tabela">
        <h2 class="secao-titulo titulo-sem-borda">Produtos cadastrados</h2>
        <a href="incluir.php" class="btn-primario">+ Novo produto</a>
    </div>

    <?php exibir_flash(); ?>

    <div class="tabela-responsiva">
        <table class="tabela-admin">
            <thead>
                <tr>
                    <th>Imagem</th>
                    <th>Nome</th>
                    <th>Categoria</th>
                    <th>Preço</th>
                    <th>Estoque</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultado->num_rows === 0): ?>
                    <tr><td colspan="7">Nenhum produto cadastrado ainda.</td></tr>
                <?php endif; ?>

                <?php while ($p = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php if (!empty($p['IMAGEM'])): ?>
                                <img class="miniatura" src="/assets/images/produtos/<?= e($p['IMAGEM']) ?>" alt="">
                            <?php else: ?>
                                <span class="texto-suave">Sem imagem</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= e($p['NOME']) ?></strong><br>
                            <span class="texto-suave">ID <?= (int) $p['ID'] ?></span>
                        </td>
                        <td><?= e($p['CATEGORIA'] ?? '—') ?></td>
                        <td>R$ <?= number_format((float) $p['PRECO'], 2, ',', '.') ?></td>
                        <td><?= (int) $p['ESTOQUE'] ?></td>
                        <td>
                            <?php if ($p['ATIVO']): ?>
                                <span class="tag-tipo tag-ativo">Ativo</span>
                            <?php else: ?>
                                <span class="tag-tipo tag-inativo">Inativo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="acoes-linha">
                                <a class="btn-tabela secundario" href="alterar.php?id=<?= (int) $p['ID'] ?>">Alterar</a>
                                <a class="btn-tabela btn-excluir" href="excluir.php?id=<?= (int) $p['ID'] ?>">Excluir</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../_rodape.php'; ?>