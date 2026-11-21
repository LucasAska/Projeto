<?php
// Endpoint público da vitrine.
// Exemplo: /api/produtos.php?busca=croche&pagina=1&por_pagina=12
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../config/vitrine.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'erro' => 'Método não permitido.']);
    exit;
}

$busca     = trim((string) ($_GET['busca'] ?? ''));
$pagina    = (int) ($_GET['pagina'] ?? 1);
$porPagina = (int) ($_GET['por_pagina'] ?? 12);

try {
    $dados = vitrine_buscar_produtos($conexao, $busca, $pagina, $porPagina);
    echo json_encode(
        ['ok' => true] + $dados,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
} catch (Throwable $ex) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'erro' => 'Não foi possível carregar os produtos.']);
}