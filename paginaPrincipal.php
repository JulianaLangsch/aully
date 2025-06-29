<?php
// Inclui o arquivo de conexão com o banco de dados
include_once("conexao.php");

// Inicia a sessão para verificar se o usuário está logado
session_start();

// Se não estiver logado, redireciona para a página de login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

// Consulta todos os posts sem limite
$stmt = $conn->prepare("SELECT e.id_experiencia, e.titulo, e.tipo, e.descricao, e.midia, e.ativa, e.id_usuario, 
           u.username, u.nome, u.foto 
    FROM aully_experiencia e 
    JOIN aully_usuario u ON e.id_usuario = u.id_usuario 
    WHERE e.ativa = 1 
    ORDER BY e.id_experiencia DESC");
$stmt->execute();

// Armazena os resultados da consulta
$experiencias = $stmt->get_result();

/*
Função que exibe comentários e respostas recursivamente
@param $conn: conexão com banco de dados
@param $id_experiencia: id do post ao qual os comentários pertencem
@param $id_pai: comentário pai (se for resposta), ou NULL se for comentário principal
@param $nivel: profundidade do comentário (para evitar loops infinitos de resposta)
*/
function exibirComentarios($conn, $id_experiencia, $id_pai = NULL, $nivel = 0)
{
    // Evita que a função exiba mais de 3 níveis de respostas
    if ($nivel >= 3)
        return;

    // Se for uma resposta a outro comentário (tem um pai definido)
    if ($id_pai) {
        // Seleciona respostas específicas a um comentário
        $sql_com = "SELECT c.*, u.username, u.nome, u.foto FROM aully_comentarios c 
                    JOIN aully_usuario u ON c.id_usuario = u.id_usuario
                    WHERE c.id_experiencia = ? AND c.id_resposta = ?
                    ORDER BY c.data_comentario ASC";
        $stmt = $conn->prepare($sql_com);
        $stmt->bind_param("ii", $id_experiencia, $id_pai);
    } else {
        // Seleciona os comentários principais (que não são respostas)
        $sql_com = "SELECT c.*, u.username, u.nome, u.foto FROM aully_comentarios c 
                    JOIN aully_usuario u ON c.id_usuario = u.id_usuario
                    WHERE c.id_experiencia = ? AND c.id_resposta IS NULL
                    ORDER BY c.data_comentario ASC";
        $stmt = $conn->prepare($sql_com);
        $stmt->bind_param("i", $id_experiencia);
    }

    // Executa a consulta e armazena os comentários
    $stmt->execute();
    $comentarios = $stmt->get_result();

    // Loop para exibir cada comentário encontrado
    while ($coment = $comentarios->fetch_assoc()) { ?>
        <div class="comentario-inner" style="padding-left:0;">
            <!-- Exibe as informações do autor do comentário -->
            <div class="usuario-info">
                <!-- Foto do usuário -->
                <img src="<?= htmlspecialchars($coment['foto'] ?? 'uploads/default.png') ?>" class="foto-perfil-coment"
                    alt="Foto de perfil do usuário">
                <div>
                    <!-- Nome e username -->
                    <strong><?= htmlspecialchars($coment['nome']) ?></strong>
                    <span class="username">@<?= htmlspecialchars($coment['username']) ?></span>
                </div>
            </div>

            <!-- Texto do comentário -->
            <p><?= htmlspecialchars($coment['comentario']) ?></p>

            <!-- Formulário de resposta ao comentário atual -->
            <form action="comentarExperiencia.php" method="post" class="form-comentar">
                <input type="hidden" name="id_experiencia" value="<?= $id_experiencia ?>">
                <input type="hidden" name="id_resposta" value="<?= $coment['id_comentario'] ?>">
                <textarea name="comentario" placeholder="Responder..." required></textarea>
                <button type="submit" class="comentario-btn">Responder</button>
            </form>

            <?php
            // Verifica se o usuário já iluminou este comentário
            $ja_iluminou_coment = false;
            if (isset($_SESSION['id_usuario'])) {
                // Consulta para saber se este usuário já iluminou o comentário
                $stmt_check = $conn->prepare("SELECT 1 FROM aully_iluminacoes_comentario WHERE id_usuario = ? AND id_comentario = ?");
                $stmt_check->bind_param("ii", $_SESSION['id_usuario'], $coment['id_comentario']);
                $stmt_check->execute();
                $stmt_check->store_result();
                $ja_iluminou_coment = $stmt_check->num_rows > 0;
                $stmt_check->close();

                // Conta o número total de iluminações deste comentário
                $stmtIlum = $conn->prepare("SELECT COUNT(*) as total FROM aully_iluminacoes_comentario WHERE id_comentario = ?");
                $stmtIlum->bind_param("i", $coment['id_comentario']);
                $stmtIlum->execute();
                $resIlum = $stmtIlum->get_result()->fetch_assoc();
                $stmtIlum->close();
                ?>

                <!-- Botão de "Iluminar comentário" -->
                <form action="iluminarComentario.php" method="post">
                    <input type="hidden" name="id_comentario" value="<?= $coment['id_comentario'] ?>">

                    <!-- Ícone de estrela com classe ativa se já iluminou -->
                    <button type="submit" class="sparkle-btn<?= $ja_iluminou_coment ? ' active' : '' ?>"
                        title="Iluminar comentário">
                        <svg class="sparkle-icon" width="28" height="28" viewBox="0 0 28 28" fill="none">
                            <polygon points="14,2 17.5,10.5 27,11 19,17 21.5,26 14,21 6.5,26 9,17 1,11 10.5,10.5" stroke="#fff"
                                stroke-width="2" fill="none" />
                        </svg>
                    </button>

                    <!-- Exibe o número total de iluminações -->
                    <span style="color:#ffca3a; font-size:1em; margin-left:2px;"><?= $resIlum['total'] ?? 0 ?></span>
                </form>

                <?php
                // Chama a função recursivamente para exibir respostas a este comentário
                exibirComentarios($conn, $id_experiencia, $coment['id_comentario'], $nivel + 1);
                ?>
            </div>
        <?php }
    }

    // Fecha o statement da consulta de comentários
    $stmt->close();
}
?>



<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Aully</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="./imgStyle/favicon.png">
    <!-- Essa linha importa a biblioteca jQuery da internet -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Calistoga&family=Spicy+Rice&display=swap');

        :root {
            --fonte-principal: "Spicy Rice", serif;
            --fonte-secundaria: "Calistoga", serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .aully {
            font-family: var(--fonte-principal);
        }

        .logo__aully__infinito {
            display: flex;
            justify-content: end;
            align-items: center;
            color: purple;
            gap: 0.80mm;
            font-size: 5vw;
            width: 10vw;
        }

        .logo {
            display: flex;
            justify-content: end;
            align-items: center;
            height: 3vh;
            padding: 50px;
        }

        .logo__aully__infinito img {
            width: 10vw;
        }

        .feed {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            text-align: center;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #6a4c93;
            padding: 20px;
            margin-bottom: 10px;
            border-radius: 8px;
            justify-content: center;
            width: 90%;
            height: 70%;
            box-sizing: border-box;
        }

        .anuncioG {
            display: none;
        }

        .containerPosts {
            padding: 10px;
            border: 2px solid #6a4c93;
            border-radius: 20px;
            margin-bottom: 30px;
            margin-top: 0;
            width: 45vw;
        }

        .titulo,
        .tipo {
            display: block;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 20px;
            font-size: 12px;
            margin-bottom: 20px;
        }

        .titulo {
            width: 5vw;
        }

        .tipo {
            width: 5vw;
        }

        .descreva {
            text-align: center;
            font-size: 15px;
            border-radius: 20px;
            width: 50%;
            resize: none;
            margin-bottom: 20px;
        }

        .titulo,
        .descreva {
            border-radius: 40px;
        }

        .titulo::placeholder,
        .descreva::placeholder {
            font-size: 12px;
            color: #6a4c93;
        }

        .tipo {
            font-size: 12px;
            color: #6a4c93;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 40px;
        }

        .input-file {
            display: none;
        }

        .file-label {
            display: inline-block;
            padding: 10px 20px;
            background: #ff595e;
            color: #fff;
            border-radius: 20px;
            cursor: pointer;
            font-family: var(--fonte-secundaria);
            font-size: 16px;
            margin-bottom: 15px;
            transition: background 0.2s;
        }

        .file-label:hover {
            background: #c43d3d;
        }

        .submit-btn {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #ff595e;
            border-radius: 50px;
            color: white;
            border: none;
            padding: 15px;
            display: block;
            margin: 0 auto;
            margin-top: 20px;
            width: 50vw;
            font-family: var(--fonte-secundaria);
            font-size: 20px;
        }

        .titulo:not(:placeholder-shown),
        .descreva:not(:placeholder-shown) {
            font-size: 18px;
            color: #6a4c93;
        }

        .anuncio {
            display: flex;
            justify-content: center;
            align-items: center;
            background: #ffca3a;
            width: 90vw;
            height: 10vh;
            padding: 10px;
            font-family: var(--fonte-secundaria);
            border-radius: 10px;
            margin-top: -80px
        }

        .anuncie_aqui {
            text-decoration: none;
            color: white;
        }

        nav {
            display: flex;
            gap: 5px;
        }

        .menu {
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: fixed;
            bottom: 0;
            margin: 0;
            width: 100%;
            padding: 10px;
            background: white;

            & a {
                flex: 1;
                color: white;
                text-decoration: none;
                font-family: var(--fonte-secundaria);
                font-size: 4vw;
                padding: 8px;

                &:last-child {
                    flex: 2.5;
                }
            }
        }

        .perfil {
            background: #ff595e;
            border-radius: 8px;
        }

        .chat {
            background: #ffca3a;
            border-radius: 8px;
        }

        .rastreamento {
            background: #8ac926;
            border-radius: 8px;
        }

        .sugestoes {
            background: #1982c4;
            border-radius: 8px;
        }

        .posts {
            background: white;
            border-radius: 0;
            margin-top: 20px;
            padding: 10px 0;
            width: 100%;
        }

        .post {
            background: #8ac926;
            border-radius: 24px;
            color: #fff;
            margin-bottom: 32px;
            /* Espaço entre posts */
            box-shadow: 0 4px 18px rgba(106, 76, 147, 0.08);
            padding: 22px 20px 18px 20px;
            position: relative;
            transition: box-shadow 0.2s;
        }

        .post:last-child {
            margin-bottom: 0;
        }

        .vote-btn {
            background-color: #ff595e;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            font-family: var(--fonte-secundaria);
            font-size: 14px;
            margin-top: 10px;
            margin-bottom: 20px;
            text-decoration: none;
            justify-self: center;
        }

        .vote-percentage {
            font-size: 14px;
            color: white;
            font-weight: bold;
            margin-top: 10px;
        }

        @media (min-width: 1024px) {

            .containerPosts {
                padding: 10px;
                border: 2px solid #6a4c93;
                border-radius: 20px;
                margin-bottom: 30px;
                margin-top: 0;
                width: 60vw;
            }

            .vote-btn {
                width: 50vw;
            }

            .container {
                width: 58vw;
            }

            .posts {
                margin-top: 0;
            }

            .titulo,
            .tipo,
            .descreva {
                width: 100%;
                margin: 0 auto 20px auto;
                display: block;
            }

            .titulo {
                padding: 20px;
            }

            .descreva {
                padding: 30px;
            }

            .submit-btn {
                display: flex;
                text-align: center;
                height: 8vh;
            }

            .formulario {
                width: 90%;
            }

            .anuncio {
                display: none;
            }

            a {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .titulo::placeholder,
            .descreva::placeholder {
                font-size: 25px;
                color: #6a4c93;
            }

            .tipo {
                font-size: 25px;
                color: #6a4c93;
                padding: 10px;
                border: 1px solid #ccc;
                border-radius: 30px;
            }

            .submit-btn {
                width: 20vw;
                padding: 20px;
                border-radius: 30px;
                font-size: 25px;
            }

            .anuncioG {
                display: block;
                height: 20vh;
                background: #ff595e;
                border-radius: 8px;
                grid-column: span 2;
                grid-row: span 1;
                margin-bottom: 20px;
            }

            .menu {
                display: flex;
                justify-content: center;
                position: absolute;
                right: 0;
                top: 0;
                width: 40vw;
                height: 20vw;
                margin-top: 250px;
                background-color: transparent;
            }

            nav {
                display: grid;
                grid-template-columns: 1fr 1fr;
                grid-template-rows: 1fr 1fr;
                gap: 10px;
            }

            .menu a {
                padding: 20px;
                font-size: 22px;
            }

            .perfil {
                height: 7vw;
                width: 15vw;
                align-items: center;
            }

            .chat {
                width: 15vw;
                align-items: center;
            }

            .feed {
                align-items: start;
                padding: 15px;
            }
        }

        /* FOTO DE PERFIL MAIOR */
        .foto-perfil-coment,
        .foto-perfil-post {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
        }

        /* USUÁRIO: nome e username juntos, username abaixo */
        .usuario-info {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }

        .usuario-info>div {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            line-height: 1.1;
        }

        .usuario-info strong {
            font-size: 1.1em;
            margin-bottom: 0;
        }

        .username {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.95em;
            margin: 0;
            padding: 0 0 0 10px;
            line-height: 1.1;
        }

        .nome {
            padding: 15px;
        }

        /* COMENTÁRIOS E RESPOSTAS: caixa arredondada */
        .comentario {
            margin-top: 10px;
            color: #333;
            border-radius: 16px;
            padding: 10px;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-self: center;
        }

        .comentario-inner {
            width: 100%;
        }

        /* BOTÃO ILUMINAR: alinhado à direita */
        .comentario .comentario-actions,
        .post .post-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 0;
            margin-bottom: 0;
        }


        /* Ajuste para formulário de resposta/comentário */
        .comentario form,
        .comentarios>form {
            margin-top: 6px;
            margin-bottom: 0;
        }

        .comentario textarea,
        .comentarios>form textarea {
            width: 100%;
            border-radius: 20px;
            border: 1px solid #ddd;
            padding: 10px;
            font-size: 1em;
            resize: none;
            margin-bottom: 6px;
        }

        .post-meta {
            margin-top: 8px;
            font-size: 0.9em;
            color: rgba(255, 255, 255, 0.8);
        }

        .toggle-comentarios {
            margin-top: 6px;
            background: none;
            border: none;
            color: #fff;
            cursor: pointer;
            text-decoration: underline;
            font-size: 0.9em;
        }

        .preview-midia {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 10px;
            justify-content: flex-start;
        }

        .preview-item {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .preview-img,
        .preview-video {
            max-width: 120px;
            max-height: 120px;
            border-radius: 10px;
            background: #fff;
            object-fit: cover;
        }

        .preview-video {
            background: #000;
        }

        .remove-btn {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ff595e;
            color: #fff;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            cursor: pointer;
            z-index: 2;
            border: 2px solid #fff;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.12);
            transition: background 0.2s;
        }

        .remove-btn:hover {
            background: #c43d3d;
        }

        .midias-post {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding: 20px;
            margin-bottom: 10px;
            justify-content: center;
        }

        .midia-img,
        .midia-video {
            max-width: 180px;
            max-height: 180px;
            border-radius: 10px;
            background: #fff;
            object-fit: cover;
        }

        .midia-video {
            background: #000;
        }

        .preview-img,
        .preview-video {
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }

        .preview-img:hover,
        .preview-video:hover {
            transform: scale(1.07);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.18);
        }

        #modal-midia {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            justify-content: center;
            align-items: center;
        }

        #modal-midia.active {
            display: flex;
        }

        #modal-midia-content img,
        #modal-midia-content video {
            max-width: 90vw;
            max-height: 80vh;
            border-radius: 16px;
            background: #fff;
        }

        #modal-midia-close {
            position: absolute;
            top: 30px;
            right: 40px;
            font-size: 2.5em;
            color: #fff;
            cursor: pointer;
            z-index: 10000;
            font-weight: bold;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 50%;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #modal-midia-close:hover {
            background: #ff595e;
        }

        #btn-topo:hover {
            background: #ff595e;
            transform: scale(1.08);
        }

        #btn-topo {
            position: fixed;
            bottom: 32px;
            right: 32px;
            width: 54px;
            height: 54px;
            border-radius: 50%;
            background: #6a4c93;
            color: #fff;
            border: none;
            font-size: 2em;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18);
            cursor: pointer;
            z-index: 1000;
            display: none;
            transition: background 0.2s, transform 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .post-options {
            position: absolute;
            top: 12px;
            right: 12px;
        }

        .options-btn {
            background: none;
            border: none;
            font-size: 1.6em;
            color: #fff;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 50%;
            transition: background 0.2s;
        }

        .options-btn:hover {
            background: rgba(0, 0, 0, 0.1);
        }

        .options-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 32px;
            background: #fff;
            color: #333;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18);
            min-width: 120px;
            z-index: 10;
            flex-direction: column;
            padding: 6px 0;
        }

        .options-menu a,
        .options-menu button {
            display: block;
            width: 100%;
            background: none;
            border: none;
            color: #333;
            text-align: left;
            padding: 8px 16px;
            cursor: pointer;
            font-size: 1em;
            border-radius: 0;
            transition: background 0.2s;
            text-decoration: none;
        }

        .options-menu a:hover,
        .options-menu button:hover {
            background: #f2f2f2;
        }


        /* Modal denúncia */
        .modal-denuncia-bg {
            display: none;
            position: fixed;
            z-index: 99999;
            left: 0;
            top: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.55);
            justify-content: center;
            align-items: center;
        }

        .modal-denuncia-bg.active {
            display: flex;
        }

        .modal-denuncia-box {
            background: #fff;
            border-radius: 18px;
            padding: 32px 28px 22px 28px;
            min-width: 320px;
            max-width: 90vw;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.18);
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: stretch;
        }

        .modal-denuncia-close {
            position: absolute;
            top: 12px;
            right: 18px;
            font-size: 2em;
            color: #6a4c93;
            cursor: pointer;
            font-weight: bold;
            transition: color 0.2s;
        }

        .modal-denuncia-close:hover {
            color: #ff595e;
        }

        #form-denuncia label {
            font-weight: bold;
            margin-bottom: 6px;
            color: #6a4c93;
        }

        #form-denuncia textarea {
            width: 100%;
            min-height: 70px;
            border-radius: 10px;
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 14px;
            font-size: 1em;
            resize: vertical;
        }

        #form-denuncia button[type="submit"] {
            background: #ff595e;
            color: #fff;
            border: none;
            border-radius: 18px;
            padding: 10px 0;
            font-size: 1.1em;
            font-family: var(--fonte-secundaria, inherit);
            cursor: pointer;
            transition: background 0.2s;
        }

        #form-denuncia button[type="submit"]:hover {
            background: #c43d3d;
        }

        .sparkle-btn {
            background: transparent;
            border: none;
            border-radius: 50%;
            padding: 4px;
            cursor: pointer;
            margin-left: auto;
            align-self: flex-end;
            transition: box-shadow 0.3s, transform 0.2s;
            outline: none;
            box-shadow: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sparkle-btn:hover {
            transform: scale(1.2);
        }

        .sparkle-icon {
            width: 28px;
            height: 28px;
            display: block;
        }

        .sparkle-btn .sparkle-icon polygon {
            stroke: #fff !important;
            fill: none !important;
            transition: fill 0.2s, stroke 0.2s;
        }

        .sparkle-btn.active .sparkle-icon polygon {
            fill: #ffca3a !important;
            stroke: #ffca3a !important;
        }

        .sparkle-btn.active {
            animation: acender 0.4s;
            box-shadow: 0 0 24px 8px #ffca3a80;
        }

        .comentario .sparkle-btn,
        .comentario .sparkle-btn.active {
            background: transparent !important;
            width: 40px;
            height: 40px;
            min-width: 40px;
            min-height: 40px;
            max-width: 40px;
            max-height: 40px;
            border-radius: 50% !important;
            display: flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
            margin: 0 0 0 auto;
            padding: 0;
        }

        .comentario .sparkle-btn .sparkle-icon {
            width: 28px !important;
            height: 28px !important;
            min-width: 28px;
            min-height: 28px;
            max-width: 28px;
            max-height: 28px;
            display: block;
            margin: 0 auto;
            /* Garante centralização vertical/horizontal */
            align-self: center;
            justify-self: center;
        }

        @keyframes acender {
            0% {
                box-shadow: 0 0 0 0 #ffca3a00;
                background: transparent;
                transform: scale(1);
            }

            60% {
                box-shadow: 0 0 32px 16px #ffca3a80;
                background: transparent;
                transform: scale(1.2);
            }

            100% {
                box-shadow: 0 0 24px 8px #ffca3a80;
                background: transparent;
                transform: scale(1);
            }
        }

        #btn-topo {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.4s ease, transform 0.4s ease;
        }

        #btn-topo.mostrar {
            opacity: 1;
            transform: translateY(0);
        }

        .vote-percentage {
            font-size: 1.1em;
            font-weight: bold;
            margin-top: 10px;
            color: #fff;
        }

        .comentario {
            width: 95%;
            max-width: 900px;
            margin: 18px auto 0 auto;
            background: #fff;
            color: #8ac926;
            border-radius: 16px;
            padding: 18px 18px 12px 18px;
            border: 2px solid #8ac926;
            box-shadow: 0 2px 8px rgba(106, 76, 147, 0.04);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-self: center;
        }

        .comentario .usuario-info strong,
        .comentario .username {
            color: #8ac926;
        }

        .comentario textarea,
        .comentarios>form textarea {
            width: 100%;
            border-radius: 20px;
            border: 2px solid #8ac926;
            padding: 10px;
            font-size: 1em;
            resize: none;
            margin-bottom: 6px;
            color: #8ac926;
            background: #f8fff8;
        }

        p {
            color: #8ac926;
        }


        .comentario-btn {
            background: linear-gradient(90deg, #6a4c93 60%, #ff595e 100%);
            color: #fff;
            border: none;
            border-radius: 22px;
            padding: 12px 36px;
            font-size: 1.1em;
            font-family: var(--fonte-secundaria, Arial, sans-serif);
            font-weight: bold;
            cursor: pointer;
            margin-top: 8px;
            box-shadow: 0 2px 8px rgba(106, 76, 147, 0.08);
            transition: background 0.2s, transform 0.15s;
            letter-spacing: 0.5px;
        }

        .comentario-btn:hover {
            background: linear-gradient(90deg, #ff595e 60%, #6a4c93 100%);
            transform: scale(1.05);
        }

        /* Estrela dos comentários: verde quando inativa, amarela quando ativa */
        .comentario .sparkle-btn .sparkle-icon polygon {
            stroke: #8ac926 !important;
            /* verde */
            fill: none !important;
            transition: fill 0.2s, stroke 0.2s;
        }

        .comentario .sparkle-btn.active .sparkle-icon polygon {
            stroke: #ffca3a !important;
            /* amarelo */
            fill: #ffca3a !important;
        }

        .descricao-exp {
            color: #fff !important;
            font-size: 1.1em;
            margin: 12px 0 18px 0;
            word-break: break-word;
        }

        @media (max-width: 600px) {
            .containerPosts {
                width: 98vw;
                padding: 4px;
            }

            .titulo,
            .tipo,
            .descreva {
                width: 98vw;
                max-width: 98vw;
                font-size: 1em;
            }

            .midias-post .midia-img,
            .midias-post .midia-video {
                max-width: 98vw;
                max-height: 180px;
            }
        }

        .modal-editar-bg {
            display: none;
            position: fixed;
            z-index: 99999;
            left: 0;
            top: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.55);
            justify-content: center;
            align-items: center;
        }

        .modal-editar-bg.active {
            display: flex;
        }

        .modal-editar-box {
            background: #fff;
            border-radius: 18px;
            padding: 32px 28px 22px 28px;
            min-width: 320px;
            max-width: 90vw;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.18);
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: stretch;
        }

        .vote-thanks {
            padding: 20px;
        }
    </style>
</head>

<body>

    <header class="logo">
        <div class="logo__aully__infinito">
            <div class="aully">Aully</div>
            <img src="./imgStyle/logo.png" alt="Mãos em um formato de infinito com as cores do arco-íris">
        </div>
    </header>

    <main class="feed">

        <div class="containerPosts">
            <section class="container">
                <form method="post" class="formulario" action="processaPost.php" enctype="multipart/form-data">
                    <input type="text" name="titulo" placeholder="Título" class="titulo" required maxlength="80">
                    <select name="tipo" class="tipo">
                        <option value="sensorial">Sensorial</option>
                        <option value="emocional">Emocional</option>
                        <option value="corporal">Corporal</option>
                        <option value="cognitivo">Cognitivo</option>
                        <option value="social">Social</option>
                    </select>
                    <textarea name="descricao" class="descreva" placeholder="Descreva a sua experiência" required
                        maxlength="1000"></textarea>
                    <label class="file-label" for="midia-input">
                        Escolher arquivos
                        <div id="limite-arquivos" style="color:#6a4c93; font-size:0.95em; margin-bottom:6px;">0/4
                            arquivos anexados</div>
                        <input type="file" name="midia[]" id="midia-input" class="input-file" accept="image/*,video/*"
                            multiple>
                    </label>
                    <div id="preview-midia" class="preview-midia"></div>
                    <button type="submit" class="submit-btn">Postar</button>
                </form>
            </section>
        </div>

        <div class="containerAnuncio">
            <section class="anuncio">
                <a class="anuncie_aqui" href="#">Anuncie Aqui</a>
            </section>
        </div>

        <section class="menu">
            <nav>
                <a class="perfil" href="perfil.php">Perfil</a>
                <a class="chat" href="construcao.html">Conversas</a>
                <a class="rastreamento" href="rastreamento.php">Rastreamento</a>
                <a class="sugestoes" href="construcao.html">Sugestões</a>
                <a class="anuncioG"
                    href="https://wa.me/5551980266081?text=Ol%C3%A1!%20Gostaria%20de%20anunciar%20meu%20produto/servi%C3%A7o%20no%20seu%20site."
                    target="_blank">Anuncie aqui</a>
            </nav>
        </section>

        <section id="posts" class="posts">

            <?php while ($exp = $experiencias->fetch_assoc()) { ?>
                <div class="post" data-id="<?= $exp['id_experiencia'] ?>">
                    <div class="usuario-info">
                        <img src="<?= htmlspecialchars($exp['foto'] ?? 'uploads/default.png') ?>" class="foto-perfil-post"
                            alt="Foto de perfil do usuário">
                        <div>
                            <div class="nome">
                                <strong><?= htmlspecialchars($exp['nome']) ?></strong>
                            </div>
                            <div class="username">@<?= htmlspecialchars($exp['username']) ?></div>
                        </div>
                    </div>
                    <div class="post-options">
                        <button class="options-btn">⋮</button>
                        <div class="options-menu">
                            <button type="button" class="denunciar-btn"
                                data-id="<?= $exp['id_experiencia'] ?>">Denunciar</button>
                            <?php if ($_SESSION['id_usuario'] == $exp['id_usuario']): ?>
                                <button type="button" class="editar-btn" data-id="<?= $exp['id_experiencia'] ?>">Editar</button>
                                <button type="button" class="excluir-btn"
                                    data-id="<?= $exp['id_experiencia'] ?>">Excluir</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <h3><?= htmlspecialchars($exp['titulo']) ?></h3>
                    <div class="tipo-experiencia" style="color:#ffca3a;font-weight:bold;margin-bottom:6px;">
                        <?= ucfirst(htmlspecialchars($exp['tipo'])) ?>
                    </div>
                    <p class="descricao-exp"><?= htmlspecialchars($exp['descricao']) ?></p>
                    <?php
                    if ($exp['midia']) {
                        $midias = json_decode($exp['midia'], true);
                        if (is_array($midias)) {
                            echo '<div class="midias-post">';
                            $count = 0;
                            foreach ($midias as $midia) {
                                if ($count >= 4)
                                    break; // Limita a 4 mídias
                                $ext = strtolower(pathinfo($midia, PATHINFO_EXTENSION));
                                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                    echo '<img src="' . htmlspecialchars($midia) . '" alt="Midia" class="midia-img" onclick="abrirModal(\'' . htmlspecialchars($midia) . '\', \'img\')">';
                                } elseif (in_array($ext, ['mp4', 'avi', 'mov'])) {
                                    echo '<video controls class="midia-video" onclick="abrirModal(\'' . htmlspecialchars($midia) . '\', \'video\')"><source src="' . htmlspecialchars($midia) . '"></video>';
                                }
                                $count++;
                            }
                            echo '</div>';
                        }
                    }

                    ?>
                    <!-- Votos -->
                    <?php
                    $stmt_ilum = $conn->prepare("SELECT COUNT(*) as total FROM aully_iluminacoes WHERE id_experiencia = ?");
                    $stmt_ilum->bind_param("i", $exp['id_experiencia']);
                    $stmt_ilum->execute();
                    $res_ilum = $stmt_ilum->get_result()->fetch_assoc();
                    $stmt_ilum->close();

                    $stmt_com = $conn->prepare("SELECT COUNT(*) as total FROM aully_comentarios WHERE id_experiencia = ?");
                    $stmt_com->bind_param("i", $exp['id_experiencia']);
                    $stmt_com->execute();
                    $res_com = $stmt_com->get_result()->fetch_assoc();
                    $stmt_com->close();
                    ?>

                    <div class="post-meta">
                        <span>💖 <?= $res_ilum['total'] ?> iluminações</span> |
                        <span>💬 <?= $res_com['total'] ?> comentários</span>
                    </div>

                    <?php
                    // Conta votos para o post
                    $stmt_votos = $conn->prepare("SELECT voto, COUNT(*) as total FROM aully_votos WHERE id_experiencia = ? GROUP BY voto");
                    $stmt_votos->bind_param("i", $exp['id_experiencia']);
                    $stmt_votos->execute();
                    $res_votos = $stmt_votos->get_result();

                    $total_votos = 0;
                    $sim = 0;
                    $nao = 0;
                    while ($row = $res_votos->fetch_assoc()) {
                        $total_votos += $row['total'];
                        if ($row['voto'] === 'sim')
                            $sim = $row['total'];
                        if ($row['voto'] === 'nao')
                            $nao = $row['total'];
                    }
                    $stmt_votos->close();

                    // Verifica se o usuário já votou
                    $ja_votou = false;
                    if (isset($_SESSION['id_usuario'])) {
                        $stmt_check = $conn->prepare("SELECT 1 FROM aully_votos WHERE id_usuario = ? AND id_experiencia = ?");
                        $stmt_check->bind_param("ii", $_SESSION['id_usuario'], $exp['id_experiencia']);
                        $stmt_check->execute();
                        $stmt_check->store_result();
                        $ja_votou = $stmt_check->num_rows > 0;
                        $stmt_check->close();
                    }


                    // Define se comentários e iluminações estão liberados
                    //Só libera comentários/iluminações se já houver 5 votos ou mais e pelo menos 70% desses votos forem "sim".
                    $liberado = ($total_votos >= 5 && $sim / $total_votos >= 0.7);
                    // Mostra porcentagem se já houver 5 votos
                    if ($total_votos >= 5) {
                        $porcentagem_sim = round(($sim / $total_votos) * 100);
                        $porcentagem_nao = 100 - $porcentagem_sim;
                        ?>
                        <div class="vote-percentage">
                            👍 <?= $porcentagem_sim ?>% &nbsp;|&nbsp; 👎 <?= $porcentagem_nao ?>%
                            <span style="font-size:0.9em;color:#ccc;">(<?= $total_votos ?> votos)</span>
                        </div>
                        <?php
                        if ($liberado) {
                            // Liberado, não mostra botão votar
                        } elseif (!$ja_votou && $_SESSION['id_usuario'] != $exp['id_usuario']) {
                            ?>
                            <a href="votar.php?id_experiencia=<?= $exp['id_experiencia'] ?>" class="vote-btn">Votar</a>
                            <?php
                        }
                    } else {
                        if ($ja_votou) { ?>
                            <div class="vote-thanks">
                                Obrigada pelo seu voto!<br>
                                Quando houver pelo menos 5 votos, a porcentagem será exibida.<br>
                                Se a experiência alcançar <b>70% ou mais de votos "sim"</b>, os comentários e iluminações serão
                                liberados!
                            </div>
                        <?php } elseif ($_SESSION['id_usuario'] != $exp['id_usuario']) { ?>
                            <a href="votar.php?id_experiencia=<?= $exp['id_experiencia'] ?>" class="vote-btn">Votar</a>
                        <?php }
                    }
                    ?>

                    <?php if ($liberado): ?>
                        <?php
                        $ja_iluminou = false;
                        if (isset($_SESSION['id_usuario'])) {
                            $stmt_check = $conn->prepare("SELECT 1 FROM aully_iluminacoes WHERE id_usuario = ? AND id_experiencia = ?");
                            $stmt_check->bind_param("ii", $_SESSION['id_usuario'], $exp['id_experiencia']);
                            $stmt_check->execute();
                            $stmt_check->store_result();
                            $ja_iluminou = $stmt_check->num_rows > 0;
                            $stmt_check->close();
                        }
                        ?>
                        <form action="iluminarExperiencia.php" method="post" class="form-iluminar">
                            <input type="hidden" name="id_experiencia" value="<?= $exp['id_experiencia'] ?>">
                            <button type="submit" class="sparkle-btn<?= $ja_iluminou ? ' active' : '' ?>"
                                title="Iluminar postagem">
                                <svg class="sparkle-icon" width="28" height="28" viewBox="0 0 28 28" fill="none">
                                    <polygon points="14,2 17.5,10.5 27,11 19,17 21.5,26 14,21 6.5,26 9,17 1,11 10.5,10.5"
                                        stroke="#fff" stroke-width="2" fill="none" />
                                </svg>
                            </button>
                        </form>

                        <!-- Comentários principais -->
                        <form action="comentarExperiencia.php" method="post" class="form-comentar" style="margin-top:12px;">
                            <input type="hidden" name="id_experiencia" value="<?= $exp['id_experiencia'] ?>">
                            <input type="hidden" name="id_resposta" value="">
                            <textarea name="comentario" class="comentario" placeholder="Comente aqui..." required></textarea>
                            <button type="submit" class="comentario-btn">Comentar</button>
                        </form>

                        <?php
                        // Comentários principais
                        $sql_com = "SELECT c.*, u.username, u.nome, u.foto FROM aully_comentarios c 
    JOIN aully_usuario u ON c.id_usuario = u.id_usuario
    WHERE c.id_experiencia = ? AND c.id_resposta IS NULL
    ORDER BY c.data_comentario ASC";
                        $stmt = $conn->prepare($sql_com);
                        $stmt->bind_param("i", $exp['id_experiencia']);
                        $stmt->execute();
                        $comentarios = $stmt->get_result();
                        $comentarios_array = [];
                        while ($coment = $comentarios->fetch_assoc()) {
                            $comentarios_array[] = $coment;
                        }
                        $stmt->close();

                        for ($i = 0; $i < count($comentarios_array); $i++) { ?>
                            <div class="comentario" <?= $i >= 3 ? 'style="display:none;"' : '' ?>>
                                <div class="comentario-inner" style="padding-left:0;">
                                    <div class="usuario-info">
                                        <img src="<?= htmlspecialchars($comentarios_array[$i]['foto'] ?? 'uploads/default.png') ?>"
                                            class="foto-perfil-coment" alt="Foto de perfil do usuário">
                                        <div>
                                            <strong><?= htmlspecialchars($comentarios_array[$i]['nome']) ?></strong>
                                            <span
                                                class="username">@<?= htmlspecialchars($comentarios_array[$i]['username']) ?></span>
                                        </div>
                                    </div>
                                    <p><?= htmlspecialchars($comentarios_array[$i]['comentario']) ?></p>
                                    <form action="comentarExperiencia.php" method="post" class="form-comentar">
                                        <input type="hidden" name="id_experiencia" value="<?= $exp['id_experiencia'] ?>">
                                        <input type="hidden" name="id_resposta"
                                            value="<?= $comentarios_array[$i]['id_comentario'] ?>">
                                        <textarea name="comentario" placeholder="Responder..." required></textarea>
                                        <button type="submit" class="comentario-btn">Responder</button>
                                    </form>
                                    <?php
                                    $ja_iluminou_coment = false;
                                    if (isset($_SESSION['id_usuario'])) {
                                        $stmt_check = $conn->prepare("SELECT 1 FROM aully_iluminacoes_comentario WHERE id_usuario = ? AND id_comentario = ?");
                                        $stmt_check->bind_param("ii", $_SESSION['id_usuario'], $comentarios_array[$i]['id_comentario']);
                                        $stmt_check->execute();
                                        $stmt_check->store_result();
                                        $ja_iluminou_coment = $stmt_check->num_rows > 0;
                                        $stmt_check->close();

                                        $stmtIlum = $conn->prepare("SELECT COUNT(*) as total FROM aully_iluminacoes_comentario WHERE id_comentario = ?");
                                        $stmtIlum->bind_param("i", $comentarios_array[$i]['id_comentario']);
                                        $stmtIlum->execute();
                                        $resIlum = $stmtIlum->get_result()->fetch_assoc();
                                        $stmtIlum->close();
                                    }
                                    ?>
                                    <form action="iluminarComentario.php" method="post" style="display:inline;">
                                        <input type="hidden" name="id_comentario"
                                            value="<?= $comentarios_array[$i]['id_comentario'] ?>">
                                        <button type="submit" class="sparkle-btn<?= $ja_iluminou_coment ? ' active' : '' ?>"
                                            title="Iluminar comentário">
                                            <svg class="sparkle-icon" width="28" height="28" viewBox="0 0 28 28" fill="none">
                                                <polygon
                                                    points="14,2 17.5,10.5 27,11 19,17 21.5,26 14,21 6.5,26 9,17 1,11 10.5,10.5"
                                                    stroke="#fff" stroke-width="2" fill="none" />
                                            </svg>
                                        </button>
                                        <span
                                            style="color:#ffca3a; font-size:1em; margin-left:2px;"><?= $resIlum['total'] ?? 0 ?></span>
                                    </form>
                                    <?php exibirComentarios($conn, $exp['id_experiencia'], $comentarios_array[$i]['id_comentario'], 1); ?>
                                </div>
                            </div>
                        <?php } ?>
                    <?php endif; // fim do if ($liberado) ?>
                </div> <!-- fim do .post -->
            <?php } // fim do while ($exp = $experiencias->fetch_assoc()) ?>
        </section>

        <div id="modal-editar-post" class="modal-editar-bg" style="display:none;">
            <div class="modal-editar-box">
                <span class="modal-editar-close"
                    style="cursor:pointer;font-size:2em;position:absolute;top:10px;right:20px;">&times;</span>
                <h2>Editar Postagem</h2>
                <form id="form-editar-post">
                    <input type="hidden" name="id_experiencia" id="editar-id-experiencia">
                    <input type="text" name="titulo" id="editar-titulo" class="titulo" required maxlength="80"
                        placeholder="Título">
                    <select name="tipo" id="editar-tipo" class="tipo">
                        <option value="sensorial">Sensorial</option>
                        <option value="emocional">Emocional</option>
                        <option value="corporal">Corporal</option>
                        <option value="cognitivo">Cognitivo</option>
                        <option value="social">Social</option>
                    </select>
                    <textarea name="descricao" id="editar-descricao" class="descreva" required maxlength="1000"
                        placeholder="Descreva a sua experiência"></textarea>
                    <button type="submit" class="submit-btn">Salvar Alterações</button>
                </form>
                <div id="editar-msg" style="color:green;display:none;margin-top:10px;">Post atualizado!</div>
            </div>
        </div>

    </main>



    <script>
        const LIMITE_ARQUIVOS = 4;
        let arquivosSelecionados = [];

        document.getElementById('midia-input').addEventListener('change', function (e) {
            const files = Array.from(e.target.files);

            arquivosSelecionados = arquivosSelecionados.concat(files);

            arquivosSelecionados = arquivosSelecionados.filter((file, index, self) =>
                index === self.findIndex(f => f.name === file.name && f.size === file.size)
            );

            if (arquivosSelecionados.length > LIMITE_ARQUIVOS) {
                alert(`Você pode anexar no máximo ${LIMITE_ARQUIVOS} arquivos.`);
                arquivosSelecionados = arquivosSelecionados.slice(0, LIMITE_ARQUIVOS);
            }

            atualizarPrevia();
        });

        function atualizarPrevia() {
            const preview = document.getElementById('preview-midia');
            const limite = document.getElementById('limite-arquivos');
            preview.innerHTML = '';

            arquivosSelecionados.forEach((file, idx) => {
                const url = URL.createObjectURL(file);
                let element = '';
                if (file.type.startsWith('image/')) {
                    element = `<img src="${url}" class="preview-img" onclick="abrirModal('${url}', 'img')">`;
                } else if (file.type.startsWith('video/')) {
                    element = `<video controls class="preview-video" onclick="abrirModal('${url}', 'video')"><source src="${url}"></video>`;
                }
                preview.innerHTML += `
        <div class="preview-item">
            <span class="remove-btn" onclick="removerArquivo(${idx})">&times;</span>
            ${element}
        </div>
    `;
            });

            limite.textContent = `${arquivosSelecionados.length}/${LIMITE_ARQUIVOS} arquivos anexados`;

            atualizarInputFile();
        }

        function removerArquivo(idx) {
            arquivosSelecionados.splice(idx, 1);
            atualizarPrevia();
        }

        function atualizarInputFile() {
            const dataTransfer = new DataTransfer();
            arquivosSelecionados.forEach(file => dataTransfer.items.add(file));
            document.getElementById('midia-input').files = dataTransfer.files;
        }

        function abrirModal(url, tipo) {
            const modal = document.getElementById('modal-midia');
            const content = document.getElementById('modal-midia-content');
            if (tipo === 'img') {
                content.innerHTML = `<img src="${url}" style="max-width:90vw;max-height:80vh;">`;
            } else if (tipo === 'video') {
                content.innerHTML = `<video controls autoplay style="max-width:90vw;max-height:80vh;"><source src="${url}"></video>`;
            }
            modal.classList.add('active');
        }

        let btnTopo;
//voltar ao topo
        document.addEventListener('DOMContentLoaded', function () {
            btnTopo = document.getElementById('btn-topo');
            window.addEventListener('scroll', function () {
                btnTopo.style.display = window.scrollY > 200 ? 'block' : 'none';
                if (window.scrollY > 200) {
                    btnTopo.classList.add('mostrar');
                } else {
                    btnTopo.classList.remove('mostrar');
                }
            });
            btnTopo.onclick = function () {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            };
            document.getElementById('modal-midia-close').onclick = function () {
                document.getElementById('modal-midia').classList.remove('active');
            };
            document.getElementById('modal-midia').onclick = function (e) {
                if (e.target === this) this.classList.remove('active');
            };
        });

        function toggleComentarios(id_experiencia, btn) {
            const postDiv = btn.closest('.post');
            const comentarios = postDiv.querySelectorAll('.comentario');
            const mostrandoMais = btn.textContent.trim() === "Ver mais";

            comentarios.forEach((coment, idx) => {
                if (idx >= 3) {
                    coment.style.display = mostrandoMais ? "block" : "none";
                }
            });

            btn.textContent = mostrandoMais ? "Ver menos" : "Ver mais";
        }


        function atualizarPost(id) {
            $.get('getPost.php', { id_experiencia: id }, function (data) {
                // Cria um container temporário para manipular o conteúdo
                const temp = document.createElement('div');
                temp.innerHTML = data;

                // Procura pelo novo post com data-id correspondente
                const novoPost = temp.querySelector('.post[data-id="' + id + '"]');
                const postAtual = document.querySelector('.post[data-id="' + id + '"]');

                if (novoPost && postAtual) {
                    postAtual.replaceWith(novoPost);
                } else {
                    console.error("Post não encontrado para substituir.");
                }
            }).fail(function () {
                console.error("Erro ao carregar o post atualizado.");
            });
        }

        $(function () {

            $(document).on('submit', '.form-comentar', function (e) {
                e.preventDefault();
                const form = $(this);
                const btn = form.find('button[type="submit"]');
                btn.prop('disabled', true);
                const postDiv = form.closest('.post');
                const id = postDiv.data('id');
                $.post(form.attr('action'), form.serialize(), function () {
                    form.find('textarea[name="comentario"]').val('');
                    btn.prop('disabled', false);
                    if (id) {
                        atualizarPost(id);
                    } else {
                        location.reload();
                    }
                }).fail(function () {
                    btn.prop('disabled', false);
                });
            });
        });
        <?php
        $ja_iluminou_coment = false;
        if (isset($_SESSION['id_usuario'])) {
            $stmt_check = $conn->prepare("SELECT 1 FROM aully_iluminacoes_comentario WHERE id_usuario = ? AND id_comentario = ?");
            $stmt_check->bind_param("ii", $_SESSION['id_usuario'], $coment['id_comentario']);
            $stmt_check->execute();
            $stmt_check->store_result();
            $ja_iluminou_coment = $stmt_check->num_rows > 0;
            $stmt_check->close();

            ?>

            $(document).on('submit', '.form-iluminar', function (e) {
                e.preventDefault();
                const form = $(this);
                const postDiv = form.closest('.post');
                const id = postDiv.data('id');
                const btn = form.find('.sparkle-btn');
                $.post(form.attr('action'), form.serialize(), function (resp) {
                    try {
                        const data = JSON.parse(resp);
                        postDiv.find('.post-meta span').first().html('💖 ' + data.total + ' iluminações');
                        if (data.action === 'added') {
                            btn.addClass('active');
                        } else {
                            btn.removeClass('active');
                        }
                    } catch (e) {
                        atualizarPost(id);
                    }
                });
            });

            // AJAX para iluminar comentário
            $(document).on('submit', 'form[action$="iluminarComentario.php"]', function (e) {
                e.preventDefault();
                const form = $(this);
                const btn = form.find('.sparkle-btn');
                const span = form.find('span');
                $.post(form.attr('action'), form.serialize(), function (resp) {
                    try {
                        const data = JSON.parse(resp);
                        span.text(data.total ?? 0);
                        if (data.action === 'added') {
                            btn.addClass('active');
                        } else {
                            btn.removeClass('active');
                        }
                    } catch (e) {
                        location.reload();
                    }
                });
            });
            <?php
        }
        ?>


        // Abrir/fechar menu de opções
        $(document).on('click', '.options-btn', function (e) {
            e.stopPropagation();
            $('.options-menu').hide();
            $(this).siblings('.options-menu').toggle();
        });

        $(document).on('click', function () {
            $('.options-menu').hide();
        });

        // AJAX para excluir post
        $(document).on('click', '.excluir-btn', function (e) {
            e.preventDefault();
            if (confirm('Deseja realmente excluir esta postagem? Esta ação é irreversível!')) {
                const postDiv = $(this).closest('.post');
                const id = postDiv.data('id');
                $.get('excluirExperiencia.php?id=' + id, function (resp) {
                    // Remove só o post excluído
                    postDiv.remove();
                });
            }
        });

        // Abrir modal denúncia
        $(document).on('click', '.denunciar-btn', function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            $('#denuncia-id-exp').val(id);
            $('#denuncia-motivo').val('');
            $('#denuncia-msg').hide();
            $('#modal-denuncia').addClass('active');
            $('.options-menu').hide();
        });

        // Fechar modal denúncia
        // Fecha ao clicar no X
        $('.modal-denuncia-close').on('click', function () {
            $('#modal-denuncia').removeClass('active');
        });
        // Fecha ao clicar no fundo
        $('#modal-denuncia').on('click', function (e) {
            if (e.target === this) $(this).removeClass('active');
        });
        $('.modal-denuncia-box').on('click', function (e) {
            e.stopPropagation();
        });

        // Enviar denúncia via AJAX
        $('#form-denuncia').on('submit', function (e) {
            e.preventDefault();
            $.post('denunciar.php', $(this).serialize(), function () {
                $('#denuncia-msg').show();
                setTimeout(function () {
                    $('#modal-denuncia').removeClass('active');
                }, 1200);
            });
        });

        // Abrir modal de edição
        $(document).on('click', '.editar-btn', function () {
            const id = $(this).data('id');
            // Busca os dados do post via AJAX
            $.get('getPost.php', { id_experiencia: id }, function (data) {
                // Supondo que getPost.php retorna HTML do post, extraia os dados:
                let post = $($.parseHTML($.trim(data))).filter('.post[data-id="' + id + '"]');
                if (!post.length) {
                    post = $($.trim(data));
                }
                $('#editar-id-experiencia').val(id);
                $('#editar-titulo').val(post.find('h3').text().trim());
                $('#editar-tipo').val(post.find('.tipo-experiencia').text().trim().toLowerCase());
                $('#editar-descricao').val(post.find('.descricao-exp').html().trim());
                $('#modal-editar-post').addClass('active').show();
            });
        });

        // Fechar modal
        $('.modal-editar-close, #modal-editar-post').on('click', function (e) {
            if (e.target === this) $('#modal-editar-post').removeClass('active').hide();
        });
        $('.modal-editar-box').on('click', function (e) {
            e.stopPropagation();
        });

        // Enviar edição via AJAX
        $('#form-editar-post').on('submit', function (e) {
            e.preventDefault();
            $.post('editarExperiencia.php', $(this).serialize(), function (resp) {
                console.log("Resposta da edição:", resp); // Log da resposta do servidor
                $('#editar-msg').show();
                setTimeout(function () {
                    $('#modal-editar-post').removeClass('active').hide();
                    $('#editar-msg').hide();
                    // Atualiza o post na tela
                    const id = $('#editar-id-experiencia').val();
                    if (id) {
                        atualizarPost(id);
                    }
                }, 1200);
            }).fail(function (jqXHR, textStatus, errorThrown) {
                console.error("Erro na edição:", textStatus, errorThrown); // Log de erro
            });
        });

        let tempoInativo = 0;

        // Função para resetar o tempo ao detectar atividade
        function resetarTempo() {
            tempoInativo = 0;
        }

        // Escuta vários tipos de atividade do usuário
        ['mousemove', 'keypress', 'click', 'scroll', 'touchstart'].forEach(evt => {
            document.addEventListener(evt, resetarTempo);
        });

        // Checa inatividade a cada 1 minuto
        setInterval(() => {
            tempoInativo++;
            if (tempoInativo >= 20) { // 20 minutos de inatividade
                // Redireciona para logout (já registra no banco via logout.php)
                location.href = 'logout.php';
            }
        }, 60000);

    </script>

    <button id="btn-topo" title="Voltar ao topo">&#8679;</button>

    <div id="modal-midia">
        <span id="modal-midia-close">&times;</span>
        <div id="modal-midia-content"></div>
    </div>

    <!-- Modal de denúncia -->
    <div id="modal-denuncia" class="modal-denuncia-bg">
        <div class="modal-denuncia-box">
            <span class="modal-denuncia-close">&times;</span>
            <h2>Denunciar postagem</h2>
            <form id="form-denuncia">
                <input type="hidden" name="id_experiencia" id="denuncia-id-exp">
                <label for="denuncia-motivo">Motivo:</label>
                <textarea name="motivo" id="denuncia-motivo" required placeholder="Descreva o motivo..."
                    maxlength="255"></textarea>
                <button type="submit">Enviar denúncia</button>
            </form>
            <div id="denuncia-msg" style="display:none;color:green;margin-top:10px;">Denúncia enviada!</div>
        </div>
    </div>

</body>

</html>