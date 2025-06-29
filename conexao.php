<?php
// conexao pela internet
//  $servidor = "localhost";
//$usuario = "mascarenhas_alunos";
//$senha = "nsuB2z6vQmPsfeHBdV7U";
// $dbname = "mascarenhas_alunos";    
//conexao local
$servidor = "localhost";
$usuario = "root";
$senha = "";
$dbname = "aully";

//Criar a conexao
$conn = mysqli_connect($servidor, $usuario, $senha, $dbname);


if (!$conn) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}
?>