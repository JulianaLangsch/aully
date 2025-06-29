<?php
session_start();
include_once("conexao.php");

$id_usuario = $_SESSION['id_usuario'];

// Atualização da foto, se enviada (processa sempre que houver upload)
if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $pasta = "uploads";
    if (!is_dir($pasta)) {
        mkdir($pasta, 0777, true);
    }
    $novo_nome_arquivo = $pasta . "/" . $id_usuario . "_" . uniqid() . "." . $ext;
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $novo_nome_arquivo)) {
        $stmt = $conn->prepare("UPDATE aully_usuario SET foto = ? WHERE id_usuario = ?");
        $stmt->bind_param("si", $novo_nome_arquivo, $id_usuario);
        $stmt->execute();
        $stmt->close();
        header("Location: perfil.php");
        exit();
    }
}

// Atualização de perfil (nome, bio, data_nasc)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_perfil'])) {
    $novo_nome = trim($_POST['nome']);
    $nova_bio = trim($_POST['bio']);
    $data_nasc = $_POST['data_nasc'];

    $stmt = $conn->prepare("UPDATE aully_usuario SET nome = ?, biografia = ?, data_nasc = ? WHERE id_usuario = ?");
    $stmt->bind_param("sssi", $novo_nome, $nova_bio, $data_nasc, $id_usuario);
    $stmt->execute();
    $stmt->close();
}

// Busca dados atualizados
$stmt = $conn->prepare("SELECT nome, biografia, data_nasc, foto FROM aully_usuario WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="./imgStyle/favicon.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <title>Aully</title>
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(to bottom, white, #ff595e);
            font-family: var(--fonte-secundaria, 'Calistoga', serif);
            color: #ff595e;
            margin: 0;
            display: flex;
            flex-direction: column;
        }

        main {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px 0 60px 0;
        }

        .foto-perfil {
            display: flex;
            flex-direction: column;
            align-items: center;
            transform: translateX(20px);
            /* mesmo ajuste para alinhamento */

        }

        .foto-perfil img {
            border-radius: 50%;
            width: 120px;
            height: 120px;
            object-fit: cover;
            border: 3px solid #ff595e;
        }

        .edit-icon {
            cursor: pointer;
            margin-left: 10px;
            color: #ff595e;
            font-size: 18px;
            transition: color 0.2s;
        }

        .edit-icon:hover {
            color: #c43d3d;
        }

        .campo-editavel {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            justify-content: center;
        }

        .campo-editavel input,
        .campo-editavel textarea {
            border-radius: 20px;
            border: 1px solid #ff595e;
            padding: 8px;
            width: 100%;
            max-width: 250px;
            text-align: center;
            font-size: 16px;
            color: #ff595e;
            background: #fff;
            margin-top: 5px;
        }

        .campo-editavel textarea {
            resize: none;
            height: 60px;
        }

        .campo-editavel label {
            font-weight: bold;
            min-width: 80px;
            color: #ff595e;
        }

        .bloqueio {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
        }

        .bloqueio input {
            width: 200px;
            border-radius: 40px;
            height: 30px;
            display: flex;
            border: 1px solid #ff595e;
            text-align: center;
            justify-content: center;
        }

        button,
        .bloqueio button {
            border-radius: 20px;
            background-color: #ff595e;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 12px;
            margin-top: 10px;
            text-align: center;
        }

        button:hover,
        .bloqueio button:hover {
            background-color: #c43d3d;
        }

        .acoes-perfil {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
        }

        .acoes-perfil a {
            color: #fff;
            border-radius: 15px;
            padding: 6px 18px;
            text-decoration: none;
            font-size: 14px;
            margin: 2px 0;
        }

        .acoes-perfil a:hover {
            background: #c43d3d;
        }

        h4 {
            color: white;
        }

        .containerDados {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            gap: 15px;
            transform: translateX(-30px);
            /* Ajusta levemente para esquerda */
        }

        .bloqueio form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        #salvar-btn {
            display: flex;
            justify-content: center;
            transform: translateX(30px);

        }

        .btn-voltar-inicio {
            display: block;
            background: #ff595e;
            color: #fff;
            padding: 10px 28px;
            border-radius: 20px;
            text-decoration: none;
            font-family: var(--fonte-secundaria, 'Calistoga', serif);
            font-size: 1.1em;
            margin: 24px 0 10px 0;
            margin-left: 24px;
            /* <-- Alinha à esquerda */
            transition: background 0.2s, color 0.2s;
            box-shadow: 0 2px 8px rgba(255, 89, 94, 0.10);
            border: none;
            width: fit-content;
        }

        .btn-voltar-inicio:hover {
            background: #c43d3d;
            color: #fff;
        }
    </style>
</head>

<body>
    <a href="paginaPrincipal.php" class="btn-voltar-inicio">← Voltar para o início</a>
    <main>
        <form method="post" enctype="multipart/form-data">
            <section class="containerDados">
                <!-- Foto de perfil -->
                <div class="foto-perfil campo-editavel">
                    <img src="<?= htmlspecialchars(!empty($usuario['foto']) ? $usuario['foto'] : 'uploads/default.png') ?>"
                        alt="Foto de perfil">
                    <i class="fa-solid fa-pen-to-square edit-icon" title="Editar foto"
                        onclick="document.getElementById('foto-input').click()"></i>
                    <input type="file" name="foto" id="foto-input" style="display:none" accept="image/*"
                        onchange="this.form.submit()">
                </div>
                <!-- Nome -->
                <div class="campo-editavel">
                    <label for="nome"></label>
                    <input type="text" name="nome" id="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" readonly>
                    <i class="fa-solid fa-pen-to-square edit-icon" title="Editar nome"
                        onclick="editarCampo('nome')"></i>
                </div>
                <!-- Biografia -->
                <div class="campo-editavel">
                    <label for="bio"></label>
                    <textarea name="bio" id="bio" readonly
                        placeholder="Crie sua biografia"><?= htmlspecialchars($usuario['biografia']) ?></textarea>
                    <i class="fa-solid fa-pen-to-square edit-icon" title="Editar biografia"
                        onclick="editarCampo('bio')"></i>
                </div>
                <!-- Data de nascimento -->
                <div class="campo-editavel">
                    <label for="data_nasc"></label>
                    <input type="date" name="data_nasc" id="data_nasc"
                        value="<?= htmlspecialchars($usuario['data_nasc']) ?>" readonly>
                    <i class="fa-solid fa-pen-to-square edit-icon" title="Editar data de nascimento"
                        onclick="editarCampo('data_nasc')"></i>
                </div>
                <button type="submit" name="atualizar_perfil" id="salvar-btn" style="display: none;">Salvar</button>
        </form>
        </section>
        <!-- Bloqueio de usuário 
        <div class="bloqueio">
            <h4>Bloquear usuário</h4>
            <form method="post" action="bloquearUsuario.php">
                <input type="text" name="usuario_bloquear" placeholder="Insira o nome de usuário">
                <button type="submit">Bloquear</button>
            </form>
        </div>
        -->
        <!-- Ações de perfil -->
        <div class="acoes-perfil">
            <a href="logout.php">Sair</a>
            <a href="excluirPerfil.php"
                onclick="return confirm('Tem certeza que deseja excluir o perfil? Esta ação é irreversível!');">Excluir
                meu perfil</a>
        </div>
    </main>
    <script>
        // Função para ativar edição dos campos
        function editarCampo(id) {
            const campo = document.getElementById(id);
            campo.removeAttribute('readonly');
            campo.focus();
            document.getElementById('salvar-btn').style.display = 'inline-block';
        }
    </script>
</body>

</html>