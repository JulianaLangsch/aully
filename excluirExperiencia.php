<?php
session_start();
include_once("conexao.php");

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

//captura o identificador da experiência a ser excluída por meio do parâmetro id enviado via GET. Caso esse ID não seja fornecido, o usuário é redirecionado para a página principal, evitando a execução sem dados válidos.
$id_experiencia = $_GET['id'] ?? null;

if (!$id_experiencia) {
    header("Location: paginaPrincipal.php");
    exit();
}

// Descobre o tipo da experiência antes de excluir, filtrando pelo ID da experiência e pelo ID do usuário logado, reforçando que o usuário só pode excluir suas próprias experiências.
$stmt_tipo = $conn->prepare("SELECT tipo FROM aully_experiencia WHERE id_experiencia=? AND id_usuario=?");
$stmt_tipo->bind_param("ii", $id_experiencia, $_SESSION['id_usuario']);
$stmt_tipo->execute();
$result_tipo = $stmt_tipo->get_result();
$tipo = null;
if ($row = $result_tipo->fetch_assoc()) {
    $tipo = $row['tipo'];
}
$stmt_tipo->close();

if ($tipo) {
    //atualiza a tabela aully_experienciastotal
    // Diminui 1 do total do tipo
    $stmt_update = $conn->prepare("UPDATE aully_experienciastotal SET quantidade = quantidade - 1 WHERE tipo = ? AND id_usuario = ?");
    $stmt_update->bind_param("si", $tipo, $_SESSION['id_usuario']);
    $stmt_update->execute();
    $stmt_update->close();

    //verificação para remover o registro na tabela aully_experienciastotal caso a quantidade chegue a zero ou fique negativa, mantendo a tabela limpa e consistente.
    // Remove o registro se a quantidade chegar a zero 
    $stmt_del = $conn->prepare("DELETE FROM aully_experienciastotal WHERE quantidade <= 0 AND tipo = ? AND id_usuario = ?");
    $stmt_del->bind_param("si", $tipo, $_SESSION['id_usuario']);
    $stmt_del->execute();
    $stmt_del->close();
}

//exclusão efetiva da experiência na tabela aully_experiencia, usando uma query preparada que assegura que apenas a experiência correspondente ao ID informado e pertencente ao usuário será excluída, evitando exclusões indevidas.
// Exclui a experiência
$stmt = $conn->prepare("DELETE FROM aully_experiencia WHERE id_experiencia=? AND id_usuario=?");
$stmt->bind_param("ii", $id_experiencia, $_SESSION['id_usuario']);
$stmt->execute();
$stmt->close();

header("Location: paginaPrincipal.php");
exit();
?>