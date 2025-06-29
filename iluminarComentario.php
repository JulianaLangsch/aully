<?php
include_once("conexao.php");
session_start();

//trata da lógica de "iluminar" e "desiluminar" um comentário, funcionando como um sistema de curtir ou reagir, feito de forma assíncrona via AJAX.

// código recupera o id_usuario da sessão e o id_comentario enviado via POST. Se algum desses dados estiver ausente, a execução é encerrada e uma resposta JSON com erro é retornada.
$id_usuario = $_SESSION['id_usuario'] ?? null;
$id_comentario = $_POST['id_comentario'] ?? null;

if (!$id_usuario || !$id_comentario) {
    echo json_encode(['error' => 'Dados inválidos']);
    exit;
}

//verificação para saber se o usuário já iluminou aquele comentário. Isso é feito com uma consulta SQL na tabela aully_iluminacoes_comentario, utilizando SELECT 1 apenas para verificar a existência do registro. Se a resposta indicar que o usuário já iluminou, o sistema interpreta como uma tentativa de "desiluminar", e o registro correspondente é removido do banco. Caso contrário, considera-se que o usuário quer iluminar, e um novo registro é inserido associando o id_usuario ao id_comentario.

// Verifica se já iluminou
$stmt = $conn->prepare("SELECT 1 FROM aully_iluminacoes_comentario WHERE id_usuario = ? AND id_comentario = ?");
$stmt->bind_param("ii", $id_usuario, $id_comentario);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Já iluminou, então remove
    $stmt->close();
    $stmt = $conn->prepare("DELETE FROM aully_iluminacoes_comentario WHERE id_usuario = ? AND id_comentario = ?");
    $stmt->bind_param("ii", $id_usuario, $id_comentario);
    $stmt->execute();
    $action = 'removed';
} else {
    // Não iluminou, então adiciona
    $stmt->close();
    $stmt = $conn->prepare("INSERT INTO aully_iluminacoes_comentario (id_usuario, id_comentario) VALUES (?, ?)");
    $stmt->bind_param("ii", $id_usuario, $id_comentario);
    $stmt->execute();
    $action = 'added';
}

//o script executa uma nova consulta para contar o número total de iluminações daquele comentário. Esse valor é necessário para atualizar dinamicamente a interface do usuário com a nova contagem após a ação.

// Conta total
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM aully_iluminacoes_comentario WHERE id_comentario = ?");
$stmt->bind_param("i", $id_comentario);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

//a resposta é enviada em formato JSON contendo duas chaves: action, que indica se a ação foi 'added' ou 'removed', e total, que informa o total de iluminações atualizadas daquele comentário. Esse retorno é interpretado em JavaScript, permitindo que o botão seja atualizado visualmente (ativado ou desativado) e que a contagem de iluminações seja ajustada em tempo real sem recarregar a página.
echo json_encode(['action' => $action, 'total' => $total]);
?>