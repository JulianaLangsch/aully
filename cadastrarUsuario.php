<?php
include('conexao.php');

//realiza uma consulta à tabela aully_pessoa, buscando os nomes e IDs de todas as pessoas cadastradas, para exibir na lista de seleção do formulário.
// Buscar pessoas para preencher o select
$pessoas = $conn->query("SELECT id_pessoa, nome_pessoa FROM aully_pessoa");

$msg = '';
$msg_tipo = '';

//coleta dados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email_usuario'] ?? '';
    $senha = $_POST['senha_usuario'] ?? '';
    $nivel = $_POST['nivel_acesso'] ?? '';
    $br_data = $_POST['data_nasc'] ?? '';
    $id_pessoa = $_POST['id_pessoa'] ?? 0;

    // Buscar nome da pessoa vinculada
    $nome = '';
    if ($id_pessoa > 0) {
        $stmt_nome = $conn->prepare("SELECT nome_pessoa FROM aully_pessoa WHERE id_pessoa = ?");
        $stmt_nome->bind_param("i", $id_pessoa);
        $stmt_nome->execute();
        $stmt_nome->bind_result($nome_pessoa);
        if ($stmt_nome->fetch()) {
            $nome = $nome_pessoa;
        }
        $stmt_nome->close();
    }

    // Converter data de nascimento para formato americano
    $data_nasc = DateTime::createFromFormat('d/m/Y', $br_data);
    if ($data_nasc) {
        $data_nasc = $data_nasc->format('Y-m-d');
    } else {
        $data_nasc = null;
    }

    if (!empty($email) && !empty($username) && !empty($senha) && !empty($nivel) && !empty($data_nasc) && $id_pessoa > 0) {
        // Criptografa a senha antes de salvar
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        //insere o novo usuário na tabela aully_usuario, preenchendo os campos com os dados informados e iniciando os valores padrão como: usuario_ativo = 1, experienciasPostadas = 0 e aceitou_termos = 0
        $sql = "INSERT INTO aully_usuario (email_usuario, senha_usuario, nivel_acesso, usuario_ativo, id_pessoa, data_nasc, experienciasPostadas, username, aceitou_termos, nome) 
        VALUES (?, ?, ?, 1, ?, ?, 0, ?, 0, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssisss", $email, $senha_hash, $nivel, $id_pessoa, $data_nasc, $username, $nome);
        if ($stmt->execute()) {
            $msg = "Usuário cadastrado com sucesso! Redirecionando...";
            $msg_tipo = "sucesso";
            echo "<meta http-equiv='refresh' content='2;url=administrador.php'>";
        } else {
            $msg = "Erro ao cadastrar usuário.";
            $msg_tipo = "erro";
        }
        $stmt->close();
    } else {
        $msg = "Preencha todos os campos corretamente.";
        $msg_tipo = "erro";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Cadastrar Usuário</title>
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

        .cadastro-usuario-container {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 2px 16px 0 rgba(106, 76, 147, 0.08);
            padding: 32px 28px 24px 28px;
            margin-top: 60px;
            margin-bottom: 60px;
            width: 90vw;
            max-width: 400px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .cadastro-usuario-container h2 {
            font-family: var(--fonte-principal);
            color: #e36414;
            margin-bottom: 18px;
            font-size: 2em;
            letter-spacing: 1px;
        }

        .cadastro-usuario-container label {
            font-family: var(--fonte-secundaria);
            color: #6a4c93;
            font-size: 1.1em;
            margin-bottom: 4px;
            margin-top: 10px;
            display: block;
            text-align: left;
            width: 100%;
        }

        .cadastro-usuario-container input[type="text"],
        .cadastro-usuario-container input[type="email"],
        .cadastro-usuario-container input[type="password"],
        .cadastro-usuario-container select {
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

        .cadastro-usuario-container input[type="text"]:focus,
        .cadastro-usuario-container input[type="email"]:focus,
        .cadastro-usuario-container input[type="password"]:focus,
        .cadastro-usuario-container select:focus {
            border: 1.5px solid #e36414;
            outline: none;
            background: #fff;
        }

        .cadastro-usuario-container button[type="submit"] {
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

        .cadastro-usuario-container button[type="submit"]:hover {
            background: #c67800;
        }

        @media (max-width: 500px) {
            .cadastro-usuario-container {
                padding: 18px 8px 14px 8px;
                margin-top: 30px;
            }

            .cadastro-usuario-container h2 {
                font-size: 1.3em;
            }
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
            box-shadow: 0 2px 8px rgba(106, 76, 147, 0.08);
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
            box-shadow: 0 2px 8px rgba(106, 76, 147, 0.08);
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
    <div class="cadastro-usuario-container">
        <h2>Cadastrar Usuário</h2>
        <form method="POST">
            <label>Nome de Usuário:</label><br>
            <input type="text" name="username" required><br><br>

            <label>Email:</label><br>
            <input type="email" name="email_usuario" required><br><br>

            <label>Senha:</label><br>
            <input type="password" name="senha_usuario" required><br><br>

            <label>Data de Nascimento:</label><br>
            <input type="text" name="data_nasc" id="data_nasc" placeholder="dd/mm/aaaa" required><br><br>

            <label>Nível de Acesso:</label><br>
            <select name="nivel_acesso" required>
                <option value="1">Administrador</option>
                <option value="2">Gerente</option>
                <option value="3">Usuário</option>
            </select><br><br>

            <label>Vincular à Pessoa:</label><br>
            <select name="id_pessoa" required>
                <option value="">Selecione</option>
                <?php while ($pessoa = $pessoas->fetch_assoc()): ?>
                    <option value="<?= $pessoa['id_pessoa'] ?>"><?= htmlspecialchars($pessoa['nome_pessoa']) ?></option>
                <?php endwhile; ?>
            </select><br><br>

            <button type="submit">Cadastrar</button>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('data_nasc');
            input.addEventListener('input', function () {
                let value = input.value.replace(/\D/g, '').slice(0, 8);
                if (value.length >= 5) {
                    input.value = value.replace(/(\d{2})(\d{2})(\d{1,4})/, '$1/$2/$3');
                } else if (value.length >= 3) {
                    input.value = value.replace(/(\d{2})(\d{1,2})/, '$1/$2');
                } else {
                    input.value = value;
                }
            });
        });
    </script>
</body>

</html>