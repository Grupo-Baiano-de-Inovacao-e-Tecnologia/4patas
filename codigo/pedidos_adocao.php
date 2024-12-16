<?php
include_once 'config.php';
include_once 'SolicitacaoAdocao.php';

session_start();
if (!isset($_SESSION['abrigo_id'])) {
    echo "<script>
    alert('ID do abrigo não fornecido.');
    window.location.href = 'login.php';
    </script>";
    exit;
}

$abrigo_id = $_SESSION['abrigo_id'];
$solicitacaoAdocao = new SolicitacaoAdocao($conn);
$solicitacoes = $solicitacaoAdocao->listSolicitacoesByAbrigo($abrigo_id)
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilo/pedidos_adocao.css">
    <link rel="stylesheet" href="estilo/div_cad.css">
    <title>Pedidos de Adoção</title>
</head>
<body>
    <header>
        <div id="logo">
            <a href="index.php">
                <img src="imagem/logo.png" alt="Logo 4 Patas">
            </a>
        </div>
        <nav>
            <a href="minha_conta_abrigo.php">Minha Conta</a>
            <a href="#" id="abrirDivCadastro">Cadastrar Animal</a>
            <a href="historico_adocao.php">Histórico</a>
            <a href="pagina_abrigo.php">Meus Animais</a>
            <a href="sair.php">Sair</a>
        </nav>
    </header>

    <div id="cadastroDiv" class="cadastroContainer" style="display: none;">
        <div class="cadastroConteudoA">
            <p class="cadastroTituloA">SELECIONE O TIPO DE ANIMAL</p>
            <a id="cadastroCao" href="selecionar_tipo.php?tipo=cao">Cão</a>
            <a id="cadastroGato" href="selecionar_tipo.php?tipo=gato">Gato</a>
            <div class="cadastroFecharA">&times;</div>
        </div>
    </div>

    <div id="container">
        <h1>Solicitações de Adoção</h1>
        <table>
            <thead>
                <tr>
                    <th class="usuario">Nome do Adotante</th>
                    <th class="animal">Nome do Animal</th>
                    <th class="data">Solicitado em</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($solicitacoes as $solicitacao) { ?>
                    <tr>
                        <td class="usuario"><?php echo htmlspecialchars($solicitacao['usuario_nome']); ?></td>
                        <td class="animal"><?php echo htmlspecialchars($solicitacao['animal_nome']); ?></td>
                        <td class="data"><?php echo date('d/m/Y', strtotime($solicitacao['data_inicio'])); ?></td>
                        <td class="abrir"><a id="abrir" href="detalhes_solicitacao.php?id=<?php echo $solicitacao['id']; ?>">ABRIR</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>

<script>
    document.getElementById('abrirDivCadastro').onclick = function(event) {
        event.preventDefault();
        document.getElementById('cadastroDiv').style.display = 'flex';
    };

    document.querySelector('.cadastroFecharA').onclick = function() {
        document.getElementById('cadastroDiv').style.display = 'none';
    };

    window.onclick = function(event) {
        if (event.target == document.getElementById('cadastroDiv')) {
                document.getElementById('cadastroDiv').style.display = 'none';
        }
    };
</script>
</html>
