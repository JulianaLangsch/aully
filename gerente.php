<?php
session_start();
include('conexao.php');

// Verifica se está logado e se é gerente (ajuste o valor conforme seu sistema, ex: 2 para gerente)
if (!isset($_SESSION['id_usuario']) || $_SESSION['nivel_acesso'] != 2) {
    header('Location: login.php');
    exit();
}

if (!$conn) {
    die('Erro na conexão com o banco de dados.');
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aully - Painel do Gerente</title>
    <link rel="icon" type="image/png" href="./imgStyle/favicon.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* Use o mesmo CSS do administrador.php para manter o visual */
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
        .admin-header nav a {
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
        .admin-header nav a:hover {
            background: #ffca3a;
            color: #fff;
        }
        .admin-main {
            max-width: 1250px;
            margin: 30px auto 0 auto;
            padding: 0 16px 32px 16px;
        }
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }
        .section-header h2 {
            margin: 0;
            color: #6a4c93;
            font-size: 1.4em;
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
            table-layout: fixed;
        }
        th, td {
            padding: 8px 6px;
            border-bottom: 1px solid #eee;
            text-align: center;
            word-break: break-word;
            max-width: 120px;
        }
        th {
            background: #6a4c93;
            color: #fff;
            font-size: 1em;
        }
        tr:last-child td {
            border-bottom: none;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        @media (max-width: 800px) {
            .admin-main {
                padding: 0 4px 24px 4px;
            }
            table, th, td {
                font-size: 0.95em;
            }
            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
        }
        .sair-btn {
            display: inline-block;
            padding: 10px 24px;
            background: #ff595e;
            color: #fff;
            border-radius: 20px;
            text-decoration: none;
            font-family: 'Calistoga', serif;
            font-size: 18px;
            margin-bottom: 18px;
            transition: background 0.2s;
        }
        .sair-btn:hover {
            background: #6a4c93;
        }
    </style>
</head>

<body>
    <header class="admin-header">
        <h1>Painel do Gerente - Aully</h1>
        <nav>
            <a href="logout.php" class="sair-btn">Sair</a>
        </nav>
    </header>

    <main class="admin-main">
        <section class="user-management">
            <div class="section-header">
                <h2>Usuários da Rede</h2>
            </div>

            <?php
            // Consulta igual ao do administrador
            $sql = "SELECT 
                        u.id_usuario, 
                        p.nome_pessoa,
                        u.username,
                        u.email_usuario,
                        p.estado,
                        u.data_nasc,
                        (SELECT COUNT(*) FROM aully_experiencia e WHERE e.id_usuario = u.id_usuario) AS experienciasPostadas,
                        u.nivel_acesso,
                        u.usuario_ativo
                    FROM aully_usuario u
                    JOIN aully_pessoa p ON u.id_pessoa = p.id_pessoa";
            $result = $conn->query($sql);
            ?>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th>Data de Nascimento</th>
                        <th>Experiências</th>
                        <th>Nível de Acesso</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $data_formatada = '';
                            if (!empty($row['data_nasc']) && $row['data_nasc'] !== '0000-00-00') {
                                $data_formatada = date('d/m/Y', strtotime($row['data_nasc']));
                            }

                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($row['id_usuario']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['nome_pessoa']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['username']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['email_usuario']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['estado']) . '</td>';
                            echo '<td>' . $data_formatada . '</td>';
                            echo '<td>' . htmlspecialchars($row['experienciasPostadas']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['nivel_acesso']) . '</td>';
                            echo '<td>' . ($row['usuario_ativo'] == 1 ? 'Ativo' : 'Inativo') . '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="9">Nenhum usuário encontrado.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>