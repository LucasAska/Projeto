<?php
// Carrinho de compras (guardado na sessão) e proteção CSRF das páginas da loja.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_valido(): bool
{
    $enviado = $_POST['csrf'] ?? '';
    return is_string($enviado) && hash_equals(csrf_token(), $enviado);
}

// Busca um produto no banco. Retorna null se não existir.
function carrinho_produto(mysqli $conexao, int $id): ?array
{
    $stmt = $conexao->prepare("SELECT ID, NOME, PRECO, ESTOQUE, IMAGEM, ATIVO FROM PRODUTOS WHERE ID = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $produto = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $produto ?: null;
}

// Adiciona ao carrinho. Retorna uma mensagem de erro, ou '' se deu certo.
function carrinho_adicionar(mysqli $conexao, int $id, int $qtd): string
{
    if ($qtd < 1) {
        return 'Escolha uma quantidade válida.';
    }

    $produto = carrinho_produto($conexao, $id);
    if (!$produto || (int) $produto['ATIVO'] !== 1) {
        return 'Este produto não está disponível.';
    }

    $novaQtd = ($_SESSION['carrinho'][$id] ?? 0) + $qtd;
    if ($novaQtd > (int) $produto['ESTOQUE']) {
        return 'A quantidade passa do estoque disponível para "' . $produto['NOME'] . '".';
    }

    $_SESSION['carrinho'][$id] = $novaQtd;
    return '';
}

// Muda a quantidade de um item. Quantidade 0 remove o item.
function carrinho_atualizar(mysqli $conexao, int $id, int $qtd): string
{
    if (!isset($_SESSION['carrinho'][$id])) {
        return '';
    }

    if ($qtd <= 0) {
        unset($_SESSION['carrinho'][$id]);
        return '';
    }

    $produto = carrinho_produto($conexao, $id);
    if (!$produto || (int) $produto['ATIVO'] !== 1) {
        unset($_SESSION['carrinho'][$id]);
        return 'Um dos produtos não está mais disponível e foi removido do carrinho.';
    }

    if ($qtd > (int) $produto['ESTOQUE']) {
        return 'A quantidade de "' . $produto['NOME'] . '" passa do estoque disponível.';
    }

    $_SESSION['carrinho'][$id] = $qtd;
    return '';
}

function carrinho_quantidade_total(): int
{
    return array_sum($_SESSION['carrinho'] ?? []);
}

// Monta os itens do carrinho com preço e foto atuais, e calcula o total.
function carrinho_detalhes(mysqli $conexao): array
{
    $linhas = [];
    $total = 0.0;
    $tudoDisponivel = true;

    foreach ($_SESSION['carrinho'] ?? [] as $id => $qtd) {
        $produto = carrinho_produto($conexao, (int) $id);

        $disponivel = $produto
            && (int) $produto['ATIVO'] === 1
            && (int) $produto['ESTOQUE'] >= $qtd;

        $preco    = $produto ? (float) $produto['PRECO'] : 0.0;
        $subtotal = $preco * $qtd;

        if ($disponivel) {
            $total += $subtotal;
        } else {
            $tudoDisponivel = false;
        }

        $arquivo = !empty($produto['IMAGEM']) ? basename($produto['IMAGEM']) : '';
        $foto    = ($arquivo !== '' && is_file(__DIR__ . '/../assets/images/produtos/' . $arquivo))
            ? '/assets/images/produtos/' . $arquivo
            : null;

        $linhas[] = [
            'id'         => (int) $id,
            'nome'       => $produto['NOME'] ?? 'Produto indisponível',
            'preco'      => $preco,
            'foto'       => $foto,
            'estoque'    => $produto ? (int) $produto['ESTOQUE'] : 0,
            'qtd'        => (int) $qtd,
            'subtotal'   => $subtotal,
            'disponivel' => $disponivel,
        ];
    }

    return [
        'linhas'          => $linhas,
        'total'           => $total,
        'tudo_disponivel' => $tudoDisponivel,
    ];
}

// Texto de aviso de estoque baixo, ou '' quando não precisa mostrar nada
function estoque_aviso(int $estoque): string
{
    if ($estoque <= 0) {
        return 'Esgotado';
    }
    if ($estoque === 1) {
        return 'Última unidade!';
    }
    if ($estoque <= 3) {
        return 'Últimas ' . $estoque . ' unidades!';
    }
    return '';
}