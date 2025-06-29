<?php
session_start();
include_once("conexao.php");

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

//recebe via método POST o id_experiencia e o voto, que pode ser 'sim' ou 'não'. O ID da experiência é convertido para inteiro para garantir segurança no banco de dados.
$id_usuario = $_SESSION['id_usuario'];
$id_experiencia = intval($_POST['id_experiencia']);
$voto = $_POST['voto']; // 'sim' ou 'não'

//Para evitar votos duplicados, é feita uma consulta que verifica se o usuário já votou naquela experiência. Caso o usuário não tenha votado ainda, o voto é inserido na tabela aully_votos.
// Evita votos duplicados
$check = $conn->prepare("SELECT * FROM aully_votos WHERE id_usuario = ? AND id_experiencia = ?");
$check->bind_param("ii", $id_usuario, $id_experiencia);
$check->execute();
$res = $check->get_result();

if ($res->num_rows === 0) {
    $stmt = $conn->prepare("INSERT INTO aully_votos (id_usuario, id_experiencia, voto) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $id_usuario, $id_experiencia, $voto);
    $stmt->execute();

    // Atualiza porcentagem
    $sql_total = $conn->prepare("SELECT COUNT(*) as total FROM aully_votos WHERE id_experiencia = ?");
    $sql_total->bind_param("i", $id_experiencia);
    $sql_total->execute();
    $total = $sql_total->get_result()->fetch_assoc()['total'];

    $sql_sim = $conn->prepare("SELECT COUNT(*) as total FROM aully_votos WHERE id_experiencia = ? AND voto = 'sim'");
    $sql_sim->bind_param("i", $id_experiencia);
    $sql_sim->execute();
    $sim = $sql_sim->get_result()->fetch_assoc()['total'];

    $porcentagem = ($sim / $total) * 100;

    //atualiza os campos votos (total de votos) e porcentagem (percentual de votos 'sim') na tabela aully_experiencia
    $update = $conn->prepare("UPDATE aully_experiencia SET votos = ?, porcentagem = ? WHERE id_experiencia = ?");
    $update->bind_param("idi", $total, $porcentagem, $id_experiencia);
    $update->execute();
}

header("Cache-Control: no-cache, must-revalidate");
header("Location: paginaPrincipal.php");
exit();

?>