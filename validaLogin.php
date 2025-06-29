<?php
session_start();
include_once("conexao.php");

if (!isset($_POST['username'], $_POST['senha'])) {
    $_SESSION['loginErro'] = "Preencha todos os campos.";
    header("Location: login.php");
    exit();
}

$username = trim($_POST['username']);
$senha = $_POST['senha'];

// Consulta pelo username
$sql = "SELECT * FROM aully_usuario WHERE username = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $_SESSION['loginErro'] = "Nome de usuário ou senha inválido.";
    header("Location: login.php");
    exit();
}

$usuario = $result->fetch_assoc();

// Verifica se usuário está ativo
if ((int) $usuario['usuario_ativo'] !== 1) {
    $_SESSION['loginErro'] = "Usuário inativo.";
    header("Location: login.php");
    exit();
}

// Verifica senha
if (!password_verify($senha, $usuario['senha_usuario'])) {
    $_SESSION['loginErro'] = "Nome de usuário ou senha inválido.";
    header("Location: login.php");
    exit();
}

// Login válido: define sessão
$_SESSION['id_usuario'] = $usuario['id_usuario'];
$_SESSION['nivel_acesso'] = $usuario['nivel_acesso'];
$_SESSION['username'] = $usuario['username'];

// Registra login no log_acesso
$stmtLog = $conn->prepare("INSERT INTO aully_log_acesso (id_usuario) VALUES (?)");
$stmtLog->bind_param("i", $usuario['id_usuario']);
$stmtLog->execute();

if ($stmtLog->affected_rows > 0) {
    $_SESSION['id_log'] = $conn->insert_id;
}
$stmtLog->close();

// Redirecionamento
if ((int) $usuario['aceitou_termos'] === 0) {
    header("Location: boasVindas.php");
    exit();
}

switch ((int) $_SESSION['nivel_acesso']) {
    case 1:
        header("Location: administrador.php");
        break;
    case 2:
        header("Location: gerente.php");
        break;
    default:
        header("Location: paginaPrincipal.php");
        break;
}
exit();
?>