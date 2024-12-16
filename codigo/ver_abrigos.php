<?php
session_start();
include_once 'config.php';
include_once 'abrigo.php';

$autenticado = isset($_SESSION['user_id']);
$user_id = $autenticado ? $_SESSION['user_id'] : null;
$abrigo = new Abrigo($conn);
$abrigos = $abrigo->listAbrigoseCanis();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilo/ver_abrigos.css">
    <title>Canis e Abrigos</title>
</head>
<body>
    <header>
        <div id="logo">
            <a href="index.php">
                <img src="imagem/logo.png" alt="Logo 4 Patas">
            </a>
        </div>
        <nav>
            <a href="<?php echo $autenticado ? 'minha_conta_usuario.php' : 'javascript:void(0);' ?>" onclick="<?php echo $autenticado ? '' : 'confirmarLogin(\'login.php\')' ?>">Minha Conta</a>
            <?php if ($autenticado): ?>
                <a href="meus_animais.php">Meus Animais</a>
                <a href="minhas_solicitacoes.php">Minhas Solicitações</a>
            <?php endif; ?>
            <a href="pagina_usuario.php">Quero Adotar</a>
            <a href="sair.php">Sair</a>
        </nav>
    </header>

    <div id="container">
        <h1>Canis e Abrigos</h1>
        <table>
            <thead>
                <tr>
                    <th class="nome">Nome</th>
                    <th class="telefone">Telefone</th> 
                    <th class="endereco">Endereço</th>
                    <th class="detalhes"></th>
                    <th class="whats"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($abrigos as $abrigo) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($abrigo['nome']); ?></td>
                        <td><?php echo htmlspecialchars($abrigo['telefone']); ?></td>
                        <td id ="endereco"><?php echo htmlspecialchars($abrigo['rua'] . ', ' . $abrigo['numero'] . ' - ' . $abrigo['bairro'] . ', ' . $abrigo['cidade'] . ' - ' . $abrigo['estado']); ?></td>
                        <td>
                            <?php if (!empty($abrigo['site'])) { ?>
                                <a href="<?php echo htmlspecialchars($abrigo['site']); ?>" target="_blank">
                                <img class="detalhesb"src="imagem/icone_link.png" alt="Link">
                                </a>
                            <?php } ?>
                        </td>
                        <td class="whatsapp">
                            <a href="https://wa.me/<?= preg_replace('/\D/', '', $abrigo['telefone']) ?>" target="_blank">
                                <img src="imagem/icone_whats.png" alt="WhatsApp" style="height: 2vh;">
                            </a>
                        </td>

                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <script>        
        function confirmarLogin(url) {
            if (confirm("É necessário fazer o login para acessar esta funcionalidade. Deseja fazer o login agora?")) {
                window.location.href = url;
            }
        }
    </script>
</body>
</html>
