<?php
//começa definindo o tipo de conteúdo da resposta como text/html com charset UTF-8, já que ele retornará HTML puro. 
header("Content-Type: text/html; charset=UTF-8");
include_once("conexao.php");
session_start();

//getPost.php é responsável por montar dinamicamente a estrutura HTML completa de uma experiência, incluindo autor, conteúdo, mídias, opções de ação e todos os comentários e respostas associados. O objetivo é permitir que esse conteúdo seja carregado de forma assíncrona via AJAX, mantendo a página principal leve e interativa.


// Função para exibir comentários e respostas
function exibirComentarios($conn, $id_experiencia, $id_pai = NULL, $nivel = 0)
{
    if ($nivel >= 3)
        return;

    $sql = "SELECT c.*, u.username, u.nome, u.foto FROM aully_comentarios c 
            JOIN aully_usuario u ON c.id_usuario = u.id_usuario 
            WHERE c.id_experiencia = ? AND " . ($id_pai ? "c.id_resposta = ?" : "c.id_resposta IS NULL") . " 
            ORDER BY c.data_comentario ASC";

    $stmt = $conn->prepare($sql);
    if ($id_pai) {
        $stmt->bind_param("ii", $id_experiencia, $id_pai);
    } else {
        $stmt->bind_param("i", $id_experiencia);
    }
    $stmt->execute();
    $comentarios = $stmt->get_result();

    $count = 0;
    while ($coment = $comentarios->fetch_assoc()):
        $exibir = $nivel === 0 && $count >= 3 ? 'style="display:none;"' : '';
        ?>
        <div class="comentario" <?= $exibir ?> style="margin-left: <?= $nivel * 20 ?>px;">
            <div class="usuario-info">
                <img src="<?= htmlspecialchars($coment['foto'] ?? 'uploads/default.png') ?>" class="foto-perfil-coment">
                <strong><?= htmlspecialchars($coment['nome']) ?></strong>
                <div class="username">@<?= htmlspecialchars($coment['username']) ?></div>
            </div>
            <p><?= htmlspecialchars($coment['comentario']) ?></p>

            <form action="comentarExperiencia.php" method="post" class="form-comentar">
                <input type="hidden" name="id_experiencia" value="<?= $id_experiencia ?>">
                <input type="hidden" name="id_resposta" value="<?= $coment['id_comentario'] ?>">
                <textarea name="comentario" placeholder="Responder..." required></textarea>
                <button type="submit">Responder</button>
            </form>

            <?php
            $ja_iluminou_coment = false;
            if (isset($_SESSION['id_usuario'])) {
                $stmt_check = $conn->prepare("SELECT 1 FROM aully_iluminacoes_comentario WHERE id_usuario = ? AND id_comentario = ?");
                $stmt_check->bind_param("ii", $_SESSION['id_usuario'], $coment['id_comentario']);
                $stmt_check->execute();
                $stmt_check->store_result();
                $ja_iluminou_coment = $stmt_check->num_rows > 0;
                $stmt_check->close();
            }
            ?>
            <form action="iluminarComentario.php" method="post" style="display:inline;">
                <input type="hidden" name="id_comentario" value="<?= $coment['id_comentario'] ?>">
                <button type="submit" class="sparkle-btn<?= $ja_iluminou_coment ? ' active' : '' ?>" title="Iluminar postagem">
                    <svg class="sparkle-icon" width="28" height="28" viewBox="0 0 28 28" fill="none">
                        <polygon points="14,2 17.5,10.5 27,11 19,17 21.5,26 14,21 6.5,26 9,17 1,11 10.5,10.5" stroke="#fff"
                            stroke-width="2" fill="none" />
                    </svg>
                </button>
                <?php
                $stmtIlum = $conn->prepare("SELECT COUNT(*) as total FROM aully_iluminacoes_comentario WHERE id_comentario = ?");
                $stmtIlum->bind_param("i", $coment['id_comentario']);
                $stmtIlum->execute();
                $resIlum = $stmtIlum->get_result()->fetch_assoc();
                $stmtIlum->close();
                ?>
                <span style="color:#ffca3a; font-size:1em; margin-left:2px;"><?= $resIlum['total'] ?? 0 ?></span>
            </form>

            <?php exibirComentarios($conn, $id_experiencia, $coment['id_comentario'], $nivel + 1); ?>
        </div>
        <?php
        $count++;
    endwhile;
    $stmt->close();

    if ($nivel === 0 && $count > 3): ?>
        <button class="toggle-comentarios" onclick="toggleComentarios(<?= $id_experiencia ?>, this)">Ver mais</button>
    <?php endif;
}

// Obtém ID
$id_experiencia = intval($_GET['id_experiencia'] ?? 0);
if (!$id_experiencia)
    exit;

// Busca experiência
$stmt = $conn->prepare("SELECT e.*, u.username, u.nome, u.foto FROM aully_experiencia e 
JOIN aully_usuario u ON e.id_usuario = u.id_usuario 
WHERE e.id_experiencia = ?");
$stmt->bind_param("i", $id_experiencia);
$stmt->execute();
$exp = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$exp)
    exit;

// Conta iluminações
$stmt_ilum = $conn->prepare("SELECT COUNT(*) as total FROM aully_iluminacoes WHERE id_experiencia = ?");
$stmt_ilum->bind_param("i", $exp['id_experiencia']);
$stmt_ilum->execute();
$res_ilum = $stmt_ilum->get_result()->fetch_assoc();
$stmt_ilum->close();

// Conta comentários
$stmt_com = $conn->prepare("SELECT COUNT(*) as total FROM aully_comentarios WHERE id_experiencia = ?");
$stmt_com->bind_param("i", $exp['id_experiencia']);
$stmt_com->execute();
$res_com = $stmt_com->get_result()->fetch_assoc();
$stmt_com->close();

// BUFFER HTML
//ob_start() e ob_get_clean(), permitem capturar toda a saída do script como uma string e enviá-la de uma só vez como resposta.
ob_start();
?>

<div class="post" data-id="<?= $exp['id_experiencia'] ?>">
    <div class="usuario-info">
        <img src="<?= htmlspecialchars($exp['foto'] ?? 'uploads/default.png') ?>" class="foto-perfil-post">
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
            <button type="button" class="denunciar-btn" data-id="<?= $exp['id_experiencia'] ?>">Denunciar</button>
            <?php if (isset($_SESSION['id_usuario']) && $_SESSION['id_usuario'] == $exp['id_usuario']): ?>
                <button type="button" class="editar-btn" data-id="<?= $exp['id_experiencia'] ?>">Editar</button>
                <button type="button" class="excluir-btn" data-id="<?= $exp['id_experiencia'] ?>">Excluir</button>
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
                    break;
                $ext = strtolower(pathinfo($midia, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                    echo '<img src="' . htmlspecialchars($midia) . '" class="midia-img" onclick="abrirModal(\'' . htmlspecialchars($midia) . '\', \'img\')">';
                } elseif (in_array($ext, ['mp4', 'avi', 'mov'])) {
                    echo '<video controls class="midia-video" onclick="abrirModal(\'' . htmlspecialchars($midia) . '\', \'video\')"><source src="' . htmlspecialchars($midia) . '"></video>';
                }
                $count++;
            }
            echo '</div>';
        }
    }
    ?>

    <div class="post-meta">
        <span>💖 <?= $res_ilum['total'] ?> iluminações</span> |
        <span>💬 <?= $res_com['total'] ?> comentários</span>
    </div>

    <?php exibirComentarios($conn, $exp['id_experiencia']); ?>
</div>

<?php
$html = ob_get_clean();
echo trim($html);
exit;
?>