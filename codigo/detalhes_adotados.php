<?php
session_start();
include_once 'config.php';
include_once 'Animal.php';
include_once 'SolicitacaoAdocao.php';

function formatarCastracao($castrado) {
    return $castrado === 'sim' ? "Castrado" : "Não Castrado";
}

function formatarSexo($sexo) {
    return $sexo === 'macho' ? "Macho" : "Fêmea";
}

function formatarPorte($porte) {
    switch ($porte) {
        case 'pequeno':
            return "Porte Pequeno";
        case 'medio':
            return "Porte Médio";
        case 'grande':
            return "Porte Grande";
        default:
            return ucfirst($porte);
    }
}

if (!isset($_GET['animal_id']) || empty($_GET['animal_id'])) {
    echo "<script>
            alert('Animal não encontrado!');
            window.location.href = 'login.php';
          </script>";
    exit;
}

$animal_id = $_GET['animal_id'];
$query = "SELECT a.* FROM animal a WHERE a.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $animal_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>
            alert('Animal não encontrado no banco de dados!');
            window.location.href = 'login.php';
          </script>";
    exit;
}

$row = $result->fetch_assoc();
$animal = new Animal($conn);
$animal->id = $row['id'];
$animal->data_nascimento = $row['data_nascimento'];
$animal->nome = $row['nome'];
$animal->sexo = $row['sexo'];
$animal->foto = $row['foto'];
$animal->castrado = $row['castrado'];
$animal->porte = $row['porte'];
$animal->descricao = $row['descricao'];

$solicitacaoAdocao = new SolicitacaoAdocao($conn);
$solicitacaoAdocao->animal_id = $animal_id;

$querySolicitacao = "SELECT sa.nome_usuario, sa.data_fim 
                      FROM solicitacao_adocao sa
                      WHERE sa.animal_id = ? AND sa.status = 'aprovado'
                      ORDER BY sa.data_fim DESC LIMIT 1";
$stmtSolicitacao = $conn->prepare($querySolicitacao);
$stmtSolicitacao->bind_param('i', $animal_id);
$stmtSolicitacao->execute();
$resultSolicitacao = $stmtSolicitacao->get_result();
$solicitacao = $resultSolicitacao->fetch_assoc();

$usuario_nome = $solicitacao ? $solicitacao['nome_usuario'] : 'Não disponível';
$data_fim = $solicitacao ? $solicitacao['data_fim'] : null;

$idadeFormatada = $animal->calcularIdade();

$mostrarBotaoCancelar = false;
if ($data_fim) {
    $prazo = new DateTime($data_fim);
    $hoje = new DateTime();
    $intervalo = $hoje->diff($prazo);
    $diasRestantes = $intervalo->days;
    $mostrarBotaoCancelar = ($diasRestantes <= 30);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar_adocao'])) {
    $usuario_nome = $_POST['usuario_nome'];
    if ($solicitacaoAdocao->cancelarAdocao($animal_id, $usuario_nome)) {
        echo "<script>
                alert('Adoção cancelada com sucesso!');
                window.location.href = 'pagina_abrigo.php';
              </script>";
    } else {
        echo "<script>alert('Erro ao cancelar a adoção.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilo/detalhes_adotados.css">
    <link rel="stylesheet" href="estilo/div_cad.css">
    <title>Detalhes do Animal Adotado</title>
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
            <a href="historico_adocao.php">Histórico</a>
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
        <h1><?php echo htmlspecialchars($animal->nome); ?></h1>
        
        <div class="animal-detalhes">
            <div class="imagem-container">
                <img id="uploadPreview" src="animais/<?php echo htmlspecialchars($animal->foto); ?>" alt="Foto do Animal">
                <a href="#" id="verImagem">Abrir imagem</a>
            </div>
            <div class="grupo-formulario">
                <div class="linha">
                    <input type="text" id="sexo" name="sexo" readonly value="<?php echo formatarSexo($animal->sexo); ?>">
                    <input type="text" id="castrado" name="castrado" readonly value="<?php echo formatarCastracao($animal->castrado); ?>">
                    <input type="text" id="idade" name="idade" readonly value="<?php echo $idadeFormatada; ?>">
                </div>
                <div class="linha">
                    <input type="text" id="porte" name="porte" readonly value="<?php echo formatarPorte($animal->porte); ?>">
                    <input type="text" id="usuario" name="usuario" readonly value="Fui adotado por <?php echo htmlspecialchars($usuario_nome); ?>">                
                </div>
                <textarea id="descricao" name="descricao" rows="4" readonly><?php echo htmlspecialchars($animal->descricao); ?></textarea><br>
            </div>
        </div>
        <div class="botoes">
            <a id="voltar" href="#" onclick="history.back();">VOLTAR</a>
            <?php if ($mostrarBotaoCancelar): ?>
                <div class="cancelar">
                    <form method="POST" action="" onsubmit="return confirmarCancelamento();">
                        <input type="hidden" name="usuario_nome" value="<?php echo htmlspecialchars($usuario_nome); ?>">
                        <input type="submit" name="cancelar_adocao" id="cancelar" value="CANCELAR ADOÇÃO">
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="abrir" id="abrirImagem">
        <button class="fechar" id="fecharImagem">X</button>
        <img id="imagemAnimal" src="animais/<?php echo htmlspecialchars($animal->foto); ?>" alt="Foto do Animal">
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

        document.getElementById('verImagem').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('abrirImagem').style.display = 'flex';
        });

        document.getElementById('fecharImagem').addEventListener('click', function() {
            document.getElementById('abrirImagem').style.display = 'none';
        });

        function confirmarCancelamento() {
            return confirm('Você tem certeza que deseja cancelar esta adoção? Essa ação só deve ser realizada se o adotante não compareceu ou desistiu da adoção. Após a confirmação, essa ação não poderá ser desfeita.');
        }

    </script>
</body>
</html>
