<?php
session_start();
include_once("conexao.php");

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

// Quando o usuário clica em "Concordo"
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_usuario = $_SESSION['id_usuario'];

    // Atualiza aceitou_termos
    $update = $conn->prepare("UPDATE aully_usuario SET aceitou_termos = 1 WHERE id_usuario = ?");
    $update->bind_param("i", $id_usuario);
    $update->execute();
    $update->close();

    // Busca o nível de acesso
    $select = $conn->prepare("SELECT nivel_acesso FROM aully_usuario WHERE id_usuario = ?");
    $select->bind_param("i", $id_usuario);
    $select->execute();
    $res = $select->get_result();
    $dados = $res->fetch_assoc();
    $select->close();

    //direciona
    if ($dados['nivel_acesso'] == "1") {
        header("Location: administrador.php");
    } elseif ($dados['nivel_acesso'] == "2") {
        header("Location: gerente.php");
    } else {
        header("Location: paginaPrincipal.php");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Aully</title>
    <link rel="icon" type="image/png" href="./imgStyle/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Calistoga&family=Exo+2&family=Pacifico&family=Playfair+Display&family=Spicy+Rice&display=swap');

        :root {
            --fonte-principal: "Spicy Rice", serif;
            --fonte-secundaria: "Calistoga", serif;
        }

        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .aully {
            font-family: var(--fonte-principal);
            color: purple;
        }

        .logo__aully__infinito {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.80mm;
            font-size: 4.5vw;
            padding: 20px 0 100px;
        }

        .logo__aully__infinito img {
            width: 10vw;
        }

        main {
            display: flex;
            flex-direction: column;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgb(250, 103, 152);
            background: rgba(44, 1, 44, 0.3);
            width: 80vw;
            text-align: center;
            margin-bottom: 20px;
        }

        h1,
        h2 {
            color: rgb(122, 0, 122);
            font-family: var(--fonte-secundaria);
        }

        .termos button {
            border-radius: 25px;
            background: linear-gradient(to right, #cb6ce6, #ff5757);
            border: none;
            padding: 15px;
            font-size: 1.8rem;
            cursor: pointer;
            color: white;
            font-family: var(--fonte-secundaria);
        }
    </style>
</head>

<body>

    <header>
        <div class="logo__aully__infinito">
            <div class="aully">Aully</div>
            <img src="./imgStyle/logo.png" alt="Mãos em um formato de infinito com as cores do arco-íris">
        </div>
    </header>

    <main>
        <div class="termos">
            <h1>Seja bem-vindo(a)!</h1>
            <h2>Este site não deverá ser utilizado para auto diagnóstico, nem para testagem e investigação do TEA. Use
                com responsabilidade.<br><br>

                <strong>Termos de Uso</strong><br><br>
                1. Aceitação dos Termos<br>
                Ao usar nosso aplicativo, você concorda com estes Termos de Uso.<br><br>

                2. Coleta de Dados<br>
                Coletamos e armazenamos informações que você insere em nosso aplicativo. Isso inclui, mas não se limita
                a, suas experiências pessoais, respostas a votações e quaisquer estratégias de enfrentamento que você
                compartilhe.<br><br>

                3. Uso dos Dados<br>
                Os dados que coletamos são usados para fins de pesquisa e estudo.<br><br>

                4. Compartilhamento de Dados<br>
                Experiências relevantes podem ser compartilhadas com a comunidade.<br><br>

                5. Privacidade<br>
                Respeitamos sua privacidade e tomamos medidas para proteger suas informações.<br><br>

                6. Alterações aos Termos<br>
                Podemos alterar estes Termos de Uso de tempos em tempos. Se fizermos alterações, notificaremos você.
            </h2>

            <form method="POST">
                <button type="submit">Concordo</button>
            </form>
        </div>
    </main>

</body>

</html>