<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Usuário</title>
</head>
<body>
    <h2>Cadastro de Novo Usuário</h2>
    <form action="backend.php" method="POST">
        <label>Nome Completo:</label><br>
        <input type="text" name="nome" maxlength="100" required><br><br>

        <label>Telefone:</label><br>
        <input type="text" name="telefone" maxlength="20" required><br><br>

        <label>E-mail:</label><br>
        <input type="email" name="email" maxlength="150" required><br><br>

        <label>Senha:</label><br>
        <input type="password" name="senha" maxlength="255" required><br><br>

        <label>CEP:</label><br>
        <input type="text" name="cep" maxlength="9" required><br><br>

        <label>Logradouro (Rua, Av.):</label><br>
        <input type="text" name="logradouro" maxlength="255" required><br><br>

        <label>Número:</label><br>
        <input type="text" name="numero" maxlength="20" required><br><br>

        <label>Complemento:</label><br>
        <input type="text" name="complemento" maxlength="100"><br><br>

        <label>Bairro:</label><br>
        <input type="text" name="bairro" maxlength="100" required><br><br>

        <label>Cidade:</label><br>
        <input type="text" name="cidade" maxlength="100" required><br><br>

        <label>UF:</label><br>
        <input type="text" name="uf" maxlength="2" required><br><br>

        <label>Tipo de Conta:</label><br>
        <select name="tipo">
            <option value="cliente">Cliente</option>
            <option value="admin">Administrador</option>
        </select><br><br>

        <button type="submit">Cadastrar Usuário</button>
    </form>
</body>
</html>