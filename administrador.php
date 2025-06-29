<?php
session_start();
include('conexao.php');

//A primeira parte do código inicia a sessão com session_start() e inclui o arquivo conexao.php, que contém os dados de conexão com o banco de dados. Em seguida, é feita uma verificação de segurança para garantir que apenas usuários logados e com nível de acesso igual a 1 (ou seja, administradores) possam acessar essa página. Caso contrário, o usuário é redirecionado para a página de login.

// Verifica se está logado e se é administrador
if (!isset($_SESSION['id_usuario']) || $_SESSION['nivel_acesso'] != 1) {
    header('Location: login.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aully - Administração</title>
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

        .section-header a {
            background: #8ac926;
            color: #fff;
            padding: 7px 16px;
            border-radius: 18px;
            text-decoration: none;
            margin-left: 8px;
            font-size: 1em;
            transition: background 0.2s;
        }

        .section-header a:hover {
            background: #1982c4;
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
            /* Adicionado para controlar largura */
        }

        th,
        td {
            padding: 8px 6px;
            /* Reduzido */
            border-bottom: 1px solid #eee;
            text-align: center;
            word-break: break-word;
            /* Permite quebra de linha */
            max-width: 120px;
            /* Limita largura das células */
        }

        input[type="text"],
        input[type="email"],
        select {
            max-width: 100px;
            width: 90%;
            font-size: 0.98em;
            padding: 3px 4px;
            box-sizing: border-box;
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

        .user-management,
        .denuncias {
            margin-bottom: 36px;
        }

        .denuncias h2 {
            color: #ff595e;
            font-size: 1.2em;
            margin-bottom: 10px;
        }

        .denuncias p {
            background: #fff3cd;
            color: #856404;
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 8px;
            font-size: 1em;
        }

        .denuncias a {
            color: #1982c4;
            text-decoration: underline;
            margin-left: 8px;
        }

        .denuncias a:hover {
            color: #6a4c93;
        }

        .editar-btn,
        .inativar-btn,
        .ativar-btn {
            background: #6a4c93;
            color: #fff;
            border: none;
            border-radius: 16px;
            padding: 6px 14px;
            margin: 2px 2px;
            font-size: 0.98em;
            cursor: pointer;
            transition: background 0.2s;
        }

        .editar-btn:hover {
            background: #1982c4;
        }

        .salvar-btn {
            background: #ffca3a;
            color: #fff;
            border: none;
            border-radius: 16px;
            padding: 6px 14px;
            margin: 2px 2px;
            font-size: 0.98em;
            cursor: pointer;
            transition: background 0.2s;
            font-weight: bold;
        }

        .salvar-btn:hover {
            background: #6a4c93;
            color: #fff;
        }

        .inativar-btn {
            background: #ff595e;
        }

        .inativar-btn:hover {
            background: #c43d3d;
        }

        .ativar-btn {
            background: #8ac926;
        }

        .ativar-btn:hover {
            background: #1982c4;
        }

        @media (max-width: 800px) {
            .admin-main {
                padding: 0 4px 24px 4px;
            }

            table,
            th,
            td {
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

        /* Controle sutil das larguras das colunas da tabela de usuários */
        table th:nth-child(1),
        /* ID */
        table td:nth-child(1) {
            width: 60px;
            /* ID: pequeno */
            min-width: 40px;
            max-width: 70px;
        }

        table th:nth-child(4),
        /* Email */
        table td:nth-child(4) {
            width: 130px;
            min-width: 60px;
            max-width: 150px;
        }

        table th:nth-child(5),
        /* Estado */
        table td:nth-child(5) {
            width: 60px;
            /* Estado: pequeno */
            min-width: 50px;
            max-width: 90px;
        }

        table th:nth-child(8),
        /* Nível de Acesso */
        table td:nth-child(8) {
            width: 95px;
            /* Nível: pequeno */
            min-width: 60px;
            max-width: 100px;
        }

        table th:nth-child(9),
        /* Status */
        table td:nth-child(9) {
            width: 60px;
            /* Status: pequeno */
            min-width: 50px;
            max-width: 100px;
        }

        table th:nth-child(7),
        /* Experiências */
        table td:nth-child(7) {
            width: 100px;
            /* Experiências: um pouco maior */
            min-width: 90px;
            max-width: 140px;
        }
    </style>
</head>

<body>
    <header class="admin-header">
        <h1>Painel de Administração - Aully</h1>
        <nav>
            <a href="logout.php" class="sair-btn">Sair</a>
            <a href="relatorio.php" class="relatorio-btn"><i class="fas fa-chart-bar"></i> Relatório de Acessos</a>
            <a href="denuncias.php" class="relatorio-btn"><i class="fas fa-flag"></i> Denúncias</a>
        </nav>
    </header>

    <main class="admin-main">
        <section class="user-management">
            <div class="section-header">
                <h2>Gerenciamento de Usuários</h2>
                <div>
                    <a href="cadastrarUsuario.php">Cadastrar Usuário</a>
                    <a href="cadastrarPessoa.php">Cadastrar Pessoa</a>
                </div>
            </div>

            <?php
            //realiza uma consulta SQL que junta os dados da tabela aully_usuario com os dados da tabela aully_pessoa, utilizando um JOIN. Essa consulta retorna informações como o nome da pessoa, username, email, estado, data de nascimento, número de experiências postadas (calculado com uma subquery), nível de acesso e se o usuário está ativo ou não. Os resultados dessa consulta são armazenados na variável $result e utilizados para gerar dinamicamente a tabela HTML que exibe os dados dos usuários.

            // Consulta com subquery para contar experiências reais
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
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="userList">
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $data_formatada = '';
                            if (!empty($row['data_nasc']) && $row['data_nasc'] !== '0000-00-00') {
                                $data_formatada = date('d/m/Y', strtotime($row['data_nasc']));
                            }

//A função htmlspecialchars() serve para evitar que códigos maliciosos (como scripts) sejam executados quando inseridos em campos como formulários. Ela converte caracteres especiais em entidades HTML. Isso é fundamental para proteger contra ataques como XSS (Cross-site Scripting).
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
                            echo '<td>';
                            echo '<button class="editar-btn" data-id="' . $row['id_usuario'] . '"><i class="fas fa-edit"></i> Editar</button> ';

                            if ($row['usuario_ativo'] == 1) {
                                echo '<button class="inativar-btn" data-id="' . $row['id_usuario'] . '"><i class="fas fa-user-slash"></i> Inativar</button>';
                            } else {
                                echo '<button class="ativar-btn" data-id="' . $row['id_usuario'] . '"><i class="fas fa-user-check"></i> Ativar</button>';
                            }

                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="10">Nenhum usuário encontrado.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </section>

    </main>

    <script>
        //Os botões de ação são gerenciados com JavaScript, usando fetch e event listeners. Quando o botão “Editar” é clicado, a linha da tabela se transforma em campos de formulário (input ou select), permitindo modificar os dados diretamente. O botão também muda para “Salvar”. Ao clicar em “Salvar”, o JavaScript coleta os dados dos inputs e envia uma requisição assíncrona via fetch para o arquivo editarUsuario.php

        document.addEventListener('DOMContentLoaded', () => {
            const userList = document.getElementById('userList');

            userList.addEventListener('click', async (e) => {
                const btn = e.target.closest('button');
                if (!btn) return;

                const row = btn.closest('tr');
                const id = btn.dataset.id;
                const cells = row.querySelectorAll('td');

                // SALVAR
                if (btn.classList.contains('salvar-btn')) {
                    const inputs = row.querySelectorAll('input, select');
                    const dados = {};
                    inputs.forEach(input => {
                        dados[input.name] = input.value;
                    });
                    dados.id_usuario = id;

                    try {
                        const res = await fetch('editarUsuario.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(dados)
                        });

                        const resposta = await res.text();

                        if (resposta.trim() === 'ok') {
                            cells[1].innerText = dados.nome;
                            cells[2].innerText = dados.username; // Adicionado
                            cells[3].innerText = dados.email;
                            cells[4].innerText = dados.estado;
                            cells[5].innerText = dados.data_nasc;
                            cells[7].innerText = dados.nivel_acesso;

                            btn.innerHTML = '<i class="fas fa-edit"></i> Editar';
                            btn.classList.remove('salvar-btn');
                            btn.classList.add('editar-btn');
                        } else {
                            alert('Erro ao salvar: ' + resposta);
                        }
                    } catch (err) {
                        alert('Erro de conexão');
                        console.error(err);
                    }
                }

                // EDITAR
                else if (btn.classList.contains('editar-btn')) {
                    const nome = cells[1].innerText;
                    const username = cells[2].innerText; // Adicionado
                    const email = cells[3].innerText;
                    const estado = cells[4].innerText;
                    const data_nasc_br = cells[5].innerText;
                    const nivel = cells[7].innerText;

                    cells[1].innerHTML = `<input type="text" name="nome" value="${nome}">`;
                    cells[2].innerHTML = `<input type="text" name="username" value="${username}">`;
                    cells[3].innerHTML = `<input type="email" name="email" value="${email}">`;
                    cells[4].innerHTML = `<input type="text" name="estado" value="${estado}">`;
                    cells[5].innerHTML = `<input type="text" name="data_nasc" value="${data_nasc_br}" placeholder="dd/mm/aaaa">`;
                    cells[7].innerHTML = `
    <select name="nivel_acesso">
        <option value="1" ${nivel === '1' ? 'selected' : ''}>Administrador</option>
        <option value="2" ${nivel === '2' ? 'selected' : ''}>Gerente</option>
        <option value="3" ${nivel === '3' ? 'selected' : ''}>Usuário</option>
    </select>
`;

                    btn.innerHTML = '<i class="fas fa-save"></i> Salvar';
                    btn.classList.remove('editar-btn');
                    btn.classList.add('salvar-btn');
                }

                //os botões de ativar e inativar também funcionam por meio de requisições assíncronas, enviando o ID do usuário para o arquivo status.php, que altera o status no banco de dados e retorna a nova situação da conta, atualizando o botão e o texto exibido na tabela automaticamente.
                // ATIVAR / INATIVAR
                else if (btn.classList.contains('inativar-btn') || btn.classList.contains('ativar-btn')) {
                    if (confirm('Tem certeza que deseja alterar o status deste usuário?')) {
                        try {
                            const res = await fetch(`status.php?id=${id}`);
                            const resposta = await res.text();

                            if (resposta.trim() === '1') {
                                cells[8].innerText = 'Ativo';
                                btn.innerHTML = '<i class="fas fa-user-slash"></i> Inativar';
                                btn.classList.remove('ativar-btn');
                                btn.classList.add('inativar-btn');
                            } else if (resposta.trim() === '0') {
                                cells[8].innerText = 'Inativo';
                                btn.innerHTML = '<i class="fas fa-user-check"></i> Ativar';
                                btn.classList.remove('inativar-btn');
                                btn.classList.add('ativar-btn');
                            } else {
                                alert('Erro: ' + resposta);
                            }
                        } catch (err) {
                            alert('Erro ao alterar status.');
                            console.error(err);
                        }
                    }
                }
            });
        });
    </script>

</body>

</html>