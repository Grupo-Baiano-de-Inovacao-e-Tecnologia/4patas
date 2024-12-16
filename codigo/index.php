<?php
session_start();
include_once 'config.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio</title>
    <link rel="stylesheet" href="estilo/index.css">
    <link rel="stylesheet" href="estilo/div_cad.css">
</head>
<body>
    <header>
        <a href="index.php">    
            <img id="logo" src="imagem/logo_inicio.png" alt="Logo Quatro Patas">
        </a>    
        <nav>
            <a href="#" id="abrirDivopcao">Seja um Parceiro</a>
        </nav>
    </header>

    <main id="conteudo">
        <h1>4 PATAS</h1>
        <p>Comece sua Jornada de Adoção Aqui!</p>
        <p>Seja Parte da Solução</p>
        <button onclick="location.href='pagina_usuario.php'">ADOTAR</button>
    </main>

    <div id="opcaoDiv" class="opcaoContainer" style="display: none;">
        <div class="opcaoConteudo">
            <p class="opcaoTitulo">SEJA UM PARCEIRO</p>
            <a id="login" href="login.php">LOGIN</a>
            <a id="cadastroAbrigo" href="cadastro_abrigo_1.php">CADASTRO</a>
            <div class="opcaoFechar">&times;</div>
        </div>
    </div>

    <script>
        document.getElementById('abrirDivopcao').onclick = function(event) {
            event.preventDefault();
            document.getElementById('opcaoDiv').style.display = 'flex';
        };

        document.querySelector('.opcaoFechar').onclick = function() {
            document.getElementById('opcaoDiv').style.display = 'none';
        };

        window.onclick = function(event) {
            if (event.target == document.getElementById('opcaoDiv')) {
                document.getElementById('opcaoDiv').style.display = 'none';
            }
        };
    </script>
</body>
</html>
