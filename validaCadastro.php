<?php
session_start();
include('conexao.php');

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recebe e limpa os dados
    $nome = trim($_POST['nome']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $estado = trim($_POST['state']);
    $data_nasc = trim($_POST['data_nasc']);
    $senha = $_POST['password'];
    $confirmarSenha = $_POST['confirm_password'];

    // Validação de campos obrigatórios
    if (empty($nome) || empty($username) || empty($email) || empty($estado) || empty($data_nasc) || empty($senha) || empty($confirmarSenha)) {
        $_SESSION['msg'] = "Preencha todos os campos!";
        header("Location: cadastro.php");
        exit();
    }

    // Verifica se as senhas coincidem
    if ($senha !== $confirmarSenha) {
        $_SESSION['msg'] = "As senhas não coincidem!";
        header("Location: cadastro.php");
        exit();
    }

    // Verifica se o e-mail já existe
    $verificaEmail = $conn->prepare("SELECT id_usuario FROM aully_usuario WHERE email_usuario = ?");
    $verificaEmail->bind_param("s", $email);
    $verificaEmail->execute();
    $verificaEmail->store_result();

    if ($verificaEmail->num_rows > 0) {
        $_SESSION['msg'] = "Este e-mail já está cadastrado! Tente outro ou faça login.";
        $verificaEmail->close();
        header("Location: cadastro.php");
        exit();
    }
    $verificaEmail->close();

    $verificaUsername = $conn->prepare("SELECT id_usuario FROM aully_usuario WHERE username = ?");
    $verificaUsername->bind_param("s", $username);
    $verificaUsername->execute();
    $verificaUsername->store_result();

    if ($verificaUsername->num_rows > 0) {
        $_SESSION['msg'] = "Este nome de usuário já está em uso! Tente outra variação.";
        $verificaUsername->close();
        header("Location: cadastro.php");
        exit();
    }
    $verificaUsername->close();

    // Criptografa a senha
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    // Processa upload da foto
    $foto = $_FILES['foto'];
    $caminho_foto = null;

    if ($foto['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $permitidas)) {
            $_SESSION['msg'] = "Formato de imagem inválido. Use jpg, png, gif ou webp.";
            header("Location: cadastro.php");
            exit();
        }

        $pasta_upload = 'uploads/';
        if (!is_dir($pasta_upload)) {
            mkdir($pasta_upload, 0755, true);
        }

        $novo_nome = uniqid() . '.' . $ext;
        $caminho_foto = $pasta_upload . $novo_nome;

        if (!move_uploaded_file($foto['tmp_name'], $caminho_foto)) {
            $_SESSION['msg'] = "Erro ao salvar a foto de perfil.";
            header("Location: cadastro.php");
            exit();
        }
    } else {
        $_SESSION['msg'] = "Erro no upload da foto.";
        header("Location: cadastro.php");
        exit();
    }

    // Inserir na tabela aully_pessoa
    $stmtPessoa = $conn->prepare("INSERT INTO aully_pessoa (nome_pessoa, estado) VALUES (?, ?)");
    $stmtPessoa->bind_param("ss", $nome, $estado);
    if (!$stmtPessoa->execute()) {
        die("Erro ao cadastrar pessoa: " . $stmtPessoa->error);
    }
    $idPessoa = $conn->insert_id;
    $stmtPessoa->close();

    // Inserir na tabela aully_usuario
    $stmtUsuario = $conn->prepare("INSERT INTO aully_usuario
        (email_usuario, senha_usuario, nivel_acesso, usuario_ativo, id_pessoa, data_nasc, experienciasPostadas, username, aceitou_termos, foto)
        VALUES (?, ?, '3', 1, ?, ?, 0, ?, 0, ?)");

    $stmtUsuario->bind_param("ssisss", $email, $senhaHash, $idPessoa, $data_nasc, $username, $caminho_foto);

    if ($stmtUsuario->execute()) {
        $_SESSION['msg'] = "Cadastro realizado com sucesso!";
        $stmtUsuario->close();
        $conn->close();
        header("Location: login.php");
        exit();
    } else {
        die("Erro ao cadastrar usuário: " . $stmtUsuario->error);
    }

} else {
    $_SESSION['msg'] = "Acesso inválido!";
    header("Location: cadastro.php");
    exit();
}
?>