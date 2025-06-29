<?php
include('conexao.php');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['id_usuario'])) {
    echo 'erro_dados_invalidos';
    exit;
}

$id_usuario = intval($data['id_usuario']);
$nome = $data['nome'] ?? '';
$username = $data['username'] ?? '';
$email = $data['email'] ?? '';
$estado = $data['estado'] ?? '';
$br_data = $data['data_nasc'] ?? '';
$data_nasc = DateTime::createFromFormat('d/m/Y', $br_data);
$data_nasc = $data_nasc ? $data_nasc->format('Y-m-d') : null;
$nivel = $data['nivel_acesso'] ?? ''; // varchar(1), não int!

// Verifica se o usuário existe
$sqlPessoa = "SELECT id_pessoa FROM aully_usuario WHERE id_usuario = ?";
$stmt = $conn->prepare($sqlPessoa);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $id_pessoa = $result->fetch_assoc()['id_pessoa'];


    //são preparados e executados dois comandos de atualização. O primeiro atualiza os campos username, email_usuario, data_nasc e nivel_acesso na tabela aully_usuario, vinculando os parâmetros de forma segura com prepared statements para prevenir SQL Injection. O segundo comando atualiza o nome da pessoa e o estado na tabela aully_pessoa, utilizando o id_pessoa obtido previamente para garantir que o registro correto seja alterado.

    // Atualiza dados do usuário
    $sqlUpdateUsuario = "UPDATE aully_usuario SET username = ?, email_usuario = ?, data_nasc = ?, nivel_acesso = ? WHERE id_usuario = ?";
    $stmtUsuario = $conn->prepare($sqlUpdateUsuario);
    $stmtUsuario->bind_param("sssii", $username, $email, $data_nasc, $nivel, $id_usuario);
    $usuarioOk = $stmtUsuario->execute();

    // Atualiza dados da pessoa
    $sqlUpdatePessoa = "UPDATE aully_pessoa SET nome_pessoa = ?, estado = ? WHERE id_pessoa = ?";
    $stmtPessoa = $conn->prepare($sqlUpdatePessoa);
    $stmtPessoa->bind_param("ssi", $nome, $estado, $id_pessoa);
    $pessoaOk = $stmtPessoa->execute();

    if ($usuarioOk && $pessoaOk) {
        echo 'ok';
    } else {
        echo 'erro_update';
    }
} else {
    echo 'erro_usuario_nao_encontrado';
}
?>