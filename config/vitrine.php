<?php
// Consulta da vitrine: somente produtos ativos e com estoque,
// filtrados pela busca e divididos em páginas.

function vitrine_buscar_produtos(mysqli $conexao, string $termo = '', int $pagina = 1, int $porPagina = 12): array
{
    $termo     = trim(mb_substr($termo, 0, 100));
    $pagina    = max(1, $pagina);
    $porPagina = min(max(1, $porPagina), 50);
    $offset    = ($pagina - 1) * $porPagina;

    $where  = "p.ATIVO = 1 AND p.ESTOQUE > 0";
    $params = [];
    $tipos  = '';

    if ($termo !== '') {
        // Escapa % e _ para que a busca trate o texto como texto literal
        $like   = '%' . addcslashes($termo, '%_\\') . '%';
        $where .= " AND (p.NOME LIKE ? OR p.DESCRICAO LIKE ? OR c.NOME LIKE ?)";
        $params = [$like, $like, $like];
        $tipos  = 'sss';
    }

    // Total de resultados, para calcular a quantidade de páginas
    $stmt = $conexao->prepare(
        "SELECT COUNT(*) AS total
         FROM PRODUTOS p
         LEFT JOIN CATEGORIAS c ON c.ID = p.CATEGORIA_ID
         WHERE $where"
    );
    if ($tipos !== '') {
        $stmt->bind_param($tipos, ...$params);
    }
    $stmt->execute();
    $total = (int) $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    // Itens da página atual (LIMIT e OFFSET são inteiros já validados acima)
    $stmt = $conexao->prepare(
        "SELECT p.ID, p.NOME, p.DESCRICAO, p.PRECO, p.ESTOQUE, p.IMAGEM, c.NOME AS CATEGORIA
         FROM PRODUTOS p
         LEFT JOIN CATEGORIAS c ON c.ID = p.CATEGORIA_ID
         WHERE $where
         ORDER BY p.NOME ASC
         LIMIT $porPagina OFFSET $offset"
    );
    if ($tipos !== '') {
        $stmt->bind_param($tipos, ...$params);
    }
    $stmt->execute();
    $resultado = $stmt->get_result();

    $itens = [];
    while ($linha = $resultado->fetch_assoc()) {
        $itens[] = vitrine_formatar_produto($linha);
    }
    $stmt->close();

    return [
        'busca'         => $termo,
        'pagina'        => $pagina,
        'por_pagina'    => $porPagina,
        'total'         => $total,
        'total_paginas' => max(1, (int) ceil($total / $porPagina)),
        'itens'         => $itens,
    ];
}

// Monta o produto no formato que o front recebe.
// Verifica se o arquivo da foto existe, para o front mostrar "Sem foto" quando faltar.
function vitrine_formatar_produto(array $linha): array
{
    $nomeArquivo = !empty($linha['IMAGEM']) ? basename($linha['IMAGEM']) : '';
    $temFoto     = $nomeArquivo !== '' && is_file(__DIR__ . '/../assets/images/produtos/' . $nomeArquivo);
    $preco       = (float) $linha['PRECO'];

    return [
        'id'              => (int) $linha['ID'],
        'nome'            => $linha['NOME'],
        'descricao'       => $linha['DESCRICAO'] ?? '',
        'categoria'       => $linha['CATEGORIA'] ?? '',
        'preco'           => $preco,
        'preco_formatado' => 'R$ ' . number_format($preco, 2, ',', '.'),
        'estoque'         => (int) $linha['ESTOQUE'],
        'tem_foto'        => $temFoto,
        'foto_url'        => $temFoto ? '/assets/images/produtos/' . $nomeArquivo : null,
    ];
}