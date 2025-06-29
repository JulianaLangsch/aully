<?php
session_start();
include_once("conexao.php");

// Busca a denúncia pelo id passado na URL
$id_denuncia = $_GET['id'] ?? null;
$denuncia = null;

// recebe o identificador da denúncia via parâmetro na URL (id) e faz uma consulta no banco para recuperar os detalhes dessa denúncia, incluindo dados da experiência denunciada, como título, descrição, tipo, e o identificador do usuário dono da experiência. Isso é feito com um JOIN entre as tabelas aully_denuncias e aully_experiencia, garantindo que a denúncia seja exibida com todas as informações relevantes para a análise.

if ($id_denuncia) {
    $stmt = $conn->prepare("SELECT d.*, e.titulo, e.descricao, e.id_usuario as id_dono, e.tipo, e.id_experiencia
        FROM aully_denuncias d
        LEFT JOIN aully_experiencia e ON d.id_experiencia = e.id_experiencia
        WHERE d.id_denuncia = ?");
    $stmt->bind_param("i", $id_denuncia);
    $stmt->execute();
    $denuncia = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (!$denuncia) {
    echo "<div style='color:red; text-align:center; margin-top:40px;'>Denúncia não encontrada.</div>";
    exit();
}

// Processa ação do admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    $acao = $_POST['acao'];
    if ($acao === 'aprovar') {
        // Inativa a experiência
        $stmt = $conn->prepare("UPDATE aully_experiencia SET ativa = 0 WHERE id_experiencia = ?");
        $stmt->bind_param("i", $denuncia['id_experiencia']);
        $stmt->execute();
        $stmt->close();

        // Atualiza o total de experiências do tipo
        $stmt_tipo = $conn->prepare("UPDATE aully_experienciastotal SET quantidade = quantidade - 1 WHERE tipo = ? AND id_usuario = ?");
        $stmt_tipo->bind_param("si", $denuncia['tipo'], $denuncia['id_dono']);
        $stmt_tipo->execute();
        $stmt_tipo->close();

        // Remove o registro se a quantidade chegar a zero (opcional)
        $stmt_del = $conn->prepare("DELETE FROM aully_experienciastotal WHERE quantidade <= 0 AND tipo = ? AND id_usuario = ?");
        $stmt_del->bind_param("si", $denuncia['tipo'], $denuncia['id_dono']);
        $stmt_del->execute();
        $stmt_del->close();

        // Marca denúncia como aprovada
        $stmt = $conn->prepare("UPDATE aully_denuncias SET status = 'aprovado' WHERE id_denuncia = ?");
        $stmt->bind_param("i", $id_denuncia);
        $stmt->execute();
        $stmt->close();

        $msg = "Denúncia aprovada e experiência inativada!";
    } elseif ($acao === 'reprovar') {
        // Marca denúncia como reprovada
        $stmt = $conn->prepare("UPDATE aully_denuncias SET status = 'reprovado' WHERE id_denuncia = ?");
        $stmt->bind_param("i", $id_denuncia);
        $stmt->execute();
        $stmt->close();

        $msg = "Denúncia reprovada!";
    }
    header("Location: denuncias.php?msg=" . urlencode($msg));
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Revisar Denúncia</title>
    <link rel="icon" type="image/png" href="./imgStyle/favicon.png">
    <style>
        body {
            font-family: 'Calistoga', Arial, sans-serif;
            background: #f3f3f3;
            margin: 0;
        }

        .container {
            max-width: 500px;
            margin: 40px auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 2px 16px 0 rgba(106, 76, 147, 0.08);
            padding: 32px 28px;
        }

        h2 {
            color: #6a4c93;
        }

        .campo {
            margin-bottom: 18px;
        }

        .campo strong {
            color: #6a4c93;
        }

        .botoes {
            display: flex;
            gap: 18px;
            margin-top: 24px;
        }

        .btn-aprovar,
        .btn-reprovar {
            padding: 10px 28px;
            border: none;
            border-radius: 16px;
            font-size: 1.1em;
            cursor: pointer;
            font-family: inherit;
            font-weight: bold;
        }

        .btn-aprovar {
            background: #8ac926;
            color: #fff;
        }

        .btn-aprovar:hover {
            background: #4f772d;
        }

        .btn-reprovar {
            background: #ff595e;
            color: #fff;
        }

        .btn-reprovar:hover {
            background: #6a4c93;
        }

        .voltar {
            display: inline-block;
            margin-top: 24px;
            color: #6a4c93;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Revisar Denúncia</h2>
        <div class="campo"><strong>Motivo:</strong> <?= htmlspecialchars($denuncia['motivo']) ?></div>
        <div class="campo"><strong>Experiência:</strong> <?= htmlspecialchars($denuncia['titulo']) ?></div>
        <div class="campo"><strong>Descrição:</strong> <?= htmlspecialchars($denuncia['descricao']) ?></div>
        <form method="post" class="botoes">
            <button type="submit" name="acao" value="aprovar" class="btn-aprovar">Aprovar denúncia</button>
            <button type="submit" name="acao" value="reprovar" class="btn-reprovar">Reprovar denúncia</button>
        </form>
        <a href="denuncias.php" class="voltar">← Voltar para denúncias</a>
    </div>
</body>

</html>