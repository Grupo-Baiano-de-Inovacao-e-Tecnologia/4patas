<?php
include_once 'config.php';
include_once 'SolicitacaoAdocao.php';
session_start();

$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['user_id'])) {
    echo "<script>
    alert('ID do usuário não fornecido.');
    window.location.href = 'login.php';
    </script>";
    exit;
}

if (isset($_GET['delete_id'])) {
    $solicitacao = new SolicitacaoAdocao($conn);
    $delete_id = $_GET['delete_id'];
    
    if ($solicitacao->delete($delete_id)) {
        header("Location: minhas_solicitacoes.php?deleted=success");
        exit();
    } else {
        echo "Erro ao excluir a solicitação.";
    }
}

$solicitacoes = [];

$query = "SELECT sa.id, sa.nome_animal, sa.data_inicio, sa.status, a.nome AS instituicao_nome 
          FROM solicitacao_adocao sa
          JOIN animal an ON sa.animal_id = an.id
          JOIN abrigo a ON an.abrigo_id = a.id
          WHERE sa.usuario_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    switch ($row['status']) {
        case 'aprovado':
            $row['status'] = 'Aprovada';
            break;
        case 'recusado':
            $row['status'] = 'Recusada';
            break;
        case 'analise':
            $row['status'] = 'Análise';
            break;
    }
    $solicitacoes[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilo/minhas_solicitacoes.css">
    <title>Minhas Solicitações</title>
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
            <a href="meus_animais.php">Meus Animais</a>
            <a href="ver_abrigos.php">Canis e Abrigos</a>
            <a href="pagina_usuario.php">Quero Adotar</a>
            <a href="sair.php">Sair</a>
        </nav>
    </header>

    <div id="container">
        <h1>Minhas Solicitações</h1>
        <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 'success'): ?>
            <p class="mensagem">Solicitação excluída com sucesso.</p>
        <?php endif; ?>
        <table>
            <thead>
                <tr>
                    <th class="nome_animal">Nome do Animal</th>
                    <th class="instituicao">Instituição</th> 
                    <th class="solicitado">Solicitado em</th>
                    <th class="situacao">Situação</th>
                    <th class="excluir">Excluir</th>
                    <th class="detalhes">Mais</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($solicitacoes as $solicitacao): ?>
                <tr>
                    <td><?php echo htmlspecialchars($solicitacao['nome_animal']); ?></td>
                    <td><?php echo htmlspecialchars($solicitacao['instituicao_nome']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($solicitacao['data_inicio'])); ?></td>
                    <td><?php echo htmlspecialchars($solicitacao['status']); ?></td>
                    <td>
                        <?php if ($solicitacao['status'] == 'Análise'): ?>
                            <a id="excluir" href="minhas_solicitacoes.php?delete_id=<?php echo $solicitacao['id']; ?>" onclick="return confirm('Tem certeza que deseja excluir esta solicitação?');">-</a>
                        <?php else: ?>
                            
                        <?php endif; ?>
                    </td>
                    <td><a id="detalhes" href="detalhes_minhas_solicitacoes.php?id=<?php echo $solicitacao['id']; ?>">+</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
