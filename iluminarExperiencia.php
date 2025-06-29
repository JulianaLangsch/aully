<?php
session_start();
include_once("conexao.php");

if (!isset($_SESSION['id_usuario']))
    exit;

$id_usuario = $_SESSION['id_usuario'];
$id_experiencia = $_POST['id_experiencia'] ?? 0;

// identificador da experiência (id_experiencia) é obtido via POST, com valor padrão igual a zero caso não esteja presente. O próximo passo é consultar o banco de dados para verificar se o usuário já iluminou aquela experiência. Isso é feito com um SELECT 1 na tabela aully_iluminacoes, que armazena os registros de iluminações por usuário e por experiência.

// Verifica se já iluminou
$stmt = $conn->prepare("SELECT 1 FROM aully_iluminacoes WHERE id_usuario = ? AND id_experiencia = ?");
$stmt->bind_param("ii", $id_usuario, $id_experiencia);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Já iluminou, remove
    $stmt->close();
    $stmt = $conn->prepare("DELETE FROM aully_iluminacoes WHERE id_usuario = ? AND id_experiencia = ?");
    $stmt->bind_param("ii", $id_usuario, $id_experiencia);
    $stmt->execute();
    $action = 'removed';
} else {
    // Não iluminou, adiciona
    $stmt->close();
    $stmt = $conn->prepare("INSERT INTO aully_iluminacoes (id_usuario, id_experiencia) VALUES (?, ?)");
    $stmt->bind_param("ii", $id_usuario, $id_experiencia);
    $stmt->execute();
    $action = 'added';
}
$stmt->close();

//consulta para contar quantas iluminações aquela experiência possui no total. Utilizando um SELECT COUNT(*), ele retorna a quantidade atualizada de iluminações
// Conta total
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM aully_iluminacoes WHERE id_experiencia = ?");
$stmt->bind_param("i", $id_experiencia);
$stmt->execute();
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();

//a resposta é enviada no formato JSON com duas informações principais: total, indicando o número total de iluminações da experiência, e action, informando se a ação foi 'added' (quando o usuário iluminou) ou 'removed' (quando desiluminou).
echo json_encode(['total' => $total, 'action' => $action]);
exit;
?>