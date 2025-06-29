<?php

session_start();
include_once('conexao.php');

//logout.php é responsável por encerrar a sessão do usuário de forma segura e registrar o horário de saída no banco de dados. 

//O código verifica se existe um id_log armazenado na sessão — esse identificador representa o registro da sessão atual na tabela aully_log_acesso, que é usada para monitorar acessos dos usuários. Se esse ID estiver definido, o script prepara uma atualização (UPDATE) nessa tabela para preencher o campo data_logout com a data e hora atuais (NOW()), marcando o momento em que o usuário fez o logout.

if (isset($_SESSION['id_log'])) {
    $stmt = $conn->prepare("UPDATE aully_log_acesso SET data_logout = NOW() WHERE id_log = ?");
    $stmt->bind_param("i", $_SESSION['id_log']);
    $stmt->execute();
    $stmt->close();
}

//Depois que o horário de saída é registrado no banco, o script chama session_unset() para limpar todas as variáveis da sessão e em seguida session_destroy() para finalizar completamente a sessão do usuário.
// Destruir sessão
session_unset();
session_destroy();

//o usuário é redirecionado para a página de login
header("Location: login.php");
exit();

?>