<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aully</title>
</head>

<body>
    <form method="POST" action="cadastra_experiencia.php" enctype="multipart/form-data">
        <input type="text" name="titulo" placeholder="Título" required>
        <textarea name="descricao" placeholder="Descrição" required></textarea>

        <label for="tipo">Tipo:</label>
        <select name="tipo" required>
            <option value="sensorial">Sensorial</option>
            <option value="emocional">Emocional</option>
            <option value="corporal">Corporal</option>
            <option value="cognitivo">Cognitivo</option>
            <option value="social">Social</option>
        </select>

        <label for="midia">Mídia (opcional):</label>
        <input type="file" name="midia" accept="image/*,video/*">

        <button type="submit">Salvar Experiência</button>
    </form>


    <?php
    session_start();
    include_once("conexao.php");

    if (!isset($_SESSION['id_usuario'])) {
        die("Usuário não autenticado.");
    }

    $id_usuario = $_SESSION['id_usuario'];

    //Se a requisição for do tipo POST, o código coleta os dados enviados, validando se o tipo selecionado está entre os permitidos. Caso o usuário tenha enviado um arquivo de mídia, o script verifica se o tipo de arquivo é válido, define um nome único para o arquivo, move-o para a pasta uploads e armazena o caminho da mídia na variável $midia.
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $titulo = trim($_POST['titulo']);
        $descricao = trim($_POST['descricao']);
        $tipo = trim($_POST['tipo']);

        $tipos_permitidos = ['sensorial', 'emocional', 'corporal', 'cognitivo', 'social'];
        if (!in_array($tipo, $tipos_permitidos)) {
            die("Tipo inválido.");
        }

        // === Upload de arquivo ===
        $midia = '';
        if (isset($_FILES['midia']) && $_FILES['midia']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_tmp = $_FILES['midia']['tmp_name'];
            $file_name = basename($_FILES['midia']['name']);
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov', 'avi', 'mkv'];
            if (!in_array($file_ext, $allowed_ext)) {
                die("Tipo de arquivo não permitido.");
            }

            $new_name = uniqid('midia_') . '.' . $file_ext;
            $file_dest = $upload_dir . $new_name;

            if (move_uploaded_file($file_tmp, $file_dest)) {
                $midia = $file_dest;
            } else {
                die("Falha no upload do arquivo.");
            }
        }

        // 1. Inserir na aully_experiencia
        $stmt = $conn->prepare("INSERT INTO aully_experiencia
        (titulo, midia, descricao, comentarios, curtidas, votos, porcentagem, id_usuario, ativa) 
        VALUES (?, ?, ?, '', 0, 0, 0, ?, 1)");
        $stmt->bind_param("sssi", $titulo, $midia, $descricao, $id_usuario);
        $stmt->execute();
        $stmt->close();

        // 2. Atualizar ou inserir em aully_experienciastotal (controla quantas experiências de cada tipo o usuário já postou)
        // Incremento no total de experiências postadas pelo usuário na tabela aully_usuario
        $stmt_total = $conn->prepare("SELECT quantidade FROM aully_experienciastotal WHERE tipo = ? AND id_usuario = ?");
        $stmt_total->bind_param("si", $tipo, $id_usuario);
        $stmt_total->execute();
        $result_total = $stmt_total->get_result();

        if ($result_total->num_rows > 0) {
            $stmt_update = $conn->prepare("UPDATE aully_experienciastotal 
            SET quantidade = quantidade + 1 
            WHERE tipo = ? AND id_usuario = ?");
            $stmt_update->bind_param("si", $tipo, $id_usuario);
            $stmt_update->execute();
            $stmt_update->close();
        } else {
            $stmt_insert = $conn->prepare("INSERT INTO aully_experienciastotal
            (quantidade, tipo, id_usuario) VALUES (1, ?, ?)");
            $stmt_insert->bind_param("si", $tipo, $id_usuario);
            $stmt_insert->execute();
            $stmt_insert->close();
        }
        $stmt_total->close();

//Recalcula a porcentagem daquele tipo de experiência em relação ao total de experiências que o usuário postou. Essa porcentagem é calculada dividindo a quantidade de um determinado tipo pela quantidade total postada, multiplicando por 100 e arredondando o valor.

        // 3. Recalcular porcentagem
        $stmt_totais = $conn->prepare("SELECT experienciasPostadas FROM aully_usuario WHERE id_usuario = ?");
        $stmt_totais->bind_param("i", $id_usuario);
        $stmt_totais->execute();
        $result_totais = $stmt_totais->get_result();
        $dados_usuario = $result_totais->fetch_assoc();
        $total_postadas = $dados_usuario['experienciasPostadas'];
        $stmt_totais->close();

        $stmt_qtd = $conn->prepare("SELECT quantidade FROM aully_experienciastotal WHERE tipo = ? AND id_usuario = ?");
        $stmt_qtd->bind_param("si", $tipo, $id_usuario);
        $stmt_qtd->execute();
        $result_qtd = $stmt_qtd->get_result();
        $dados_tipo = $result_qtd->fetch_assoc();
        $qtd_tipo = $dados_tipo['quantidade'];
        $stmt_qtd->close();

        $porcentagem = ($total_postadas > 0) ? round(($qtd_tipo / $total_postadas) * 100) : 0;
        $porcentagem = (int) $porcentagem;

    }
    ?>
</body>

</html>