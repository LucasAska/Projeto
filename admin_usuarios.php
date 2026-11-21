<?php
require_once __DIR__ . '/config/conexao.php';
require_once __DIR__ . '/config/auth.php';

exigir_admin();

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$meuId = (int) $_SESSION['usuario_id'];

// Ações da lista (sempre por POST, com token)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        flash('erro', 'Requisição inválida. Tente novamente.');
        header('Location: admin_usuarios.php');
        exit;
    }

    $id   = (int) ($_POST['id'] ?? 0);
    $acao = $_POST['acao'] ?? '';

    if ($id <= 0) {
        flash('erro', 'Usuário inválido.');
    } elseif ($id === $meuId) {
        flash('erro', 'Você não pode alterar ou excluir a sua própria conta por aqui.');
    } elseif ($acao === 'tipo') {
        $tipo = $_POST['tipo'] ?? '';

        if (!in_array($tipo, ['cliente', 'admin'], true)) {
            flash('erro', 'Tipo de conta inválido.');
        } else {
            $stmt = $conexao->prepare("UPDATE USUARIOS SET TIPO = ? WHERE id = ?");
            $stmt->bind_param('si', $tipo, $id);
            $ok = $stmt->execute();
            $stmt->close();

            if ($ok) {
                flash('sucesso', 'Tipo de conta atualizado.');
            } else {
                flash('erro', 'Erro ao atualizar o tipo da conta.');
            }
        }
    } elseif ($acao === 'excluir') {
        try {
            $stmt = $conexao->prepare("DELETE FROM USUARIOS WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $apagou = $stmt->affected_rows > 0;
            $stmt->close();

            if ($apagou) {
                flash('sucesso', 'Usuário excluído com sucesso.');
            } else {
                flash('erro', 'Usuário não encontrado.');
            }
        } catch (mysqli_sql_exception $ex) {
            if ($ex->getCode() === 1451) {
                flash('erro', 'Este usuário já tem pedidos e não pode ser excluído.');
            } else {
                flash('erro', 'Erro ao excluir o usuário.');
            }
        }
    }

    header('Location: admin_usuarios.php');
    exit;
}

$resultado = $conexao->query(
    "SELECT ID, NOME, EMAIL, TELEFONE, CIDADE, UF, TIPO
     FROM USUARIOS
     ORDER BY ID DESC"
);

$titulo        = 'Usuários';
$secaoAtual    = 'usuarios';
$larguraClasse = 'container-admin';

require __DIR__ . '/admin/_topo.php';
?>

<div class="cabecalho">
    <h1>Usuários cadastrados</h1>
    <p>Consulte as contas, altere o tipo de acesso ou remova usuários.</p>
</div>

<div class="card">
    <div class="topo-tabela">
        <h2 class="secao-titulo titulo-sem-borda">Lista de contas</h2>
        <a href="cadastro.php" class="btn-primario">+ Novo usuário</a>
    </div>

    <?php exibir_flash(); ?>

    <div class="tabela-responsiva">
        <table class="tabela-admin">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome / E-mail</th>
                    <th>Telefone</th>
                    <th>Cidade/UF</th>
                    <th>Tipo</th>
                    <th>Ações</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($resultado->num_rows === 0): ?>
                    <tr>
                        <td colspan="6">Nenhum usuário cadastrado.</td>
                    </tr>
                <?php endif; ?>

                <?php while ($u = $resultado->fetch_assoc()): ?>
                    <?php $ehMinhaConta = (int) $u['ID'] === $meuId; ?>

                    <tr>
                        <td><?= (int) $u['ID'] ?></td>

                        <td>
                            <strong><?= e($u['NOME']) ?></strong><br>
                            <span class="texto-suave">
                                <?= e($u['EMAIL']) ?>
                            </span>
                        </td>

                        <td><?= e($u['TELEFONE']) ?></td>

                        <td>
                            <?= e($u['CIDADE']) ?>/<?= e($u['UF']) ?>
                        </td>

                        <td>
                            <?php if ($u['TIPO'] === 'admin'): ?>
                                <span class="tag-tipo tag-ativo">Admin</span>
                            <?php else: ?>
                                <span class="tag-tipo tag-inativo">Cliente</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php if ($ehMinhaConta): ?>

                                <span class="texto-suave">Sua conta</span>

                            <?php else: ?>

                                <div class="acoes-linha">

                                    <a href="editar_usuario.php?id=<?= (int) $u['ID'] ?>"
                                       class="btn-tabela secundario">
                                        Editar
                                    </a>

                                    <form method="post">
                                        <input type="hidden" name="csrf"
                                               value="<?= e($_SESSION['csrf']) ?>">

                                        <input type="hidden" name="id"
                                               value="<?= (int) $u['ID'] ?>">

                                        <input type="hidden" name="acao"
                                               value="tipo">

                                        <?php if ($u['TIPO'] === 'admin'): ?>

                                            <input type="hidden" name="tipo"
                                                   value="cliente">

                                            <button type="submit" class="btn-tabela">
                                                Tornar cliente
                                            </button>

                                        <?php else: ?>

                                            <input type="hidden" name="tipo"
                                                   value="admin">

                                            <button type="submit" class="btn-tabela">
                                                Tornar admin
                                            </button>

                                        <?php endif; ?>
                                    </form>

                                    <!-- Formulário de exclusão -->
                                    <form method="post"
                                          class="form-excluir"
                                          data-nome="<?= e($u['NOME']) ?>">

                                        <input type="hidden" name="csrf"
                                               value="<?= e($_SESSION['csrf']) ?>">

                                        <input type="hidden" name="id"
                                               value="<?= (int) $u['ID'] ?>">

                                        <input type="hidden" name="acao"
                                               value="excluir">

                                        <button type="submit"
                                                class="btn-tabela btn-perigo">
                                            Excluir
                                        </button>
                                    </form>

                                </div>

                            <?php endif; ?>
                        </td>
                    </tr>

                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Janela personalizada de confirmação -->
<div id="modalExcluir" class="modal-excluir" aria-hidden="true">

    <div class="modal-conteudo"
         role="dialog"
         aria-modal="true"
         aria-labelledby="tituloModalExcluir">

        <div class="modal-icone">!</div>

        <h2 id="tituloModalExcluir">Excluir usuário?</h2>

        <p>
            Tem certeza que deseja excluir
            <strong id="nomeUsuarioExcluir"></strong>?
        </p>

        <p class="modal-aviso">
            Essa ação não poderá ser desfeita.
        </p>

        <div class="modal-botoes">

            <button type="button"
                    id="cancelarExcluir"
                    class="btn-cancelar">
                Cancelar
            </button>

            <button type="button"
                    id="confirmarExcluir"
                    class="btn-confirmar">
                Sim, excluir
            </button>

        </div>
    </div>
</div>

<style>
.modal-excluir {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: rgba(0, 0, 0, .55);
}

.modal-excluir.ativo {
    display: flex;
}

.modal-conteudo {
    width: 100%;
    max-width: 420px;
    padding: 30px;
    border-radius: 14px;
    background: #fff;
    text-align: center;
    box-shadow: 0 10px 35px rgba(0, 0, 0, .2);
}

.modal-icone {
    display: flex;
    width: 55px;
    height: 55px;
    margin: 0 auto 15px;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #fff0f0;
    color: #c62828;
    font-size: 32px;
    font-weight: bold;
}

.modal-conteudo h2 {
    margin: 0 0 12px;
    color: #333;
}

.modal-conteudo p {
    color: #555;
    line-height: 1.5;
}

.modal-aviso {
    color: #c62828 !important;
    font-size: 13px;
}

.modal-botoes {
    display: flex;
    gap: 12px;
    margin-top: 25px;
}

.modal-botoes button {
    flex: 1;
    padding: 12px;
    border: 0;
    border-radius: 7px;
    cursor: pointer;
    font-weight: bold;
}

.btn-cancelar {
    background: #eee;
    color: #333;
}

.btn-confirmar {
    background: #c62828;
    color: #fff;
}

.btn-confirmar:hover {
    background: #a91f1f;
}

.btn-cancelar:hover {
    background: #ddd;
}

@media (max-width: 480px) {
    .modal-conteudo {
        padding: 24px 18px;
    }

    .modal-botoes {
        flex-direction: column-reverse;
    }
}
</style>

<script>
const modalExcluir = document.getElementById('modalExcluir');
const nomeUsuarioExcluir = document.getElementById('nomeUsuarioExcluir');
const cancelarExcluir = document.getElementById('cancelarExcluir');
const confirmarExcluir = document.getElementById('confirmarExcluir');

let formularioExcluir = null;

// Abre a confirmação personalizada
document.querySelectorAll('.form-excluir').forEach(function(formulario) {

    formulario.addEventListener('submit', function(evento) {

        evento.preventDefault();

        formularioExcluir = this;

        nomeUsuarioExcluir.textContent = this.dataset.nome;

        modalExcluir.classList.add('ativo');
        modalExcluir.setAttribute('aria-hidden', 'false');

        cancelarExcluir.focus();
    });

});

// Fecha a janela
function fecharModalExcluir() {

    modalExcluir.classList.remove('ativo');
    modalExcluir.setAttribute('aria-hidden', 'true');

    formularioExcluir = null;
}

// Botão cancelar
cancelarExcluir.addEventListener('click', fecharModalExcluir);

// Confirma a exclusão
confirmarExcluir.addEventListener('click', function() {

    if (formularioExcluir) {
        HTMLFormElement.prototype.submit.call(formularioExcluir);
    }

});

// Fecha ao clicar fora
modalExcluir.addEventListener('click', function(evento) {

    if (evento.target === modalExcluir) {
        fecharModalExcluir();
    }

});

// Fecha com ESC
document.addEventListener('keydown', function(evento) {

    if (evento.key === 'Escape' && modalExcluir.classList.contains('ativo')) {
        fecharModalExcluir();
    }

});
</script>

<?php require __DIR__ . '/admin/_rodape.php'; ?>
