<?php
$tituloPagina = 'Produtos — Milka Mics';
require_once __DIR__ . '/_header.php';

$produtos = $pdo->query(
    'SELECT p.*, c.nome AS categoria
     FROM produtos p JOIN categorias c ON c.id = p.categoria_id
     ORDER BY p.id DESC'
)->fetchAll();
?>

<div class="titulo-secao">
    <h2>Produtos</h2>
    <a class="btn-principal" style="padding:10px 24px;font-size:.88rem"
       href="<?= url('admin/produto_form.php') ?>">+ Novo produto</a>
</div>

<?php if (!$produtos): ?>
    <div class="alerta alerta-info">Nenhum produto cadastrado.</div>
<?php else: ?>
    <table class="tabela">
        <thead>
            <tr><th>ID</th><th>Imagem</th><th>Nome</th><th>Categorias</th>
                <th>Preço</th><th>Estoque</th><th>Status</th><th>Ações</th></tr>
        </thead>
        <tbody>
            <?php foreach ($produtos as $p): $img = produto_imagem_url($p['imagem']); ?>
                <tr>
                    <td><?= (int)$p['id'] ?></td>
                    <td style="width:70px">
                        <?php if ($img): ?>
                            <img src="<?= e($img) ?>" alt="" style="width:52px;height:52px;object-fit:cover;border-radius:8px">
                        <?php else: ?>
                            <div style="width:52px;height:52px;border-radius:8px;background:var(--oliva-suave);
                                        display:flex;align-items:center;justify-content:center;
                                        color:var(--oliva-claro);font-family:'Playfair Display',serif">
                                <?= e(placeholder_inicial($p['nome'])) ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td><?= e($p['nome']) ?></td>
                    <td>
                        <?php
                            $cats = categorias_do_produto((int)$p['id']);
                            echo $cats ? e(implode(' + ', array_column($cats, 'nome'))) : '—';
                        ?>
                    </td>
                    <td><?= moeda($p['preco']) ?></td>
                    <td><?= (int)$p['estoque'] ?></td>
                    <td>
                        <?= $p['ativo']
                            ? '<span class="chip ativo" style="padding:3px 12px">Ativo</span>'
                            : '<span class="chip" style="padding:3px 12px">Inativo</span>' ?>
                    </td>
                    <td style="white-space:nowrap">
                        <a class="btn-secundario" style="padding:6px 16px;font-size:.8rem"
                           href="<?= url('admin/produto_form.php?id=' . $p['id']) ?>">Editar</a>
                        <a class="btn-perigo" style="margin-left:6px"
                           data-confirmar="Excluir o produto '<?= e($p['nome']) ?>'?"
                           href="<?= url('admin/produto_excluir.php?id=' . $p['id']) ?>">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/_footer.php'; ?>