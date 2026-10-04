<?php
// Oculta os erros feios do PHP para mostrarmos apenas os nossos avisos personalizados
ini_set('display_errors', 0);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

include 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recebe os dados
    $nome        = $_POST['nome'];
    $telefone    = $_POST['telefone'];
    $email       = $_POST['email'];
    $senha       = $_POST['senha']; 
    $cep         = $_POST['cep'];
    $logradouro  = $_POST['logradouro'];
    $numero      = $_POST['numero'];
    $complemento = $_POST['complemento'];
    $bairro      = $_POST['bairro'];
    $cidade      = $_POST['cidade'];
    $uf          = $_POST['uf'];
    $tipo        = $_POST['tipo'];

    // Monta o SQL
    $sql = "INSERT INTO USUARIOS (
                NOME, TELEFONE, EMAIL, SENHA, CEP, LOGRADOURO, 
                NUMERO, COMPLEMENTO, BAIRRO, CIDADE, UF, TIPO
            ) VALUES (
                '$nome', '$telefone', '$email', '$senha', '$cep', '$logradouro', 
                '$numero', '$complemento', '$bairro', '$cidade', '$uf', '$tipo'
            )";

    // Estilo base para a página de resposta ficar centrada e bonita
    echo "<div style='text-align:center; margin-top:100px; font-family:Arial, sans-serif;'>";

    try {
        // Tenta executar a gravação
        $conexao->query($sql);
        
        // Se chegar aqui, deu tudo certo!
        echo "<h2 style='color: #28a745;'>✅ Utilizador registado com sucesso!</h2>";
        echo "<p>O e-mail <strong>$email</strong> foi adicionado ao sistema.</p><br>";
        echo "<a href='index.php' style='padding:10px 20px; background:#007BFF; color:white; text-decoration:none; border-radius:5px;'>Voltar ao Início</a>";

    } catch (mysqli_sql_exception $e) {
        
        // Se falhar, o código cai diretamente aqui e analisamos o motivo
        if ($e->getCode() == 1062) { 
            // O código 1062 no MySQL significa especificamente "Entrada Duplicada"
            echo "<h2 style='color: #dc3545;'>⚠️ Atenção: Este e-mail já está registado!</h2>";
            echo "<p>O endereço <strong>$email</strong> já existe na nossa base de dados.</p>";
            echo "<p>Por favor, utiliza um e-mail diferente.</p><br>";
        } else {
            // Qualquer outro erro não previsto
            echo "<h2 style='color: #dc3545;'>❌ Ocorreu um problema ao registar:</h2>";
            echo "<p>Erro técnico: " . $e->getMessage() . "</p><br>";
        }

        // Botão para voltar atrás (usa o histórico para não apagar os campos que o utilizador já preencheu)
        echo "<a href='javascript:history.back()' style='padding:10px 20px; background:#ffc107; color:#333; text-decoration:none; border-radius:5px; font-weight:bold; border: 1px solid #d39e00;'>⬅️ Voltar e Alterar E-mail</a>";
    }

    echo "</div>";

} else {
    echo "Acesso inválido.";
}
?>