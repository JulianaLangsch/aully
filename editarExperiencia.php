<?php
session_start();
include_once("conexao.php");

//define o cabeçalho da resposta como texto simples com codificação UTF-8, pois é um script que normalmente será acessado via requisição AJAX.
header('Content-Type: text/plain; charset=utf-8');

// Verifica se usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401); // Não autorizado
    echo "Usuário não autenticado.";
    exit();
}

// Aceita id_experiencia via POST (modal)
$id_experiencia = $_POST['id_experiencia'] ?? $_GET['id'] ?? null;
$id_experiencia = intval($id_experiencia);

if (!$id_experiencia) {
    http_response_code(400); // Requisição inválida
    echo "ID inválido";
    exit();
}

//captura os dados enviados pelo usuário: título, tipo e descrição da experiência, além do ID do usuário da sessão. Antes de executar a atualização, realiza validações básicas para garantir que nenhum desses campos esteja vazio. Se algum estiver em branco, retorna um erro 422 (entidade não processável) com uma mensagem clara para o usuário.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $tipo = trim($_POST['tipo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $id_usuario = $_SESSION['id_usuario'];

    // Validações básicas
    if (empty($titulo) || empty($tipo) || empty($descricao)) {
        http_response_code(422); // Entidade não processável
        echo "Todos os campos são obrigatórios.";
        exit();
    }

    //A atualização propriamente dita é feita por meio de uma query preparada (prepared statement) que atualiza os campos título, tipo e descrição da tabela aully_experiencia, mas somente para o registro cujo id_experiencia e id_usuario correspondam aos valores informados. Essa dupla condição garante que o usuário só consiga editar suas próprias experiências, reforçando a segurança da aplicação.
    
    // Prepara e executa atualização
    $stmt = $conn->prepare("UPDATE aully_experiencia SET titulo = ?, tipo = ?, descricao = ? WHERE id_experiencia = ? AND id_usuario = ?");
    $stmt->bind_param("sssii", $titulo, $tipo, $descricao, $id_experiencia, $id_usuario);

    if ($stmt->execute()) {
        echo "ok";
    } else {
        http_response_code(500);
        echo "Erro ao atualizar experiência.";
    }

    $stmt->close();
    exit();
}

// Caso não seja POST
http_response_code(405); // Método não permitido
echo "Método não permitido.";
exit();
?>