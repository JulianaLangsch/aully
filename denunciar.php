<?php
session_start();
include_once("conexao.php");

//registra denúncia

$id_usuario = $_SESSION['id_usuario'];
$id_experiencia = $_POST['id_experiencia'] ?? NULL;
$id_comentario = $_POST['id_comentario'] ?? NULL;
$motivo = $_POST['motivo'];

//o script prepara uma query INSERT para a tabela aully_denuncias, que armazena as denúncias feitas pelos usuários. Os parâmetros da query são: id_usuario, id_experiencia, id_comentario e motivo. A função bind_param é usada para associar os valores com segurança à consulta preparada, evitando possíveis ataques de SQL Injection. Em seguida, o comando é executado e a instrução é fechada.
$stmt = $conn->prepare("INSERT INTO aully_denuncias (id_usuario, id_experiencia, id_comentario, motivo) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiis", $id_usuario, $id_experiencia, $id_comentario, $motivo);
$stmt->execute();
$stmt->close();

exit;
?>