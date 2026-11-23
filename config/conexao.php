<?php
$servidor = "db";
$utilizador = "root"; //no xamp sempre vai ser root
$senha = "root"; 
$banco = "ProjetoDados"; //colocar o nome do banco de dados

// conexão
$conexao = new mysqli($servidor, $utilizador, $senha, $banco);

//verifica conexão
if ($conexao->connect_error) {
    die("Falha na ligação: " . $conexao->connect_error);
}

$conexao->set_charset("utf8mb4");

$conexao->query("SET time_zone = '-03:00'");

?>