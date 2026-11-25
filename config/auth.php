<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bloqueia quem não for admin (a verificação é feita no servidor)
function exigir_admin(): void
{
    if (empty($_SESSION['usuario_id'])) {
        header('Location: /sign_in.php');
        exit;
    }

    if (($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
        header('Location: /index.php');
        exit;
    }
}

// Guarda uma mensagem para ser exibida na próxima página
function flash(string $tipo, string $mensagem): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensagem' => $mensagem];
}

// Mostra a mensagem uma única vez e depois apaga
function exibir_flash(): void
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $cor = $f['tipo'] === 'erro' ? '#f8d7da' : '#d4edda';
        echo '<div style="padding:10px;margin:10px 0;background:' . $cor . '">'
           . htmlspecialchars($f['mensagem'], ENT_QUOTES, 'UTF-8') . '</div>';
    }
}

// Atalho para escapar texto na tela
function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}