<?php
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../config/auth.php';

exigir_admin();

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$statusValidos = ['pendente', 'atendido', 'cancelado'];

// Filtro por status (opcional): ?status=pendente, atendido ou cancelado
$filtro = $_GET['status'] ?? '';
if (!in_array($filtro, $statusValidos, true)) {
    $filtro = '';
}

// Troca de status (POST), voltando para o mesmo filtro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $destino = 'listar.php';
    $filtroPost = $_POST['filtro'] ?? '';
    if (in_array($filtroPost, $statusValidos, true)) {
        $destino .= '?status=' . $filtroPost;
    }

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        flash('erro', 'Requisição inválida. Tente novamente.');
    } else {
        $id     = (int) ($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if ($id <= 0 || !in_array($status, $statusValidos, true)) {
            flash('erro', 'Dados inválidos para atualizar o pedido.');
        } else {
            try {
                $conexao->begin_transaction();

                // Trava o pedido para evitar duas trocas ao mesmo tempo
                $stmt = $conexao->prepare("SELECT STATUS FROM PEDIDOS WHERE ID = ? FOR UPDATE");
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $pedidoAtual = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$pedidoAtual) {
                    throw new RuntimeException('Pedido não encontrado.');
                }

                $eraCancelado  = $pedidoAtual['STATUS'] === 'cancelado';
                $ficaCancelado = $status === 'cancelado';

                // Cancelar devolve o estoque. Reativar um pedido cancelado baixa o estoque de novo.
                if ($eraCancelado !== $ficaCancelado) {
                    $stmtItens = $conexao->prepare("SELECT PRODUTO_ID, QUANTIDADE FROM PEDIDOS_ITENS WHERE PEDIDO_ID = ?");
                    $stmtItens->bind_param('i', $id);
                    $stmtItens->execute();
                    $itens = $stmtItens->get_result()->fetch_all(MYSQLI_ASSOC);
                    $stmtItens->close();

                    if ($ficaCancelado) {
                        $stmtEstoque = $conexao->prepare("UPDATE PRODUTOS SET ESTOQUE = ESTOQUE + ? WHERE ID = ?");
                    } else {
                        // Só baixa se ainda houver estoque suficiente
                        $stmtEstoque = $conexao->prepare("UPDATE PRODUTOS SET ESTOQUE = ESTOQUE - ? WHERE ID = ? AND ESTOQUE >= ?");
                    }

                    foreach ($itens as $item) {
                        $qtd       = (int) $item['QUANTIDADE'];
                        $produtoId = (int) $item['PRODUTO_ID'];

                        if ($ficaCancelado) {
                            $stmtEstoque->bind_param('ii', $qtd, $produtoId);
                        } else {
                            $stmtEstoque->bind_param('iii', $qtd, $produtoId, $qtd);
                        }
                        $stmtEstoque->execute();

                        if (!$ficaCancelado && $stmtEstoque->affected_rows === 0) {
                            throw new RuntimeException('Não há estoque suficiente para reativar este pedido.');
                        }
                    }
                    $stmtEstoque->close();
                }

                $stmt = $conexao->prepare("UPDATE PEDIDOS SET STATUS = ? WHERE ID = ?");
                $stmt->bind_param('si', $status, $id);
                $stmt->execute();
                $stmt->close();

                $conexao->commit();
                flash('sucesso', 'Pedido #' . $id . ' marcado como ' . $status . '.');
            } catch (Throwable $ex) {
                $conexao->rollback();
                flash('erro', $ex instanceof RuntimeException
                    ? $ex->getMessage()
                    : 'Erro ao atualizar o pedido.');
            }
        }
    }

    header('Location: ' . $destino);
    exit;
}

$sql = "SELECT p.ID, p.STATUS, p.CRIADO_EM, u.NOME AS CLIENTE, u.EMAIL,
               COUNT(i.ID) AS ITENS,
               COALESCE(SUM(i.QUANTIDADE * i.PRECO_UNIT), 0) AS TOTAL
        FROM PEDIDOS p
        INNER JOIN USUARIOS u ON u.id = p.USUARIO_ID
        LEFT JOIN PEDIDOS_ITENS i ON i.PEDIDO_ID = p.ID";

$params = [];
$tipos  = '';

if ($filtro !== '') {
    $sql .= " WHERE p.STATUS = ?";
    $params = [$filtro];
    $tipos  = 's';
}

$sql .= " GROUP BY p.ID, p.STATUS, p.CRIADO_EM, u.NOME, u.EMAIL
          ORDER BY p.ID DESC";

$stmt = $conexao->prepare($sql);
if ($tipos !== '') {
    $stmt->bind_param($tipos, ...$params);
}
$stmt->execute();
$resultado = $stmt->get_result();

$titulo        = 'Pedidos';
$secaoAtual    = 'pedidos';
$larguraClasse = 'container-admin';
require __DIR__ . '/../_topo.php';
?>

<div class="cabecalho">
    <h1>Pedidos realizados</h1>
    <p>Acompanhe os pedidos feitos pelos clientes e atualize o status.</p>
</div>

<div class="card">
    <div class="topo-tabela">
        <h2 class="secao-titulo titulo-sem-borda">Lista de pedidos</h2>
        <div class="acoes-linha">
            <a class="btn-tabela <?= $filtro === '' ? 'btn-primario' : 'secundario' ?>" href="listar.php">Todos</a>
            <a class="btn-tabela <?= $filtro === 'pendente' ? 'btn-primario' : 'secundario' ?>" href="listar.php?status=pendente">Pendentes</a>
            <a class="btn-tabela <?= $filtro === 'atendido' ? 'btn-primario' : 'secundario' ?>" href="listar.php?status=atendido">Atendidos</a>
            <a class="btn-tabela <?= $filtro === 'cancelado' ? 'btn-primario' : 'secundario' ?>" href="listar.php?status=cancelado">Cancelados</a>
        </div>
    </div>

    <?php exibir_flash(); ?>

    <div class="tabela-responsiva">
        <table class="tabela-admin">
            <thead>
                <tr>
                    <th>Pedido</th>
                    <th>Cliente</th>
                    <th>Data</th>
                    <th>Itens</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultado->num_rows === 0): ?>
                    <tr><td colspan="6">Nenhum pedido encontrado.</td></tr>
                <?php endif; ?>

                <?php while ($p = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= (int) $p['ID'] ?></td>
                        <td>
                            <strong><?= e($p['CLIENTE']) ?></strong><br>
                            <span class="texto-suave"><?= e($p['EMAIL']) ?></span>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($p['CRIADO_EM'])) ?></td>
                        <td><?= (int) $p['ITENS'] ?></td>
                        <td>R$ <?= number_format((float) $p['TOTAL'], 2, ',', '.') ?></td>
                        <td>
                            <form method="post" class="acoes-linha">
                                <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                                <input type="hidden" name="id" value="<?= (int) $p['ID'] ?>">
                                <input type="hidden" name="filtro" value="<?= e($filtro) ?>">
                                <select name="status" style="width:auto; height:36px; padding:0 8px;">
                                    <?php foreach ($statusValidos as $s): ?>
                                        <option value="<?= $s ?>" <?= $p['STATUS'] === $s ? 'selected' : '' ?>>
                                            <?= ucfirst($s) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn-tabela">Salvar</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../_rodape.php'; ?>