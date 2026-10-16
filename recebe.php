<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: sing_in.php");
    exit;
}

$email = trim($_POST["email"] ?? "");
$senhaDigitada = $_POST["senha"] ?? "";

if ($email === "" || $senhaDigitada === "") {
    header("Location: sing_in.php?erro=campos");
    exit;
}

require_once __DIR__ . "/config/conexao.php";

$sql = "SELECT id, NOME, EMAIL, SENHA, TIPO
        FROM USUARIOS
        WHERE EMAIL = ?
        LIMIT 1";

$stmt = $conexao->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();

$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

$stmt->close();

if (!$usuario) {
    header("Location: sing_in.php?erro=credenciais");
    exit;
}

$senhaBanco = (string) $usuario["SENHA"];

// Aceita senhas com hash e, temporariamente,
// senhas antigas que estejam salvas em texto simples.
$senhaValida = password_verify($senhaDigitada, $senhaBanco);

if (!$senhaValida && hash_equals($senhaBanco, $senhaDigitada)) {
    $senhaValida = true;

    // Atualiza a senha antiga para um hash seguro.
    $novoHash = password_hash($senhaDigitada, PASSWORD_DEFAULT);

    $atualizar = $conexao->prepare(
        "UPDATE USUARIOS SET SENHA = ? WHERE id = ?"
    );

    $atualizar->bind_param(
        "si",
        $novoHash,
        $usuario["id"]
    );

    $atualizar->execute();
    $atualizar->close();
}

if (!$senhaValida) {
    header("Location: sing_in.php?erro=credenciais");
    exit;
}

session_regenerate_id(true);

$_SESSION["usuario_id"] = $usuario["id"];
$_SESSION["usuario_nome"] = $usuario["NOME"];
$_SESSION["usuario_email"] = $usuario["EMAIL"];
$_SESSION["usuario_tipo"] = $usuario["TIPO"];

header("Location: index.php");
exit;