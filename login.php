<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Aully - Login</title>
    <link rel="icon" type="image/png" href="./imgStyle/favicon.png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Calistoga&family=Exo+2:ital,wght@0,100..900;1,100..900&family=Pacifico&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Spicy+Rice&display=swap');

        :root {
            --fonte-principal: "Spicy Rice", serif;
            --fonte-secundaria: "Calistoga", serif;
        }

        body {
            margin: 0;
            padding: 0;
        }

        main {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            background: linear-gradient(to top, indigo, white);
            height: 100vh;
            width: 100vw;
            margin: 0;
            padding: 0;
        }

        .aully {
            font-family: var(--fonte-principal);
        }

        .logo__aully__infinito {
            display: flex;
            justify-content: center;
            align-items: center;
            color: purple;
            flex-wrap: nowrap;
            padding-top: 20px;
            gap: 0.80mm;
            font-size: 4.5vw;
        }

        .logo__aully__infinito img {
            width: 10vw;
        }

        .login-container {
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 0 10px rgb(240, 236, 236);
            width: 60vw;
            box-sizing: border-box;
            background: rgba(255, 255, 255, 0.2);
            /* Fundo translúcido para melhor visualização */
            max-width: 500px;
        }

        .login-container input {
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 15px;
            margin: 10px 0;
            padding: 10px;
            font-size: 3.5vw;
            text-align: center;
        }

        .login_dados {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-flow: column
        }

        .login-container button {
            align-self: center;
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 15px;
            background: #975ab6;
            font-size: 3.5vw;
            cursor: pointer;
            color: white;
            margin-top: 20px;
            font-family: var(--fonte-secundaria);
        }

        .login-container button:hover {
            background: #512260;
        }

        .forgot-password {
            display: block;
            text-align: center;
            margin-top: 10px;
            margin-top: 15px;
        }

        .forgot-password a {
            color: white;
            text-decoration: none;
            font-size: 3.5vw;
        }

        @media (min-width: 500px) {
            main {
                height: 86vh;
                width: 100%;
            }

            .logo__aully__infinito {
                font-size: 35px;
            }

            .login-container {
                width: 310px;
            }

            .login-container input {
                font-size: 20px;
            }

            .login-container button {
                font-size: 20px;
            }

            .forgot-password a {
                font-size: 20px;
                font-family: var(--fonte-secundaria);
            }
        }
    </style>
</head>

<body>
    <header>
        <div class="logo__aully__infinito">
            <div class="aully">Aully</div>
            <img src="./imgStyle/logo.png" alt="Logo Aully">
        </div>
    </header>
    <main>
        <div class="login-container">
            <form class="login_dados" method="post" action="validaLogin.php">
                <input type="text" name="username" placeholder="Nome de usuário" required>
                <input type="password" name="senha" placeholder="Senha" required>
                <button type="submit">Entrar</button>
            </form>
            <!-- <div class="forgot-password">
                <a href="../esqueceu_senha/email_recuperacao.html">Esqueceu sua senha?</a>
            </div> -->
            <?php
            if (isset($_SESSION['loginErro'])) {
                echo "<p style='color:white'>" . $_SESSION['loginErro'] . "</p>";
                unset($_SESSION['loginErro']);
            }
            ?>
        </div>
    </main>
</body>

</html>