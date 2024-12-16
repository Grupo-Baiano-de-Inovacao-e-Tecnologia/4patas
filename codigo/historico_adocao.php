<?php
session_start();
include_once 'config.php';

if (!isset($_SESSION['abrigo_id'])) {
    echo "<script>
        alert('ID do abrigo não fornecido.');
        window.location.href = 'login.php';
    </script>";
    exit;
}

$sql = "SELECT sa.id, sa.nome_usuario, sa.nome_animal, sa.data_inicio, sa.data_fim, sa.status, u.telefone
        FROM solicitacao_adocao sa
        JOIN usuario u ON sa.usuario_id = u.id
        JOIN animal a ON sa.animal_id = a.id
        WHERE sa.status IN ('aprovado', 'recusado') 
        AND a.abrigo_id = ?
        ORDER BY sa.data_fim DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $_SESSION['abrigo_id']); 
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilo/historico_adocao.css">
    <link rel="stylesheet" href="estilo/div_cad.css">
    <title>Histórico de Adoções</title>
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
            <a href="pedidos_adocao.php">Pedidos de Adoção</a>
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
        <h1>Histórico de Adoções</h1>
        <table>
            <thead>
                <tr>
                    <th class="usuario">Nome do Adotante</th>
                    <th class="animal">Nome do Animal</th>
                    <th class="data">Período</th>
                    <th class="situacao">Situação</th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="usuario"><?= htmlspecialchars($row['nome_usuario']) ?></td>
                            <td class="animal"><?= htmlspecialchars($row['nome_animal']) ?></td>
                            <td class="data"><?= date('d/m/Y', strtotime($row['data_inicio'])) ?> - <?= date('d/m/Y', strtotime($row['data_fim'])) ?></td>
                            <td class="situacao"><?= htmlspecialchars(ucfirst($row['status'])) ?></td>
                            <td class="abrir"><a id="abrir" href="detalhes_adocao.php?id=<?= $row['id'] ?>">+</a></td>
                            <td class="whatsapp">
                                <a href="https://wa.me/<?= preg_replace('/\D/', '', $row['telefone']) ?>" target="_blank">
                                    <img src="imagem/icone_whats.png" alt="WhatsApp" style="height: 2.3vh;">
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">Nenhuma solicitação encontrada.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

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
</body>
</html>

<?php
$conn->close();
?>
