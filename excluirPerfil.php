<?php
session_start();
include_once("conexao.php");

//consulta SQL para atualizar a tabela aully_usuario, definindo o campo usuario_ativo como 0, o que representa que o usuário está inativo ou desativado no sistema. 
$id_usuario = $_SESSION['id_usuario'];
$stmt = $conn->prepare("UPDATE aully_usuario SET usuario_ativo = 0 WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
session_destroy();
header("Location: index.php");
exit();
?>