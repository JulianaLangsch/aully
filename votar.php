<?php
session_start();
include_once("conexao.php");

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$id_experiencia = isset($_GET['id_experiencia']) ? intval($_GET['id_experiencia']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $voto = $_POST['voto']; // sim ou não

    // Verifica se já votou
    $stmt = $conn->prepare("SELECT * FROM aully_votos WHERE id_usuario = ? AND id_experiencia = ?");
    $stmt->bind_param("ii", $id_usuario, $id_experiencia);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        // Registra o voto
        $stmt = $conn->prepare("INSERT INTO aully_votos (id_usuario, id_experiencia, voto) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $id_usuario, $id_experiencia, $voto);
        $stmt->execute();

        // Atualiza a porcentagem de votos "sim"
        $totalVotos = $conn->query("SELECT COUNT(*) AS total FROM aully_votos WHERE id_experiencia = $id_experiencia")->fetch_assoc()['total'];
        $simVotos = $conn->query("SELECT COUNT(*) AS sim FROM aully_votos WHERE id_experiencia = $id_experiencia AND voto = 'sim'")->fetch_assoc()['sim'];

        $porcentagem = $totalVotos > 0 ? intval(($simVotos / $totalVotos) * 100) : 0;

        $stmt = $conn->prepare("UPDATE aully_experiencia SET votos = ?, porcentagem = ? WHERE id_experiencia = ?");
        $stmt->bind_param("iii", $totalVotos, $porcentagem, $id_experiencia);
        $stmt->execute();
    }

    header("Location: paginaPrincipal.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="./imgStyle/favicon.png">
    <title>Aully</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Calistoga&family=Spicy+Rice&display=swap');

        :root {
            --fonte-principal: "Spicy Rice", serif;
            --fonte-secundaria: "Calistoga", serif;
        }

        body {
            background: linear-gradient(to bottom, white, #1982c4);
            background-repeat: no-repeat;
            height: 100vh;
            color: #1982c4;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            margin: 0;
            font-family: var(--fonte-secundaria);
        }

        h4 {
            color: #145a8d;
        }

        h2,
        h3,
        h4 {
            font-family: var(--fonte-principal);
            font-size: clamp(20px, 3vw, 40px);
            padding: 10px;
        }

        .botoes {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }

        button {
            background: #1982c4;
            padding: 20px;
            width: 20vw;
            border: none;
            color: white;
            border-radius: 10px;
            font-family: var(--fonte-secundaria);
            font-size: clamp(20px, 3vw, 40px);
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background: #145a8d;
        }
    </style>
</head>

<body>

    <h2>A experiência também acontece com você?</h2>
    <h3>Na sua opinião, a experiência tem ligação com o autismo?</h3>

    <form method="post" action="processaVoto.php">
        <input type="hidden" name="id_experiencia" value="<?= htmlspecialchars($id_experiencia) ?>">
        <div class="botoes">
            <button type="submit" name="voto" value="sim">Sim</button>
            <button type="submit" name="voto" value="não">Não</button>
        </div>
    </form>

    <h4>Se você se identifica parcialmente ou não acha que a experiência tem ligação com o autismo, vote "não" e
        compartilhe a sua versão de experiência.</h4>

</body>

</html>