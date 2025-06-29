<?php
include('conexao.php');

$msg = '';
$msg_tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome_pessoa'] ?? '';
    $estado = $_POST['estado'] ?? '';

    //verifica se a requisição foi feita com o método POST, o que indica que o formulário de cadastro foi enviado. Se os campos “nome” e “estado” estiverem preenchidos corretamente, o código prepara uma consulta SQL utilizando prepare() e bind_param() para evitar ataques de injeção de SQL. Essa consulta insere os dados da nova pessoa na tabela aully_pessoa, salvando o nome e o estado escolhido. Se o cadastro for bem-sucedido, o sistema exibe uma mensagem de sucesso e redireciona o usuário automaticamente para a página cadastrarUsuario.php após 2 segundos. Caso ocorra algum erro, uma mensagem de erro é exibida.
    if (!empty($nome) && !empty($estado)) {
        $sql = "INSERT INTO aully_pessoa (nome_pessoa, estado) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $nome, $estado);
    if ($stmt->execute()) {
        $msg = "Pessoa cadastrada com sucesso! Redirecionando...";
        $msg_tipo = "sucesso";
        echo "<meta http-equiv='refresh' content='2;url=cadastrarUsuario.php'>";
    } else {
        $msg = "Erro ao cadastrar.";
        $msg_tipo = "erro";
    }
}  
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Pessoa</title>
    <link rel="icon" type="image/png" href="./imgStyle/favicon.png">
    <style>
@import url('https://fonts.googleapis.com/css2?family=Calistoga&family=Spicy+Rice&display=swap');

:root {
    --fonte-principal: "Spicy Rice", serif;
    --fonte-secundaria: "Calistoga", serif;
}

body {
    background: linear-gradient(to top, #e69212, white);
    min-height: 100vh;
    margin: 0;
    font-family: var(--fonte-secundaria), Arial, sans-serif;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.cadastro-pessoa-container {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 2px 16px 0 rgba(106,76,147,0.08);
    padding: 32px 28px 24px 28px;
    margin-top: 60px;
    width: 90vw;
    max-width: 400px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.cadastro-pessoa-container h2 {
    font-family: var(--fonte-principal);
    color: #e36414;
    margin-bottom: 18px;
    font-size: 2em;
    letter-spacing: 1px;
}

.cadastro-pessoa-container label {
    font-family: var(--fonte-secundaria);
    color: #6a4c93;
    font-size: 1.1em;
    margin-bottom: 4px;
    margin-top: 10px;
    display: block;
    text-align: left;
    width: 100%;
}

.cadastro-pessoa-container input[type="text"] {
    width: 100%;
    border: 1px solid #ddd;
    border-radius: 12px;
    padding: 10px;
    font-size: 1.1em;
    margin-bottom: 16px;
    margin-top: 2px;
    box-sizing: border-box;
    background: #f9f9f9;
    transition: border 0.2s;
}

.cadastro-pessoa-container input[type="text"]:focus {
    border: 1.5px solid #e36414;
    outline: none;
    background: #fff;
}

.cadastro-pessoa-container button[type="submit"] {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 14px;
    background: #e36414;
    font-size: 1.15em;
    cursor: pointer;
    color: white;
    margin-top: 10px;
    font-family: var(--fonte-secundaria);
    font-weight: bold;
    letter-spacing: 1px;
    transition: background 0.2s;
}

.cadastro-pessoa-container button[type="submit"]:hover {
    background: #c67800;
}

@media (max-width: 500px) {
    .cadastro-pessoa-container {
        padding: 18px 8px 14px 8px;
        margin-top: 30px;
    }
    .cadastro-pessoa-container h2 {
        font-size: 1.3em;
    }
}

.cadastro-pessoa-container select {
    width: 100%;
    border: 1px solid #ddd;
    border-radius: 12px;
    padding: 10px;
    font-size: 1.1em;
    margin-bottom: 16px;
    margin-top: 2px;
    box-sizing: border-box;
    background: #f9f9f9;
    transition: border 0.2s;
}

.cadastro-pessoa-container select:focus {
    border: 1.5px solid #e36414;
    outline: none;
    background: #fff;
}

.mensagem-sucesso {
    background: #d4edda;
    color: #155724;
    border: 1.5px solid #c3e6cb;
    padding: 14px 24px;
    border-radius: 12px;
    margin: 24px auto 0 auto;
    max-width: 400px;
    text-align: center;
    font-size: 1.1em;
    font-family: var(--fonte-secundaria, Arial, sans-serif);
    box-shadow: 0 2px 8px rgba(106,76,147,0.08);
}
.mensagem-erro {
    background: #f8d7da;
    color: #721c24;
    border: 1.5px solid #f5c6cb;
    padding: 14px 24px;
    border-radius: 12px;
    margin: 24px auto 0 auto;
    max-width: 400px;
    text-align: center;
    font-size: 1.1em;
    font-family: var(--fonte-secundaria, Arial, sans-serif);
    box-shadow: 0 2px 8px rgba(106,76,147,0.08);
}

 .btn-voltar-admin {
        position: absolute;
        top: 24px;
        left: 32px;
        background: #e36414;
        color: #fff;
        padding: 8px 22px;
        border-radius: 16px;
        text-decoration: none;
        font-family: var(--fonte-secundaria, Arial, sans-serif);
        font-size: 1em;
        margin-bottom: 0;
        margin-top: 0;
        transition: background 0.2s;
        z-index: 10;
    }
    .btn-voltar-admin:hover {
        background: #c67800;
        color: #fff;
    }
    
</style>

</head>
<body>
     <?php if (!empty($msg)): ?>
        <div class="mensagem-<?= $msg_tipo ?>">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

        <a href="administrador.php" class="btn-voltar-admin">← Voltar</a>


    <div class="cadastro-pessoa-container">
        <h2>Cadastrar Pessoa</h2>
        <form method="POST">
            <label>Nome:</label>
            <input type="text" name="nome_pessoa" required>

            <label>Estado:</label>
            <select name="estado" required>
                <option value="" disabled selected>Selecione seu estado</option>
                <option value="AC">Acre (AC)</option>
                <option value="AL">Alagoas (AL)</option>
                <option value="AP">Amapá (AP)</option>
                <option value="AM">Amazonas (AM)</option>
                <option value="BA">Bahia (BA)</option>
                <option value="CE">Ceará (CE)</option>
                <option value="DF">Distrito Federal (DF)</option>
                <option value="ES">Espírito Santo (ES)</option>
                <option value="GO">Goiás (GO)</option>
                <option value="MA">Maranhão (MA)</option>
                <option value="MT">Mato Grosso (MT)</option>
                <option value="MS">Mato Grosso do Sul (MS)</option>
                <option value="MG">Minas Gerais (MG)</option>
                <option value="PA">Pará (PA)</option>
                <option value="PB">Paraíba (PB)</option>
                <option value="PR">Paraná (PR)</option>
                <option value="PE">Pernambuco (PE)</option>
                <option value="PI">Piauí (PI)</option>
                <option value="RJ">Rio de Janeiro (RJ)</option>
                <option value="RN">Rio Grande do Norte (RN)</option>
                <option value="RS">Rio Grande do Sul (RS)</option>
                <option value="RO">Rondônia (RO)</option>
                <option value="RR">Roraima (RR)</option>
                <option value="SC">Santa Catarina (SC)</option>
                <option value="SP">São Paulo (SP)</option>
                <option value="SE">Sergipe (SE)</option>
                <option value="TO">Tocantins (TO)</option>
            </select>

            <button type="submit">Cadastrar</button>
        </form>
    </div>
</body>
</html>