<?php
session_start();
include_once("conexao.php");

// Verifica se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

//Quando o formulário é enviado via método POST, os dados da experiência — título, tipo e descrição — são capturados e tratados com trim() para remover espaços em branco. O ID do usuário é obtido da sessão.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $tipo = trim($_POST['tipo']);
    $descricao = trim($_POST['descricao']);
    $id_usuario = $_SESSION['id_usuario'];

    //O código então entra no processo de tratamento e validação de mídias. Ele define o diretório de destino (uploads/) e garante sua existência com mkdir(). É definido um limite de até 4 mídias por postagem e um tamanho máximo de 10MB por arquivo.
    $midias = [];
    $permitidos = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'avi', 'mov'];
    $pasta = "uploads/";
    if (!is_dir($pasta)) {
        mkdir($pasta, 0755, true);
    }
    $max_arquivos = 4;
    $count = 0;

    //Caso o envio de arquivos tenha ocorrido, cada mídia é validada individualmente: o código verifica o erro, o tamanho, a extensão permitida e também o MIME type (formato real do arquivo, como image/png ou video/mp4). Se todas as condições forem atendidas, o arquivo é renomeado com uniqid(), salvo na pasta e adicionado ao array $midias.
    if (isset($_FILES['midia']) && isset($_FILES['midia']['name']) && is_array($_FILES['midia']['name'])) {
        foreach ($_FILES['midia']['name'] as $i => $name) {
            if ($count >= $max_arquivos)
                break;
            if ($_FILES['midia']['error'][$i] === UPLOAD_ERR_OK) {
                if ($_FILES['midia']['size'][$i] > 10 * 1024 * 1024) {
                    continue; // ignora arquivos grandes
                }
                $extensao = pathinfo($name, PATHINFO_EXTENSION);
                $permitidos = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'avi', 'mov'];
                $mime_permitidos = [
                    'image/jpeg',
                    'image/png',
                    'image/gif',
                    'video/mp4',
                    'video/avi',
                    'video/quicktime'
                ];
                $tmp_name = $_FILES['midia']['tmp_name'][$i];
                $mime = mime_content_type($tmp_name);

                if (
                    in_array(strtolower($extensao), $permitidos) &&
                    in_array($mime, $mime_permitidos)
                ) {
                    $novoNome = uniqid() . '.' . $extensao;
                    $destino = $pasta . $novoNome;
                    if (move_uploaded_file($tmp_name, $destino)) {
                        $midias[] = $destino;
                        $count++;
                    }
                }
            }
        }
    }

    // Salva os caminhos das mídias como JSON no campo 'midia'
    $midiaPath = !empty($midias) ? json_encode($midias) : null;

    $stmt = $conn->prepare("INSERT INTO aully_experiencia (titulo, tipo, descricao, midia, id_usuario, curtidas, votos, porcentagem) VALUES (?, ?, ?, ?, ?, 0, 0, 0)");
    $stmt->bind_param("ssssi", $titulo, $tipo, $descricao, $midiaPath, $id_usuario);
    $stmt->execute();
    $stmt->close();

    // Atualizar ou inserir em aully_experienciastotal
    $stmt_total = $conn->prepare("SELECT quantidade FROM aully_experienciastotal WHERE tipo = ? AND id_usuario = ?");
    $stmt_total->bind_param("si", $tipo, $id_usuario);
    $stmt_total->execute();
    $result_total = $stmt_total->get_result();

    //atualiza o total de experiências postadas por tipo
    if ($result_total->num_rows > 0) {
        $stmt_update = $conn->prepare("UPDATE aully_experienciastotal SET quantidade = quantidade + 1 WHERE tipo = ? AND id_usuario = ?");
        $stmt_update->bind_param("si", $tipo, $id_usuario);
        $stmt_update->execute();
        $stmt_update->close();
    } else {
        $stmt_insert = $conn->prepare("INSERT INTO aully_experienciastotal (quantidade, tipo, id_usuario) VALUES (1, ?, ?)");
        $stmt_insert->bind_param("si", $tipo, $id_usuario);
        $stmt_insert->execute();
        $stmt_insert->close();
    }
    $stmt_total->close();

    // Atualizar experienciasPostadas
    error_log("ID do usuário: " . $id_usuario);

    //atualiza o campo experienciasPostadas na tabela aully_usuario, incrementando em 1 o total de postagens feitas por aquele usuário.
    $stmt_user = $conn->prepare("UPDATE aully_usuario SET experienciasPostadas = experienciasPostadas + 1 WHERE id_usuario = ?");
    $stmt_user->bind_param("i", $id_usuario);
    if (!$stmt_user->execute()) {
        die("Erro ao atualizar experienciasPostadas: " . $stmt_user->error);
    }
    $stmt_user->close();

    header("Location: paginaPrincipal.php?sucesso=1");
    exit();
}
?>

<style>
    .alert-sucesso {
        background: #8ac926;
        color: #fff;
        padding: 10px;
        border-radius: 8px;
        margin: 10px auto;
        text-align: center;
        width: 90%;
    }
</style>