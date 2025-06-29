<?php
include_once("conexao.php");
session_start();

//O arquivo carregarMaisPosts.php é responsável por carregar dinamicamente mais experiências

//tem a função de percorrer e exibir todos os comentários associados a uma experiência, incluindo respostas em até três níveis de profundidade. Essa função também trata a lógica de exibição do botão "iluminar" em comentários.
function exibirComentarios($conn, $id_experiencia, $id_pai = NULL, $nivel = 0)
{
    if ($nivel >= 3)
        return;

    $sql = "SELECT c.*, u.username, u.foto FROM aully_comentarios c 
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

    while ($coment = $comentarios->fetch_assoc()):
        ?>
        <div class="comentario" style="margin-left: <?= $nivel * 20 ?>px;">
            <div class="usuario-info">
                <img src="<?= htmlspecialchars($coment['foto'] ?? 'uploads/default.png') ?>" class="foto-perfil-coment">
                <div>
                    <strong><?= htmlspecialchars($coment['username']) ?></strong>
                    <span class="username">@<?= htmlspecialchars($coment['username']) ?></span>
                </div>
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
            </form>
            <?php exibirComentarios($conn, $id_experiencia, $coment['id_comentario'], $nivel + 1); ?>
        </div>
        <?php
    endwhile;
    $stmt->close();
}


//$limite = 5;
//$pagina = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
//$offset = ($pagina - 1) * $limite;

$stmt = $conn->prepare("SELECT e.*, u.username, u.nome, u.foto FROM aully_experiencia e JOIN aully_usuario u ON e.id_usuario = u.id_usuario WHERE e.ativa = 1 ORDER BY e.id_experiencia");
//$stmt->bind_param("ii");
//$stmt->execute();
//$experiencias = $stmt->get_result();

while ($exp = $experiencias->fetch_assoc()):
    ?>
    <div class="post" data-id="<?= $exp['id_experiencia'] ?>">
        <div class="usuario-info">
            <img src="<?= htmlspecialchars($exp['foto'] ?? 'uploads/default.png') ?>" class="foto-perfil-post">
            <div>
                <strong><?= htmlspecialchars($exp['username']) ?></strong>
                <div class="username">@<?= htmlspecialchars($exp['username']) ?></div>
            </div>
        </div>
        <div class="post-options">
            <button class="options-btn">⋮</button>
            <div class="options-menu">
                <button type="button" class="denunciar-btn" data-id="<?= $exp['id_experiencia'] ?>">Denunciar</button>
                <?php if ($_SESSION['id_usuario'] == $exp['id_usuario']): ?>
                    <a href="editarExperiencia.php?id=<?= $exp['id_experiencia'] ?>">Editar</a>
                    <button class="excluir-btn" data-id="<?= $exp['id_experiencia'] ?>">Excluir</button>
                <?php endif; ?>
            </div>
        </div>
        <h3><?= htmlspecialchars($exp['titulo']) ?></h3>
        <p><?= htmlspecialchars($exp['descricao']) ?></p>

        <?php
        // filepath: c:\xampp\htdocs\inf8b\trabalho\back\paginaPrincipal.php
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

        <a href="votar.php?id_experiencia=<?= $exp['id_experiencia'] ?>" class="vote-btn">Votar</a>

//O botão de “Votar” direciona para a página votar.php, e o botão de “Iluminar” realiza uma requisição POST para iluminarExperiencia.php. Há um controle visual para saber se o usuário já iluminou aquela postagem, destacando o botão com a classe active.

        <form action="iluminarExperiencia.php" method="post" class="form-iluminar">
            <input type="hidden" name="id_experiencia" value="<?= $exp['id_experiencia'] ?>">
            <button type="submit" class="sparkle-btn<?= $ja_iluminou ? ' active' : '' ?>" title="Iluminar postagem">
                <svg class="sparkle-icon" width="28" height="28" viewBox="0 0 28 28" fill="none">
                    <polygon points="14,2 17.5,10.5 27,11 19,17 21.5,26 14,21 6.5,26 9,17 1,11 10.5,10.5" stroke="#fff"
                        stroke-width="2" fill="none" />
                </svg>
            </button>
        </form>

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

//carrega até 3 comentários principais da experiência, e para cada um, exibe a foto, nome, comentário, botão para responder e também botão para iluminar o comentário. O número de iluminações de cada comentário é mostrado ao lado do botão. Também é chamada a função exibirComentarios, que exibe recursivamente as respostas de cada comentário.

// comentários principais (não-respostas)
        $sql_com = "SELECT c.*, u.username, u.foto FROM aully_comentarios c 
    JOIN aully_usuario u ON c.id_usuario = u.id_usuario
    WHERE c.id_experiencia = ? AND c.id_resposta IS NULL
    ORDER BY c.data_comentario ASC";

        $stmt = $conn->prepare($sql_com);
        $stmt->bind_param("i", $exp['id_experiencia']);
        $stmt->execute();
        $comentarios = $stmt->get_result();

        $total_com = $comentarios->num_rows;
        $comentarios_array = [];
        while ($coment = $comentarios->fetch_assoc()) {
            $comentarios_array[] = $coment;
        }
        $stmt->close();
        ?>

        <?php for ($i = 0; $i < count($comentarios_array); $i++): ?>
            <div class="comentario" <?= $i >= 3 ? 'style="display:none;"' : '' ?>>
                <div class="usuario-info">
                    <img src="<?= htmlspecialchars($comentarios_array[$i]['foto'] ?? 'uploads/default.png') ?>"
                        class="foto-perfil-coment">
                    <div>
                        <strong><?= htmlspecialchars($comentarios_array[$i]['username']) ?></strong>
                        <span class="username">@<?= htmlspecialchars($comentarios_array[$i]['username']) ?></span>
                    </div>
                </div>
                <p><?= htmlspecialchars($comentarios_array[$i]['comentario']) ?></p>
                <form action="comentarExperiencia.php" method="post" class="form-comentar">
                    <input type="hidden" name="id_experiencia" value="<?= $exp['id_experiencia'] ?>">
                    <input type="hidden" name="id_resposta" value="<?= $comentarios_array[$i]['id_comentario'] ?>">
                    <textarea name="comentario" placeholder="Responder..." required></textarea>
                    <button type="submit">Responder</button>
                </form>

                <form action="iluminarComentario.php" method="post" style="display:inline;">
                    <input type="hidden" name="id_comentario" value="<?= $comentarios_array[$i]['id_comentario'] ?>">
                    <button type="submit" class="sparkle-btn<?= $ja_iluminou ? ' active' : '' ?>" title="Iluminar postagem">
                        <svg class="sparkle-icon" width="28" height="28" viewBox="0 0 28 28" fill="none">
                            <polygon points="14,2 17.5,10.5 27,11 19,17 21.5,26 14,21 6.5,26 9,17 1,11 10.5,10.5" stroke="#fff"
                                stroke-width="2" fill="none" />
                        </svg>
                    </button> <?php
                    $stmtIlum = $conn->prepare("SELECT COUNT(*) as total FROM aully_iluminacoes WHERE id_comentario = ?");
                    $stmtIlum->bind_param("i", $comentarios_array[$i]['id_comentario']);
                    $stmtIlum->execute();
                    $resIlum = $stmtIlum->get_result()->fetch_assoc();
                    $stmtIlum->close();
                    ?>
                    <span style="color:#ffca3a; font-size:1em; margin-left:2px;"><?= $resIlum['total'] ?? 0 ?></span>
                </form>
                <?php exibirComentarios($conn, $exp['id_experiencia'], $comentarios_array[$i]['id_comentario'], 1); ?>
            </div>
        <?php endfor; ?>

        //se houver mais de 3 comentários principais, é exibido um botão "Ver mais", que pode ser usado por JavaScript para mostrar os demais comentários ocultos.
        <?php if ($total_com > 3): ?>
            <button class="toggle-comentarios" onclick="toggleComentarios(<?= $exp['id_experiencia'] ?>, this)">Ver
                mais</button>
        <?php endif; ?>
    </div>
<?php endwhile; ?>