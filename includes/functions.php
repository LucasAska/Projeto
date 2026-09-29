<?php

function e($valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function url(string $caminho = ''): string {
    return BASE_URL . '/' . ltrim($caminho, '/');
}

function moeda($valor): string {
    return 'R$ ' . number_format((float)$valor, 2, ',', '.');
}

function redirecionar(string $caminho): void {
    header('Location: ' . url($caminho));
    exit;
}

function set_flash(string $tipo, string $msg): void {
    $_SESSION['flash'][$tipo] = $msg;
}

function mostrar_flash(): void {
    if (empty($_SESSION['flash'])) return;
    foreach ($_SESSION['flash'] as $tipo => $msg) {
        echo '<div class="alerta alerta-' . e($tipo) . '">' . e($msg) . '</div>';
    }
    unset($_SESSION['flash']);
}

/* ------------------ Usuário ------------------ */

function usuario_logado(): bool {
    return isset($_SESSION['usuario_id']);
}

function usuario_atual(): ?array {
    global $pdo;
    static $cache = false;
    if (!usuario_logado()) return null;
    if ($cache === false) {
        $st = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
        $st->execute([$_SESSION['usuario_id']]);
        $cache = $st->fetch() ?: null;
    }
    return $cache;
}

function is_admin(): bool {
    $u = usuario_atual();
    return $u && $u['tipo'] === 'admin';
}

function exigir_login(): void {
    if (!usuario_logado()) {
        set_flash('erro', 'Você precisa entrar na sua conta para continuar.');
        redirecionar('login.php');
    }
}

function exigir_admin(): void {
    if (!is_admin()) {
        set_flash('erro', 'Acesso restrito à administração.');
        redirecionar('login.php');
    }
}

/* ------------------ Carrinho ------------------ */

function carrinho(): array {
    if (!isset($_SESSION['carrinho']) || !is_array($_SESSION['carrinho'])) {
        $_SESSION['carrinho'] = [];
    }
    return $_SESSION['carrinho'];
}

function carrinho_qtd_total(): int {
    return array_sum(carrinho());
}

function carrinho_limpar(): void {
    $_SESSION['carrinho'] = [];
}

/* ------------------ Produtos ------------------ */

function produto_imagem_url(?string $imagem): ?string {
    if (!$imagem) return null;
    if (!is_file(UPLOAD_DIR . $imagem)) return null;
    return url('uploads/' . $imagem);
}

function placeholder_inicial(string $nome): string {
    return mb_strtoupper(mb_substr(trim($nome), 0, 1, 'UTF-8'), 'UTF-8');
}

/* ------------------ Categorias N:N ------------------ */

function categorias_do_produto(int $produtoId): array {
    global $pdo;
    $st = $pdo->prepare(
        'SELECT c.* FROM categorias c
         JOIN produto_categoria pc ON pc.categoria_id = c.id
         WHERE pc.produto_id = ?
         ORDER BY c.nome'
    );
    $st->execute([$produtoId]);
    return $st->fetchAll();
}

function salvar_categorias_produto(int $produtoId, array $categoriaIds): void {
    global $pdo;
    $categoriaIds = array_values(array_unique(array_map('intval', $categoriaIds)));
    $categoriaIds = array_filter($categoriaIds, fn($id) => $id > 0);

    if (!$categoriaIds) return;

    $pdo->prepare('DELETE FROM produto_categoria WHERE produto_id = ?')->execute([$produtoId]);

    $ins = $pdo->prepare('INSERT INTO produto_categoria (produto_id, categoria_id) VALUES (?, ?)');
    foreach ($categoriaIds as $cid) {
        $ins->execute([$produtoId, $cid]);
    }

    $pdo->prepare('UPDATE produtos SET categoria_id = ? WHERE id = ?')
        ->execute([$categoriaIds[0], $produtoId]);
}

/* ------------------ Usuários ------------------ */

function formatar_celular(?string $tel): string {
    if (!$tel) return '—';
    $d = preg_replace('/\D/', '', $tel);
    if (strlen($d) === 11) {
        return sprintf('(%s) %s-%s',
            substr($d,0,2), substr($d,2,5), substr($d,7,4));
    }
    if (strlen($d) === 10) {
        return sprintf('(%s) %s-%s',
            substr($d,0,2), substr($d,2,4), substr($d,6,4));
    }
    return $tel;
}

function validar_celular(string $tel): bool {
    $d = preg_replace('/\D/', '', $tel);
    return strlen($d) === 10 || strlen($d) === 11;
}

function total_admins(): int {
    global $pdo;
    return (int)$pdo->query('SELECT COUNT(*) FROM usuarios WHERE tipo = "admin"')->fetchColumn();
}