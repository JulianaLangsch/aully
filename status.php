<?php
include('conexao.php');

$id = intval($_GET['id']);
if (!$id) {
    echo 'erro_id_invalido';
    exit;
}

// Consulta status atual
$sql = "SELECT usuario_ativo FROM aully_usuario WHERE id_usuario = $id";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $ativo = $row['usuario_ativo'];

    // Inverte o status: se 1 → 0, se 0 → 1
    $novoStatus = $ativo == 1 ? 0 : 1;

    $sqlUpdate = "UPDATE aully_usuario SET usuario_ativo = $novoStatus WHERE id_usuario = $id";
    if (mysqli_query($conn, $sqlUpdate)) {
        echo $novoStatus; // retorna 1 ou 0
    } else {
        echo 'erro_ao_alterar';
    }
} else {
    echo 'usuario_nao_encontrado';
}
?>