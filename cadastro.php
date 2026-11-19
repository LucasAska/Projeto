<?php
require_once __DIR__ . "/config/conexao.php";
require_once __DIR__ . "/config/verifica_acesso.php";

$tipoUsuario = strtolower(trim($_SESSION['usuario_tipo'] ?? ''));
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro | Milka Mics</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>


<header>
    <nav>
        <a class="logo" href="index.php">
            <img src="assets/images/logo-milka.png" alt="Milka Mics">
        </a>
    </nav>
</header>

<main>
    <div class="container">

        <div class="cabecalho">
            <h1>Crie sua conta</h1>
            <p>Preencha seus dados para fazer parte da Milka Mics.</p>
        </div>

        <section class="card">

            <form action="cadastro_salvar.php" method="POST">

                <h2 class="secao-titulo">Dados pessoais</h2>

                <div class="grade">

                    <div class="campo completo">
                        <label for="nome">Nome completo</label>
                        <input type="text" id="nome" name="nome"
                               maxlength="100"
                               placeholder="Digite seu nome completo" required>
                    </div>

                    <div class="campo">
                        <label for="telefone">Telefone</label>
                        <input type="tel" id="telefone" name="telefone"
                               maxlength="20"
                               placeholder="(11) 99999-9999" required>
                    </div>

                    <div class="campo">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email"
                               maxlength="150"
                               placeholder="seuemail@exemplo.com" required>
                    </div>

                    <div class="campo completo">
                        <label for="senha">Senha</label>
                        <input type="password" id="senha" name="senha"
                               maxlength="255"
                               placeholder="Crie uma senha" required>
                    </div>

                </div>

                <h2 class="secao-titulo">Endereço</h2>

                <div class="grade">

                    <div class="campo">
                        <label for="cep">CEP</label>
                        <input type="text" id="cep" name="cep"
                               maxlength="9"
                               placeholder="00000-000" required>
                    </div>

                    <div class="campo">
                        <label for="uf">Estado (UF)</label>
                        <input type="text" id="uf" name="uf"
                               maxlength="2"
                               placeholder="Ex.: SP" required>
                    </div>

                    <div class="campo completo">
                        <label for="logradouro">Rua ou avenida</label>
                        <input type="text" id="logradouro" name="logradouro"
                               maxlength="255"
                               placeholder="Nome da rua ou avenida" required>
                    </div>

                    <div class="campo">
                        <label for="numero">Número</label>
                        <input type="text" id="numero" name="numero"
                               maxlength="20"
                               placeholder="Número do imóvel" required>
                    </div>

                    <div class="campo">
                        <label for="complemento">Complemento</label>
                        <input type="text" id="complemento" name="complemento"
                               maxlength="100"
                               placeholder="Apartamento, bloco etc.">
                    </div>

                    <div class="campo">
                        <label for="bairro">Bairro</label>
                        <input type="text" id="bairro" name="bairro"
                               maxlength="100"
                               placeholder="Seu bairro" required>
                    </div>

                    <div class="campo">
                        <label for="cidade">Cidade</label>
                        <input type="text" id="cidade" name="cidade"
                               maxlength="100"
                               placeholder="Sua cidade" required>
                    </div>

                </div>

                <div class="campo-tipo">
                    <?php if ($tipoUsuario === 'admin'): ?>
                        <label for="tipo">Tipo de conta</label>
                        <select name="tipo">
                            <option value="cliente" selected>Cliente</option>
                            <option value="admin">Administrador</option>
                        </select>
                    <?php endif; ?>
                </div>
                    
                <div class="acoes">
                    <a href="index.php" class="voltar">Voltar</a>
                    <button type="submit">Criar minha conta</button>
                </div>

            </form>

        </section>

    </div>
</main>

<footer>
    &copy; 2026 Milka Mics. Todos os direitos reservados.
</footer>

</body>
</html>
