<?php
$tituloPagina = 'Usuários — Milka Mics';
require_once __DIR__ . '/_header.php';

$busca = trim($_GET['busca'] ?? '');
$tipo  = $_GET['tipo'] ?? '';

$sql = 'SELECT * FROM usuarios WHERE 1=1';
$params = [];

if ($busca !== '') {
    $sql .= ' AND (nome LIKE ? OR email LIKE ? OR celular LIKE ?)';
    $params[] = "%{$busca}%";
    $params[] = "%{$busca}%";
    $params[] = "%{$busca}%";
}
if (in_array($tipo, ['cliente', 'admin'], true)) {
    $sql .= ' AND tipo = ?';
    $params[] = $tipo;
}
$sql .= ' ORDER BY criado_em DESC';

$st = $pdo->prepare($sql);
$st->execute($params);
$usuarios = $st->fetchAll();
?>

<div class="titulo-secao">
    <h2>Usuários</h2>
    <a class="btn-principal" style="padding:10px 24px;font-size:.88rem"
       href="<?= url('admin/usuario_form.php') ?>">+ Novo usuário</a>
</div>

<div class="filtros">
    <a class="chip <?= $tipo === '' ? 'ativo' : '' ?>" href="<?= url('admin/usuarios.php') ?>">Todos</a>
    <a class="chip <?= $tipo === 'cliente' ? 'ativo' : '' ?>" href="<?= url('admin/usuarios.php?tipo=cliente') ?>">Clientes</a>
    <a class="chip <?= $tipo === 'admin' ? 'ativo' : '' ?>" href="<?= url('admin/usuarios.php?tipo=admin') ?>">Administradores</a>

    <form class="busca" method="get" action="<?= url('admin/usuarios.php') ?>">
        <?php if ($tipo): ?><input type="hidden" name="tipo" value="<?= e($tipo) ?>"><?php endif; ?>
        <input type="text" name="busca" placeholder="Buscar nome, e-mail ou celular..." value="<?= e($busca) ?>">
        <button class="btn-principal" style="padding:9px 20px;font-size:.85rem" type="submit">Buscar</button>
    </form>
</div>

<?php if (!$usuarios): ?>
    <div class="alerta alerta-info">Nenhum usuário encontrado.</div>
<?php else: ?>
    <table class="tabela">
        <thead>
            <tr><th>ID</th><th>Nome</th><th>E-mail</th><th>Celular</th>
                <th>Endereço</th><th>Tipo</th><th>Criado em</th><th>Ações</th></tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><?= (int)$u['id'] ?></td>
                    <td><?= e($u['nome']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><?= e(formatar_celular($u['celular'] ?? '')) ?></td>
                    <td style="max-width:220px;font-size:.85rem;color:var(--texto-suave)">
                        <?= e($u['endereco'] ?: '—') ?>
                    </td>
                    <td>
                        <?php if ($u['tipo'] === 'admin'): ?>
                            <span class="chip ativo" style="padding:3px 12px">Admin</span>
                        <?php else: ?>
                            <span class="chip" style="padding:3px 12px">Cliente</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:.85rem"><?= date('d/m/Y', strtotime($u['criado_em'])) ?></td>
                    <td style="white-space:nowrap">
                        <a class="btn-secundario" style="padding:6px 16px;font-size:.8rem"
                           href="<?= url('admin/usuario_form.php?id=' . $u['id']) ?>">Editar</a>
                        <?php if ((int)$u['id'] !== (int)$_SESSION['usuario_id']): ?>
                            <a class="btn-perigo" style="margin-left:6px"
                               data-confirmar="Excluir o usuário '<?= e($u['nome']) ?>'? Os pedidos dele também serão removidos."
                               href="<?= url('admin/usuario_excluir.php?id=' . $u['id']) ?>">Excluir</a>
                        <?php else: ?>
                            <span style="margin-left:6px;font-size:.78rem;color:var(--texto-suave)">(você)</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/_footer.php'; ?>