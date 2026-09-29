<?php
require_once __DIR__ . '/../config/config.php';
exigir_admin();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    set_flash('erro', 'Usuário inválido.');
    redirecionar('admin/usuarios.php');
}

if ($id === (int)$_SESSION['usuario_id']) {
    set_flash('erro', 'Você não pode excluir sua própria conta.');
    redirecionar('admin/usuarios.php');
}

$st = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
$st->execute([$id]);
$usuario = $st->fetch();

if (!$usuario) {
    set_flash('erro', 'Usuário não encontrado.');
    redirecionar('admin/usuarios.php');
}

if ($usuario['tipo'] === 'admin' && total_admins() <= 1) {
    set_flash('erro', 'Não é possível excluir o único administrador do sistema.');
    redirecionar('admin/usuarios.php');
}

try {
    $pdo->prepare('DELETE FROM usuarios WHERE id = ?')->execute([$id]);
    set_flash('sucesso', 'Usuário excluído com sucesso.');
} catch (PDOException $ex) {
    set_flash('erro', 'Não foi possível excluir: ' . $ex->getMessage());
}

redirecionar('admin/usuarios.php');