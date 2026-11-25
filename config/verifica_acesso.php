<?php
//Verrifica se o php foi iniciado em algum local
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


//Verrifica se o usuartio esta logado
function exigirLogin()
{
    if (!isset($_SESSION["usuario_id"])) {
        header("Location: sign_in.php?erro=login_necessario");
    exit;
    }
}


function exigirAdmin($conexao)
{
    exigirLogin();

    //Armazena o id do usario logado na variavel id
    $id = (int) $_SESSION["usuario_id"];
    
    //Faz um select na tabela de acordo com id para verrificar se é um admin (o ? é para impedir sql injection)
    $stmt = $conexao->prepare("SELECT TIPO FROM USUARIOS WHERE id = ? LIMIT 1");
    
    //Substitui a ? pelo id usa o "i" pelo tipo de dado
    $stmt->bind_param("i", $id);

    //Executa a verrificação
    $stmt->execute();

    //Armazena o resultado bruto da consulta no banco
    $resultado = $stmt->get_result();

    //Tranforma o dado bruto em uma informação legivel
    $usuario = $resultado->fetch_assoc();

    //Fecha a consulta com servidor
    $stmt->close();

    //Verrifica se o usuario existe ou é admin
    if (!$usuario || $usuario["TIPO"] !== "admin") {
        //Substitui o tipo de usuario logado no site se ele não for admin por cliente
        $_SESSION["usuario_tipo"] = $usuario["TIPO"] ?? "cliente";
        header("Location: index.php?erro=acesso_negado");
        exit;
        }

    
}


?>