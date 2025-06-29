<?php
session_start();
include_once("conexao.php");

//comentarExperiencia.php é responsável por processar os comentários feitos pelos usuários em uma experiência, tanto comentários principais quanto respostas a outros comentários.

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

//o script coleta os dados enviados via POST, como o ID da experiência (id_experiencia), o conteúdo do comentário (comentario) e, opcionalmente, o ID do comentário pai (id_resposta) no caso de ser uma resposta. O id_experiencia e o id_resposta, se presente, são convertidos para inteiros como forma de segurança.

$id_usuario = $_SESSION['id_usuario'];
$id_experiencia = intval($_POST['id_experiencia']);
$comentario = trim($_POST['comentario']);
$id_resposta = isset($_POST['id_resposta']) && $_POST['id_resposta'] !== '' ? intval($_POST['id_resposta']) : null;

//Se o comentário for do tipo principal (ou seja, não for uma resposta a outro comentário), o script prepara uma instrução SQL para inserir na tabela aully_comentarios, informando o id_experiencia, id_usuario, o texto do comentário e id_resposta como NULL. Já se for uma resposta, o campo id_resposta é preenchido com o ID do comentário pai.
if ($id_resposta === null) {
    $stmt = $conn->prepare("INSERT INTO aully_comentarios (id_experiencia, id_usuario, comentario, id_resposta) VALUES (?, ?, ?, NULL)");
    $stmt->bind_param("iis", $id_experiencia, $id_usuario, $comentario);
} else {
    $stmt = $conn->prepare("INSERT INTO aully_comentarios (id_experiencia, id_usuario, comentario, id_resposta) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iisi", $id_experiencia, $id_usuario, $comentario, $id_resposta);
}
//os dados são vinculados e enviados ao banco com execute(). 
$stmt->execute();
$stmt->close();

exit;
?>