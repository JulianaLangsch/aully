<?php
session_start();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Aully</title>
    <link rel="icon" type="image/png" href="./imgStyle/favicon.png">
</head>

<body>
    <div class="logo__aully__infinito">
        <img src="./imgStyle/logo.png" alt="Mãos em um formato de infinito com as cores do arco íris">
        <div class="aully">Aully</div>
        <div class="autism">Autism</div>
        <div class="ally">Ally</div>
    </div>
    <header>
        <div class="convite__e__estrelas">
            <div class="estrela__esquerda">
                <img src="./imgStyle/estrela__direita.png" alt="Uma estrela amarela">
            </div>
            <h1 class="convite">Nenhuma experiência é única, compartilhe a sua!</h1>
            <div class="estrela__direita">
                <img src="./imgStyle/estrela__direita.png" alt="Uma estrela amarela">
            </div>
        </div>
    </header>
    <main>
        <div class="botoes">
            <a class="entrar" href="login.php">Entrar</a>
            <a class="cadastro" href="cadastro.php">Cadastre-se</a>
        </div>

        <div class="beneficios__site">
            <div class="acolhimento">
                <img src="./imgStyle/maos.png" alt="Duas pessoas brancas de mãos dadas">
                <h1>ACOLHIMENTO E ESTRATÉGIAS DE ENFRENTAMENTO</h1>
                <h1>Troca de experiências, sensação de <br>pertencimento e maior união da comunidade</h1>
            </div>
            <div class="pesquisa">
                <img src="./imgStyle/mapeamento.png" alt="Lupa e relatórios de análise">
                <h1>PESQUISA E IDENTIFICAÇÃO DE TENDÊNCIAS</h1>
                <h1>Mapeamento sensorial,<br> emocional e comportamental</h1>
            </div>
        </div>
        </div>
    </main>
    <footer>
        <div class="criadora">
            <h1>Criado por uma pessoa autista, para a comunidade autista. Feito por Juliana Langsch.
                julianalangschdasilva@gmail.com</h1>
    </footer>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Calistoga&family=Exo+2:ital,wght@0,100..900;1,100..900&family=Pacifico&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Spicy+Rice&display=swap');

        :root {
            --fonte-principal: "Spicy Rice", serif;
            --fonte-secundaria: "Calistoga", serif;
        }

        body {
            width: 98%;
        }

        head {
            background-color: white;
        }

        /*     LOGO: SIMBOLO DO INFINITO + AULLY, AUTISM ALLY     */
        /*      ALINHA O TEXTO PARA DENTRO DO LOGO   */

        .logo__aully__infinito {
            position: relative;
            display: flex;
            justify-content: center;
        }

        .logo__aully__infinito img {
            width: 23%;
            height: auto;
        }

        .aully,
        .autism,
        .ally {
            position: absolute;
            color: purple;
            display: flex;
            justify-content: center;
            font-family: var(--fonte-principal);
        }

        .aully {
            left: 44%;
            bottom: 40%;
            transform: translateX(-44%);
            font-size: 2.5vw;
        }

        .autism {
            bottom: 45%;
            left: 56%;
            transform: translateX(-57%);
            font-size: 1.8vw;
        }

        .ally {
            bottom: 30%;
            left: 56%;
            transform: translateX(-57%);
            font-size: 2vw;
        }

        /*     NENHUMA EXPERIÊNCIA É ÚNICA COMPARTILHE A SUA   */
        .convite {
            display: flex;
            text-align: center;
            background: linear-gradient(to right, red, orange, yellow, green, blue, indigo, violet);
            margin: 10px 2px;
            font-family: "Pacifico", cursive;
            font-weight: bold;
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;

        }

        .convite__e__estrelas {
            display: flex;
            justify-content: center;
            align-items: center;


        }

        .estrela__esquerda img {
            transform: scaleX(-1);
            /*inverte a imagem*/
            margin: 0;
            padding: 0;
        }


        /*      AJUSTE DOS BOTÕES, PONTEIRO, CORES, TAMANHO E BORDAS   */
        .botoes {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 5px 10px;
            font-size: 1rem;
            gap: 2.5rem;
            width: 100%;
        }

        .entrar {
            display: flex;
            justify-content: center;
            background: linear-gradient(to right, #FF5757, #CB6CE6);
            width: 10vw;
            height: 50px;
            color: white;
            font-size: large;
            cursor: pointer;
            border-radius: 18px;
            border: none;
            align-items: center;
            font-family: var(--fonte-secundaria);
            text-decoration: none;

        }


        .cadastro {
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(to right, #FFDE59, #FF3131);
            width: 15vw;
            height: 50px;
            color: white;
            font-size: large;
            cursor: pointer;
            border-radius: 18px;
            border: none;
            align-items: center;
            font-family: var(--fonte-secundaria);
            text-decoration: none;

        }

        /*    RESUMO QUE MOSTRA OS BENEFÍCIOS DO SITE */

        .beneficios__site {
            display: flex;
            justify-content: space-between;
            text-align: center;
            font-family: var(--fonte-secundaria);
            font-size: 0.5rem;
            padding: 10px;


        }

        .acolhimento {
            color: #A34BA3;
        }

        .pesquisa {
            color: #328AB0;
        }

        /* FRASE QUE ESCLARECE QUEM É A CRIADORA, E SEU EMAIL DE CONTATO, E QUEM SÃO OS USUÁRIOS ALVO.   */

        .criadora {
            font-size: 50%;
            display: flex;
            justify-content: center;
            color: orange;
            padding: 10px;
            flex-direction: column;
            align-items: center;
            text-align: center;
            font-family: "Playfair Display", serif;
        }

        .criadora h1 {
            margin: 5px 0;
        }

        /*    SMARTPHONES MENORES*/
        @media (max-width: 683px) {
            .logo__aully__infinito img {
                width: 60%;
            }

            .logo__aully__infinito {
                display: flex;
                justify-content: center;
                align-items: center;
            }

            .aully {
                font-size: 6vw;
                left: 35%;
            }

            .autism {
                font-size: 4vw;
                left: 65%;
                bottom: 45%;
            }

            .ally {
                font-size: 4vw;
                left: 65%;
                bottom: 30%;
            }

            .convite {
                font-size: 4vw;
            }

            .estrela__esquerda img,
            .estrela__direita img {
                width: 2.4rem;
            }

            .botoes {
                width: 100%;
                text-align: center;

            }

            .entrar,
            .cadastro {
                display: flex;
                justify-content: center;
                align-items: center;
                width: 20vw;
                height: 6vh;
                font-size: 3vw;
            }

            .beneficios__site {
                display: flex;
                flex-direction: column;
                font-size: 1.5vw;
                justify-content: center;
                align-items: center;
                gap: 1cm;
            }

            .acolhimento img,
            .pesquisa img {
                width: 4.38rem;
            }

            .acolhimento,
            .pesquisa {
                flex-grow: 1;
            }

            .criadora {
                display: flex;
                font-size: 1.5vw;
                justify-content: center;
            }

        }

        /*    SMARTPHONES MAIORES  */
        @media (min-width: 684px) and (max-width: 768px) {

            .logo__aully__infinito img {
                width: 60%;
            }

            .aully {
                font-size: 4vw;
                left: 35%;
                flex-grow: 1;
            }

            .autism {
                font-size: 4vw;
                left: 65%;
                bottom: 45%;
                flex-grow: 1;
            }

            .ally {
                font-size: 4vw;
                left: 65%;
                bottom: 30%;
                flex-grow: 1;
            }

            .convite {
                font-size: 4vw;
                flex-grow: 1;
            }

            .estrela__esquerda img,
            .estrela__direita img {
                width: 2.4rem;
            }

            .botoes {
                width: 100%;
            }

            .entrar,
            .cadastro {
                width: 20vw;
                height: 7vh;
                font-size: 3vw;
            }

            .beneficios__site {
                display: flex;
                font-size: 7px;
                justify-content: center;
                align-items: center;
                gap: 1cm;
            }

            .acolhimento img,
            .pesquisa img {
                width: 70px;
            }


            .criadora {
                display: flex;
                font-size: 8px;
                justify-content: center;
            }
        }

        /*          TABLET           */
        @media screen and (min-width: 769px) and (max-width: 1023px) {
            .logo__aully__infinito img {
                width: 45%;
            }

            .aully {
                font-size: 4vw;
                left: 40%;
                flex-grow: 1;
            }

            .autism {
                font-size: 3.5vw;
                left: 62%;
                bottom: 45%;
                flex-grow: 1;
            }

            .ally {
                font-size: 3.5vw;
                left: 62%;
                bottom: 30%;
                flex-grow: 1;
            }

            .botoes {
                width: 100%;
            }

            .entrar,
            .cadastro {
                width: 20vw;
                font-size: 2.5vw;
            }
        }
    </style>
</body>
</div>

</html>