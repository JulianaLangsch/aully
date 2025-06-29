<?php
session_start();
include_once("conexao.php");

if (!isset($_SESSION['id_usuario'])) {
  die("Usuário não autenticado.");
}

$id_usuario = $_SESSION['id_usuario'];

// Buscar quantidade de cada tipo e calcular o total
$tipos = ['sensorial', 'emocional', 'corporal', 'cognitivo', 'social'];
$dados = [];
$total_tipos = 0;
$quantidades = [];

foreach ($tipos as $tipo) {
  $stmt = $conn->prepare("SELECT quantidade FROM aully_experienciastotal WHERE tipo = ? AND id_usuario = ?");
  $stmt->bind_param("si", $tipo, $id_usuario);
  $stmt->execute();
  $result = $stmt->get_result()->fetch_assoc();
  $qtd = $result ? (int) $result['quantidade'] : 0;
  $quantidades[$tipo] = $qtd;
  $total_tipos += $qtd;
  $stmt->close();
}

// Calcule a porcentagem de cada tipo em relação ao total dos tipos
$dados = [];
foreach ($tipos as $tipo) {
  $porcentagem = ($total_tipos > 0) ? round(($quantidades[$tipo] / $total_tipos) * 100) : 0;
  $dados[] = [
    'label' => ucfirst($tipo),
    'value' => $porcentagem,
    'quantidade' => $quantidades[$tipo]
  ];
}

// Buscar últimas 5 experiências
$stmt_exp = $conn->prepare("SELECT titulo, descricao FROM aully_experiencia WHERE id_usuario = ? AND ativa = 1 ORDER BY id_experiencia DESC LIMIT 5");
$stmt_exp->bind_param("i", $id_usuario);
$stmt_exp->execute();
$res_exp = $stmt_exp->get_result();
$ultimas = [];
while ($row = $res_exp->fetch_assoc()) {
  $ultimas[] = [
    'titulo' => $row['titulo'],
    'descricao' => $row['descricao']
  ];
}
$stmt_exp->close();

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="./imgStyle/favicon.png">
  <title>Aully - Rastreamento</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Calistoga&family=Spicy+Rice&display=swap');

    :root {
      --fonte-principal: "Spicy Rice", serif;
      --fonte-secundaria: "Calistoga", serif;
    }

    .bar-chart {
      display: flex;
      justify-content: space-between;
      gap: 10px;
      width: 100%;
      max-width: 500px;
      height: 250px;
      border: none;
      padding: 10px;
      box-sizing: border-box;
    }

    .bar {
      display: block;
      width: 100%;
      background-color: #8ac926;
      border-radius: 4px 4px 0 0;
      transition: all 0.3s;
      min-height: 3px;
      border: 1px solid #4f772d;
      border-radius: 10px;
    }

    .bar-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      width: 20%;
    }

    .label {
      margin-bottom: 5px;
      font-size: 0.9rem;
      color: #4f772d;
      text-align: center;
    }

    .bar:hover {
      background-color: #4f772d;
    }

    .value {
      margin-top: 5px;
      font-size: 0.8rem;
      color: #4f772d;
    }

    h1,
    h2 {
      color: #4f772d;
      font-family: var(--fonte-principal);
    }

    h1 {
      width: 40%;
    }

    body {
      display: flex;
      flex-direction: column;
      background: linear-gradient(to bottom, white, #8ac926);
      background-repeat: no-repeat;
    }

    footer {
      bottom: 0;
      display: flex;
      gap: 20px;
      padding: 20px;
      justify-content: center;
    }

    .compartilhar,
    .salvar {
      cursor: pointer;
      color: white;
      padding: 10px;
      background-color: #4f772d;
      border: none;
      border-radius: 20px;
      margin-bottom: 10px;
      font-family: var(--fonte-secundaria);
    }

    main {
      display: flex;
      flex-direction: row;
      height: 100vh;
      font-family: var(--fonte-secundaria);
      gap: 24px;
    }

    .bar-chart {
      flex: 2 1 0%;
      max-width: 600px;
      min-width: 320px;
    }

    .barraLateral {
      flex: 1 1 0%;
      min-width: 180px;
      background-color: #dde5b6;
      text-align: center;
      border-radius: 20px;
      padding: 15px;
      margin-left: 0;
      min-height: 500px;
      /* Altura mínima para 5 cartões confortáveis */
      box-sizing: border-box;
    }

    .btn-voltar-inicio {
      display: block;
      background: #8ac926;
      color: #fff;
      padding: 10px 28px;
      border-radius: 20px;
      text-decoration: none;
      font-family: var(--fonte-secundaria, 'Calistoga', serif);
      font-size: 1.1em;
      margin: 24px 0 10px 24px;
      transition: background 0.2s, color 0.2s;
      box-shadow: 0 2px 8px rgba(106, 76, 147, 0.10);
      border: none;
      width: fit-content;
      font-weight: bold;
    }

    .btn-voltar-inicio:hover {
      background: #4f772d;
      ;
      box-shadow: 0 4px 16px rgba(255, 202, 58, 0.18);
    }

    .ultimasExperiencias {
      display: flex;
      flex-direction: column;
      gap: 18px;
      background: transparent;
      margin: 20px 0;
      padding: 0;
      border-radius: 0;
    }

    .experiencia-card {
      background: #8ac926;
      border-radius: 24px;
      padding: 16px 18px;
      color: #fff;
      margin: 0;
      box-shadow: 0 2px 8px rgba(106, 76, 147, 0.08);
      transition: background 0.2s, box-shadow 0.2s, color 0.2s;
      cursor: pointer;
    }

    .experiencia-card:hover {
      background: #4f772d;
      ;
      box-shadow: 0 4px 16px rgba(255, 202, 58, 0.18);
    }

    .experiencia-card h3 {
      margin: 0 0 6px 0;
      font-size: 1.1em;
      color: inherit;
      font-family: var(--fonte-secundaria, 'Calistoga', serif);
    }

    .experiencia-card p {
      margin: 0;
      font-size: 1em;
      color: inherit;
      font-family: var(--fonte-secundaria, 'Calistoga', serif);
    }

    h3 {
      text-decoration: none;
    }

    main {
      height: 100vh;
      font-family: var(--fonte-secundaria);
    }

    @media (min-width: 768px) {

      main {
        display: flex;
      }

      .barraLateral {
        margin-left: 20px;
        height: 50%;
      }

      .compartilhar,
      .salvar {
        padding: 20px;
        border-radius: 30px;
        font-size: 18px;
      }
    }

    @media (min-width: 800px) {
      .barraLateral {
        width: 100%;
      }
    }

    .bar-container .quantidade {
      font-size: 0.85em;
      color: #8ac926;
      margin-bottom: 4px;
      display: block;
    }
  </style>
</head>

<body>
  <a href="paginaPrincipal.php" class="btn-voltar-inicio">← Voltar ao início</a>
  <header>
    <h1>Rastreamento</h1>
  </header>
  <main>
    <section class="bar-chart"></section>

    <section class="barraLateral">
      <h2>Últimas experiências</h2>
      <div class="ultimasExperiencias">
        <?php foreach ($ultimas as $exp): ?>
          <div class="experiencia-card">
            <h3><?= htmlspecialchars($exp['titulo']) ?></h3>
            <p><?= htmlspecialchars($exp['descricao']) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  </main>

  <script>
    //o array dados com as informações das experiências é convertido para JSON e utilizado para construir dinamicamente o gráfico de barras, criando os elementos HTML para cada tipo, mostrando o nome, a quantidade e a barra proporcional à porcentagem calculada.
    const data = <?= json_encode($dados); ?>;

    const chart = document.querySelector('.bar-chart');

    chart.innerHTML = data
      .map(
        ({ label, value, quantidade }) => `
            <div class="bar-container">
                <span class="label">${label}</span>
                <span class="quantidade">${quantidade} experiência${quantidade === 1 ? '' : 's'}</span>
                <div class="bar" style="height: ${value}%"></div>
                <span class="value">${value}%</span>
            </div>
        `
      )
      .join('');
  </script>