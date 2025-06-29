<?php
session_start();
include_once "conexao.php";

// Verifica se o usuário é administrador (nível 1)
if (!isset($_SESSION['nivel_acesso']) || $_SESSION['nivel_acesso'] != '1') {
    header("Location: login.php");
    exit();
}

//realiza uma consulta SQL para buscar os últimos 30 registros de login armazenados na tabela aully_log_acesso. Essa consulta junta a tabela de acessos com a tabela de usuários (aully_usuario), usando o JOIN para obter o nome do usuário (username) associado a cada registro de acesso. Os dados retornados incluem o identificador do log, o nome do usuário, a data e hora do login, e a data e hora do logout, ordenados do mais recente para o mais antigo.

// Consulta os últimos 30 acessos
$sql = "SELECT l.id_log, u.username, l.data_login, l.data_logout
        FROM aully_log_acesso l
        JOIN aully_usuario u ON l.id_usuario = u.id_usuario
        ORDER BY l.data_login DESC
        LIMIT 30";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Relatório de Acessos - Aully</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="./imgStyle/favicon.png">

    <style>
        body {
            font-family: 'Calistoga', Arial, sans-serif;
            background: #f3f3f3;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            color: #6a4c93;
            margin-top: 30px;
        }

        table {
            width: 90%;
            margin: 30px auto;
            border-collapse: collapse;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(106, 76, 147, 0.08);
            overflow: hidden;
        }

        th,
        td {
            padding: 14px 10px;
            border-bottom: 1px solid #eee;
            text-align: center;
        }

        th {
            background: #6a4c93;
            color: #fff;
            font-size: 1.1em;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        .ativo {
            color: #8ac926;
            font-weight: bold;
        }

        .logout {
            color: #ff595e;
        }

        a {
            display: block;
            text-align: center;
            margin: 30px auto 0 auto;
            color: #6a4c93;
            text-decoration: none;
            font-size: 1.1em;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <h1>Relatório de Acessos</h1>
    <table>
        <thead>
            <tr>
                <th>Usuário</th>
                <th>Data/Hora Login</th>
                <th>Data/Hora Logout</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['data_login']) ?></td>
                        <td>
                            <?php if ($row['data_logout']): ?>
                                <span class="logout"><?= htmlspecialchars($row['data_logout']) ?></span>
                            <?php else: ?>
                                <span class="ativo">Ativo</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">Nenhum acesso encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <a href="administrador.php">← Voltar para o painel</a>
</body>

</html>