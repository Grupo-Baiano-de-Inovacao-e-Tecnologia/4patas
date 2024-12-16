<?php
session_start();
include_once 'config.php';
include_once 'SolicitacaoAdocao.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>
        alert('ID do usuário não fornecido.');
        window.location.href = 'login.php';
    </script>";
    exit;
}

$usuario_id = $_SESSION['user_id'];
$solicitacaoAdocao = new SolicitacaoAdocao($conn);
$animaisAprovados = $solicitacaoAdocao->listAnimaisAprovadosByUser($usuario_id);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilo/meus_animais.css">
    <title>Meus Animais</title>
</head>
<body>
    <header>
        <div id="logo">
            <a href="index.php">
                <img src="imagem/logo.png" alt="Logo 4 Patas">
            </a>
        </div>
        <nav>
            <a href="minha_conta_usuario.php">Minha Conta</a>
            <a href="minhas_solicitacoes.php">Minhas Solicitações</a>
            <a href="ver_abrigos.php">Canis e Abrigos</a>
            <a href="pagina_usuario.php">Quero Adotar</a>
            <a href="sair.php">Sair</a>
        </nav>
    </header>

    <div id="container">
        <h1>Meus Animais</h1>
        <div class="animais-container">
            <?php if (!empty($animaisAprovados)) { ?>
                <?php foreach ($animaisAprovados as $animal) { ?>
                    <div class="animal" onclick="redirectToDetails(<?php echo htmlspecialchars($animal['id']); ?>)">
                    <img src="animais/<?php echo htmlspecialchars($animal['foto']); ?>" alt="Foto de <?php echo htmlspecialchars($animal['nome']); ?>">
                    <p><?php echo htmlspecialchars($animal['nome']); ?></p>
                </div>

                <?php } ?>
            <?php } else { ?>
                <p classe="mensagem">Você ainda não adotou nenhum animal.</p>
            <?php } ?>
        </div>
    </div>

    <script>
    function redirectToDetails(animalId) {
        window.location.href = 'detalhes_meu_animal.php?id=' + animalId;
    }
    </script>
</body>
</html>
