<?php
session_start();
include_once("conexao.php");

// Apenas administradores podem acessar
if (!isset($_SESSION['nivel_acesso']) || $_SESSION['nivel_acesso'] != '1') {
    header("Location: login.php");
    exit();
}

//$status_filtro = $_GET['status'] ?? '';
//$where = '';
//if ($status_filtro === 'pendente') {
//   $where = "WHERE d.status = 'pendente'";
//} elseif ($status_filtro === 'aprovada') {
 //   $where = "WHERE d.status = 'aprovada'";
//} elseif ($status_filtro === 'reprovada') {
 //   $where = "WHERE d.status = 'reprovada'";
//}


//busca todas as denúncias na tabela aully_denuncias, juntando informações relevantes das tabelas aully_usuario (para obter o nome do usuário que fez a denúncia) e aully_experiencia (para obter o título da experiência denunciada). Essa junção é feita com LEFT JOINs para garantir que, mesmo que algum campo relacionado não exista, a denúncia seja listada. Os resultados são ordenados primeiramente pelo status da denúncia em ordem crescente (para priorizar pendentes) e depois pelo ID da denúncia em ordem decrescente (mais recentes primeiro).

$res = $conn->query("SELECT d.*, u.username, e.titulo 
    FROM aully_denuncias d
    LEFT JOIN aully_usuario u ON d.id_usuario = u.id_usuario
    LEFT JOIN aully_experiencia e ON d.id_experiencia = e.id_experiencia
    $where
    ORDER BY d.status ASC, d.id_denuncia DESC");
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Denúncias - Aully</title>
    <link rel="icon" type="image/png" href="./imgStyle/favicon.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f3f3f3;
            font-family: 'Calistoga', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .admin-header {
            background: #6a4c93;
            color: #fff;
            padding: 24px 0 16px 0;
            text-align: center;
            margin-bottom: 0;
        }

        .admin-header h1 {
            margin: 0 0 10px 0;
            font-size: 2.2em;
            letter-spacing: 1px;
        }

        .admin-header nav {
            margin-top: 10px;
        }

        .admin-header nav a,
        .relatorio-btn {
            display: inline-block;
            background: #fff;
            color: #6a4c93;
            text-decoration: none;
            padding: 8px 18px;
            border-radius: 24px;
            margin: 0 6px 6px 0;
            font-size: 1em;
            font-weight: bold;
            border: none;
            transition: background 0.2s, color 0.2s;
            cursor: pointer;
        }

        .admin-header nav a:hover,
        .relatorio-btn:hover {
            background: #ffca3a;
            color: #fff;
        }

        .denuncias-main {
            max-width: 900px;
            margin: 30px auto 0 auto;
            padding: 0 16px 32px 16px;
        }

        h2 {
            color: #ff595e;
            font-size: 1.4em;
            margin-bottom: 18px;
            letter-spacing: 1px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(106, 76, 147, 0.08);
            overflow: hidden;
            margin-bottom: 32px;
        }

        th,
        td {
            padding: 12px 8px;
            border-bottom: 1px solid #eee;
            text-align: center;
        }

        th {
            background: #6a4c93;
            color: #fff;
            font-size: 1.05em;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        .status-pendente {
            color: #ff595e;
            font-weight: bold;
        }

        .status-revisada {
            color: #8ac926;
            font-weight: bold;
        }

        .btn-revisar {
            background: #1982c4;
            color: #fff;
            border: none;
            border-radius: 16px;
            padding: 6px 14px;
            font-size: 0.98em;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
        }

        .btn-revisar:hover {
            background: #6a4c93;
        }

        @media (max-width: 800px) {
            .denuncias-main {
                padding: 0 4px 24px 4px;
            }

            table,
            th,
            td {
                font-size: 0.95em;
            }
        }

        .status-aprovado {
            color: #219653;
            background: #eafaf1;
            border-radius: 8px;
            padding: 4px 14px;
            font-weight: bold;
        }

        .status-reprovado {
            color: #c0392b;
            background: #fdeaea;
            border-radius: 8px;
            padding: 4px 14px;
            font-weight: bold;
        }

        .status-pendente {
            color: #b8860b;
            background: #fffbe6;
            border-radius: 8px;
            padding: 4px 14px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <header class="admin-header">
        <h1>Denúncias - Aully</h1>
        <nav>
            <a href="administrador.php"><i class="fas fa-arrow-left"></i> Voltar ao painel</a>
        </nav>
    </header>
    <main class="denuncias-main">
        <h2>Lista de Denúncias</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuário</th>
                    <th>Experiência</th>
                    <th>Motivo</th>
                    <th>Status</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                //Para garantir segurança contra ataques XSS, os dados exibidos são passados pela função htmlspecialchars, convertendo caracteres especiais em entidades HTML.
                <?php if ($res && $res->num_rows > 0): ?>
                    <?php while ($d = $res->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($d['id_denuncia']) ?></td>
                            <td><?= htmlspecialchars($d['username']) ?></td>
                            <td><?= htmlspecialchars($d['titulo']) ?: '-' ?></td>
                            <td><?= htmlspecialchars($d['motivo']) ?></td>
                            <td>
                                <?php if ($d['status'] == 'pendente'): ?>
                                    <span class="status-pendente">Pendente</span>
                                <?php elseif ($d['status'] == 'aprovado'): ?>
                                    <span class="status-aprovado">Aprovado</span>
                                <?php elseif ($d['status'] == 'reprovado'): ?>
                                    <span class="status-reprovado">Reprovado</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($d['status'] == 'pendente'): ?>
                                    <a href="revisarDenuncia.php?id=<?= $d['id_denuncia'] ?>" class="btn-revisar">Revisar</a>
                                <?php else: ?>
                                    <span class="btn-revisado" style="color: #888; cursor: not-allowed;">Revisado</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">Nenhuma denúncia encontrada.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>

</html>