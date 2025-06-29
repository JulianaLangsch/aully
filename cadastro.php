<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="./imgStyle/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">
    <title>Aully</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Calistoga&family=Exo+2:ital,wght@0,100..900;1,100..900&family=Pacifico&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Spicy+Rice&display=swap');

        :root {
            --fonte-principal: "Spicy Rice", serif;
            --fonte-secundaria: "Calistoga", serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            background: linear-gradient(to top, #e69212, white);
            height: 100vh;
            width: 100vw;
            margin: 0;
            padding: 0;
        }

        .cadastro-container {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            border-radius: 15px;
            width: 75vw;
            box-sizing: border-box;
        }

        .cadastro-container input {
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 15px;
            margin: 10px 0;
            padding: 10px;
            font-size: 3.5vw;
            text-align: center;
        }

        .cadastro_dados {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-flow: column;
        }

        .state {
            padding: 10px;
            border-radius: 15px;
            width: 10vw;
            text-align: center;
            margin-right: 10px;
            height: 3vw;
        }

        .cadastro-container button {
            align-self: center;
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 15px;
            background: #e36414;
            font-size: 3.5vw;
            cursor: pointer;
            color: white;
            margin-top: 20px;
            font-family: var(--fonte-secundaria);
        }

        .cadastro-container button:hover {
            background: rgb(252, 127, 10);
        }

        .idade {
            width: 10vw;
            height: 1vw;
        }

        .options {
            display: flex;
        }

        @media (min-width: 320px) {
            .idade {
                width: 35vw;
            }

            .state {
                width: 35vw;
            }

        }

        @media (min-width: 500px) {
            /* body{
        height: 86vh;
        width: 100%;
    } */

            .cadastro-container {
                width: 310px;
            }

            .cadastro-container input {
                font-size: 20px;
            }

            .cadastro-container button {
                font-size: 20px;
            }
        }


        @media (min-width: 500px) {
            .foto-perfil label {
                font-size: 16px;
            }

            .foto-perfil input[type="file"] {
                font-size: 16px;
            }
        }

        .file-label-perfil {
            display: inline-block;
            padding: 12px 28px;
            background: #e36414;
            color: #fff;
            border-radius: 24px;
            cursor: pointer;
            font-family: 'Calistoga', serif;
            font-size: 18px;
            margin-bottom: 18px;
            transition: background 0.2s;
        }

        .file-label-perfil:hover {
            background: rgb(252, 127, 10);
        }

        .input-file-perfil {
            display: none;
        }
    </style>
</head>

<body>
    <main>
        <?php
        session_start();

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        if (isset($_SESSION['msg'])) {
            echo "<div class='mensagem'>" . $_SESSION['msg'] . "</div>";
            unset($_SESSION['msg']);
        }
        ?>

        <div class="cadastro-container">
            //O formulário HTML é enviado por método POST para o arquivo validaCadastro.php, que será responsável por validar e processar os dados. O atributo enctype="multipart/form-data" permite o envio de arquivos, o que neste caso é usado para a foto de perfil. O campo de imagem está escondido visualmente, sendo ativado por um botão estilizado com a classe file-label-perfil, que muda o texto dinamicamente ao detectar a seleção de um arquivo (feito com JavaScript, ao final da página).
            <form class="cadastro_dados" method="POST" action="validaCadastro.php" enctype="multipart/form-data">
                <label class="file-label-perfil" for="foto-perfil-input">
                    <span id="texto-label-foto">Escolha sua foto de perfil</span>
                    <input type="file" name="foto" id="foto-perfil-input" class="input-file-perfil" accept="image/*">
                </label>
                <input type="text" name="nome" placeholder="Como devemos te chamar?" required>
                <input type="text" name="username" placeholder="Escolha um nome de usuário" required>
                <input type="email" name="email" placeholder="Endereço de e-mail" required>

                <div class="options">
                    <select id="state" name="state" class="state" aria-label="Estado" required>
                        <option value="" disabled selected>Selecione seu estado</option>
                        <option value="AC">Acre (AC)</option>
                        <option value="AL">Alagoas (AL)</option>
                        <option value="AP">Amapá (AP)</option>
                        <option value="AM">Amazonas (AM)</option>
                        <option value="BA">Bahia (BA)</option>
                        <option value="CE">Ceará (CE)</option>
                        <option value="DF">Distrito Federal (DF)</option>
                        <option value="ES">Espírito Santo (ES)</option>
                        <option value="GO">Goiás (GO)</option>
                        <option value="MA">Maranhão (MA)</option>
                        <option value="MT">Mato Grosso (MT)</option>
                        <option value="MS">Mato Grosso do Sul (MS)</option>
                        <option value="MG">Minas Gerais (MG)</option>
                        <option value="PA">Pará (PA)</option>
                        <option value="PB">Paraíba (PB)</option>
                        <option value="PR">Paraná (PR)</option>
                        <option value="PE">Pernambuco (PE)</option>
                        <option value="PI">Piauí (PI)</option>
                        <option value="RJ">Rio de Janeiro (RJ)</option>
                        <option value="RN">Rio Grande do Norte (RN)</option>
                        <option value="RS">Rio Grande do Sul (RS)</option>
                        <option value="RO">Rondônia (RO)</option>
                        <option value="RR">Roraima (RR)</option>
                        <option value="SC">Santa Catarina (SC)</option>
                        <option value="SP">São Paulo (SP)</option>
                        <option value="SE">Sergipe (SE)</option>
                        <option value="TO">Tocantins (TO)</option>
                    </select>

                    <input type="date" name="data_nasc" class="idade">

                </div>

                <input type="password" name="password" placeholder="Escolha sua senha" required>
                <input type="password" name="confirm_password" placeholder="Repita a senha" required>
                <button type="submit">Criar conta</button>
            </form>
        </div>
    </main>
    <script>
        document.getElementById('foto-perfil-input').addEventListener('change', function () {
            const texto = document.getElementById('texto-label-foto');
            if (this.files && this.files.length > 0) {
                texto.textContent = "Foto selecionada";
            } else {
                texto.textContent = "Escolha sua foto de perfil";
            }
        });
    </script>
</body>

</html>