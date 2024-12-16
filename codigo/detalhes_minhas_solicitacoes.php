<?php
include_once 'config.php';
include_once 'Usuario.php';
include_once 'Animal.php';
include_once 'SolicitacaoAdocao.php';

$solicitacao_id = $_GET['id'] ?? null;

if ($solicitacao_id) {
    $solicitacao = new SolicitacaoAdocao($conn);
    $solicitacao->id = $solicitacao_id;
    $detalhes_solicitacao = $solicitacao->readOne();
    $status_solicitacao = $detalhes_solicitacao['status'] ?? '';

    if ($detalhes_solicitacao) {
        $usuario = new Usuario($conn);
        $usuario->id = $detalhes_solicitacao['usuario_id'];
        $usuario_detalhes = $usuario->readOne();
    
        $animal = new Animal($conn);
        $animal->id = $detalhes_solicitacao['animal_id'];
        $animal_detalhes = $animal->readOne();
    
        $animal_id = $detalhes_solicitacao['animal_id'] ?? null;
        if ($animal_id) {
            $query = "SELECT a.*, ab.nome AS abrigo_nome, 
                             ab.cep AS abrigo_cep, ab.rua AS abrigo_rua, 
                             ab.numero AS abrigo_numero, ab.complemento AS abrigo_complemento, 
                             ab.bairro AS abrigo_bairro, ab.cidade AS abrigo_cidade, 
                             ab.estado AS abrigo_estado, ab.email AS abrigo_email, 
                             ab.telefone AS abrigo_telefone 
                      FROM animal a 
                      JOIN abrigo ab ON a.abrigo_id = ab.id 
                      WHERE a.id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $animal_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $abrigo_detalhes = $result->fetch_assoc();

            if (!$abrigo_detalhes) {
                echo "<script>
                    alert('Detalhes do animal não encontrados.');
                    window.location.href = 'login.php';
                </script>";
                exit;
            }

            $abrigo_nome = $abrigo_detalhes['abrigo_nome'] ?? '';
            $abrigo_cep = $abrigo_detalhes['abrigo_cep'] ?? '';
            $abrigo_rua = $abrigo_detalhes['abrigo_rua'] ?? '';
            $abrigo_numero = $abrigo_detalhes['abrigo_numero'] ?? '';
            $abrigo_complemento = $abrigo_detalhes['abrigo_complemento'] ?? '';
            $abrigo_bairro = $abrigo_detalhes['abrigo_bairro'] ?? '';
            $abrigo_cidade = $abrigo_detalhes['abrigo_cidade'] ?? '';
            $abrigo_estado = $abrigo_detalhes['abrigo_estado'] ?? '';
            $abrigo_email = $abrigo_detalhes['abrigo_email'] ?? '';
            $abrigo_telefone = $abrigo_detalhes['abrigo_telefone'] ?? '';
        }

        $nome_usuario = $detalhes_solicitacao['nome_usuario'] ?? $usuario_detalhes['nome'];
        $email_usuario = $detalhes_solicitacao['email_usuario'] ?? $usuario_detalhes['email'];
        $telefone_usuario = $detalhes_solicitacao['telefone_usuario'] ?? $usuario_detalhes['telefone'];
        $cpf_usuario = $detalhes_solicitacao['cpf_usuario'] ?? $usuario_detalhes['cpf'];
        $cep_usuario = $detalhes_solicitacao['cep_usuario'] ?? $usuario_detalhes['cep'];
        $idade_usuario = isset($detalhes_solicitacao['idade_usuario']) ? $detalhes_solicitacao['idade_usuario'] : (new DateTime($usuario_detalhes['data_nascimento']))->diff(new DateTime())->y;

        $nome_animal = $detalhes_solicitacao['nome_animal'] ?? $animal_detalhes['nome'];
        $idade_animal = isset($detalhes_solicitacao['idade_animal']) ? $detalhes_solicitacao['idade_animal'] : (new DateTime($animal_detalhes['data_nascimento']))->diff(new DateTime())->y;
        $sexo_animal = $detalhes_solicitacao['sexo_animal'] ?? $animal_detalhes['sexo'];
    } else {
        echo "<script>
            alert('Solicitação não encontrada!');
            window.location.href = 'login.php';
        </script>";
        exit;
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['solicitacao_id'])) {
        $solicitacao = new SolicitacaoAdocao($conn);
        $solicitacao->id = $_POST['solicitacao_id'];

        if ($solicitacao->delete($solicitacao->id)) {
            header('Location: minhas_solicitacoes.php');
            exit;
        } else {
            echo "Erro ao excluir a solicitação.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilo/detalhes_minhas_solicitacoes.css">
    <title>Detalhes da Minha Solicitação</title>
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

    <div id="msgUsuarioAprovado" class="aprovacaoContainer" style="display: <?php echo $status_solicitacao === 'aprovado' ? 'flex' : 'none'; ?>;">
        <div class="avisoConteudo">
            <h2>Solicitação Aprovada!</h2>
            <p>Você tem um prazo de 20 dias para entrar em contato ou visitar o abrigo: <strong><?php echo htmlspecialchars($abrigo_nome);?></strong>.</p>
            <p><strong>Localizado no endereço:</strong> <?php echo htmlspecialchars($abrigo_rua . ', ' . $abrigo_numero . ', ' . ($abrigo_complemento ? $abrigo_complemento . ', ' : '') . $abrigo_bairro . ', ' . $abrigo_cidade . ' - ' . $abrigo_estado . ', CEP: ' . $abrigo_cep); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($abrigo_email); ?></p>
            <p><strong>Telefone:</strong> <?php echo htmlspecialchars($abrigo_telefone); ?></p>
            <p><strong>Atenção:</strong> Caso não entre em contato ou não compareça dentro desse prazo, a solicitação poderá ser cancelada.</p>
            <button id="fecharAprovacao">OK</button>
        </div>
    </div>

    <div id="msgUsuarioRecusado" class="recusaContainer" style="display: <?php echo $status_solicitacao === 'recusado' ? 'flex' : 'none'; ?>;">
        <div class="avisoConteudo">
            <h2>Solicitação Recusada</h2>
            <p>Infelizmente, sua solicitação de adoção foi recusada.</p>
            <button id="fecharRecusado">OK</button>
        </div>
    </div>

    <div id="container">
        <h1>Detalhes da Solicitação</h1>
        <div class="informacoes-adotante">
            <h2>Suas Informações</h2>
            <div class="linha">
                <p id="nome"><strong>Nome:</strong> <?php echo htmlspecialchars($nome_usuario); ?></p>
                <p id="data_nascimento"><strong>Idade:</strong> <?php echo htmlspecialchars($idade_usuario . ' anos'); ?></p>
            </div>
            <div class="linha">
                <p id="email"><strong>Email:</strong> <?php echo htmlspecialchars($email_usuario); ?></p>
                <p id="telefone"><strong>Telefone:</strong> <?php echo htmlspecialchars($telefone_usuario); ?></p>
            </div>
            <div class="linha">
                <p id="cpf"><strong>CPF:</strong> <?php echo htmlspecialchars($cpf_usuario); ?></p>
                <p id="cep"><strong>CEP:</strong> <?php echo htmlspecialchars($cep_usuario); ?></p>
            </div>
            <div class="linha">
                <p id="bairro"><strong>Bairro:</strong> <?php echo htmlspecialchars($usuario->bairro); ?></p>
                <p id="rua"><strong></strong> <?php echo htmlspecialchars($usuario->rua); ?></p>
                <p id="numero"><strong></strong> <?php echo htmlspecialchars($usuario->numero); ?></p>
            </div>
            <div class="linha">
                <p id="complemento"><strong></strong> <?php echo htmlspecialchars($usuario->complemento ?: 'Complemento(opcional)'); ?></p>
                <p id="cidade"><strong>Cidade:</strong> <?php echo htmlspecialchars($usuario->cidade); ?></p>
                <p id="estado"><strong></strong> <?php echo htmlspecialchars($usuario->estado); ?></p>
            </div>
            <button id="abrirDivAdotante">DETALHES</button>
        </div>

        <div id="abrirAdotante" class="abrir" style="display: none;">
            <div class="abrir-content">
                <span class="fechar" id="fecharAdotante">&times;</span>
                <h2>Suas Informações</h2>
                <div class="linha">
                    <p id="nome"><strong>Nome:</strong> <?php echo htmlspecialchars($nome_usuario); ?></p>
                    <p id="data_nascimento"><strong>Idade:</strong> <?php echo htmlspecialchars($idade_usuario . ' anos'); ?></p>
                </div>
                <div class="linha">
                    <p id="email"><strong>Email:</strong> <?php echo htmlspecialchars($email_usuario); ?></p>
                    <p id="telefone"><strong>Telefone:</strong> <?php echo htmlspecialchars($telefone_usuario); ?></p>
                </div>
                <div class="linha">
                    <p id="cpf"><strong>CPF:</strong> <?php echo htmlspecialchars($cpf_usuario); ?></p>
                    <p id="cep"><strong>CEP:</strong> <?php echo htmlspecialchars($cep_usuario); ?></p>
                </div>
                <div class="linha">
                    <p id="bairro"><strong>Bairro:</strong> <?php echo htmlspecialchars($usuario->bairro); ?></p>
                    <p id="rua"><strong></strong> <?php echo htmlspecialchars($usuario->rua); ?></p>
                    <p id="numero"><strong></strong> <?php echo htmlspecialchars($usuario->numero); ?></p>
                </div>
                <div class="linha">
                    <p id="complemento"><strong></strong> <?php echo htmlspecialchars($usuario->complemento ?: 'Complemento(opcional)'); ?></p>
                    <p id="cidade"><strong>Cidade:</strong> <?php echo htmlspecialchars($usuario->cidade); ?></p>
                    <p id="estado"><strong></strong> <?php echo htmlspecialchars($usuario->estado); ?></p>
                </div>
            </div>
        </div>

        <div class="informacoes-animal">
            <h2>Informações do Animal</h2>
            <div class="linha">
                <p id="nome_animal"><strong>Nome:</strong> <?php echo htmlspecialchars($nome_animal); ?></p>
                <p id="idade"><strong></strong> <?php echo htmlspecialchars($animal->calcularIdade()); ?></p>
            </div>
            <div class="linha">
                <p id="sexo"><strong>Sexo:</strong> <?php echo htmlspecialchars($animal->sexo === 'macho' ? 'Macho' : 'Fêmea'); ?></p>
                <p id="castrado"><strong></strong> <?php echo htmlspecialchars($animal->castrado ? 'Castrado' : 'Não Castrado'); ?></p>
                <p id="porte"><strong>Porte:</strong> 
                    <?php 
                    switch ($animal->porte) {
                        case 'pequeno':
                            echo 'Pequeno';
                            break;
                        case 'medio':
                            echo 'Médio';
                            break;
                        case 'grande':
                            echo 'Grande';
                            break;
                        default:
                            echo 'Desconhecido';
                    }
                    ?>
                </p>
            </div>
            <div class="linha">
                <p id="descricao"><strong>Descrição:</strong> <?php echo htmlspecialchars($animal->descricao); ?></p>
            </div>
            <button id="abrirDivAnimal">DETALHES</button>
        </div>

        <div id="abrirAnimal" class="abrir" style="display: none;">
            <div class="abrir-content" id="abrir-contentA">
                <span class="fechar" id="fecharAnimal">&times;</span>
                <h2>Detalhes do Animal</h2>
                <div class="img-info-container">
                    <img src="animais/<?php echo htmlspecialchars($animal->foto); ?>" alt="Foto do Animal">
                    <div class="info-content">
                        <div class="linha">
                            <p id="nome_animal"><strong>Nome:</strong> <?php echo htmlspecialchars($nome_animal); ?></p>
                            <p id="idade"><strong></strong> <?php echo htmlspecialchars($animal->calcularIdade()); ?></p>
                        </div>
                        <div class="linha">
                            <p id="sexo"><strong>Sexo:</strong> <?php echo htmlspecialchars($animal->sexo === 'macho' ? 'Macho' : 'Fêmea'); ?></p>
                            <p id="castrado"><strong></strong> <?php echo htmlspecialchars($animal->castrado ? 'Castrado' : 'Não Castrado'); ?></p>
                            <p id="porte"><strong>Porte:</strong> 
                                <?php 
                                    switch ($animal->porte) {
                                        case 'pequeno':
                                            echo 'Pequeno';
                                            break;
                                        case 'medio':
                                            echo 'Médio';
                                            break;
                                        case 'grande':
                                            echo 'Grande';
                                            break;
                                        default:
                                            echo 'Desconhecido';
                                    }
                                ?>
                            </p>
                        </div>
                        <div class="linha">
                            <p id="descricao"><?php echo htmlspecialchars($animal->descricao); ?></p>
                        </div>
                    </div>
                </div>
            </div>     
        </div>

        <div class="formulario">
            <p><?php echo nl2br(htmlspecialchars($detalhes_solicitacao['formulario'])); ?></p>
        </div>

        <div class="info">
            <p id="dt_solic">Inicio <?php echo date('d/m/Y', strtotime($detalhes_solicitacao['data_inicio'])); ?></p>
            <p id="situacao"><?php echo $status_solicitacao  === 'aprovado' ? 'APROVADA' : ($status_solicitacao  === 'recusado' ? 'RECUSADA' : 'ANÁLISE'); ?></p>
        </div>  

        <div class="botoes-acoes">
            <a id="voltar" href="#" onclick="history.back();">VOLTAR</a>
            <?php if ($status_solicitacao === 'analise'): ?>
                <form action="" method="post" onsubmit="return confirm('Tem certeza que deseja excluir esta solicitação?');">
                    <input type="hidden" name="solicitacao_id" value="<?php echo htmlspecialchars($solicitacao_id); ?>">
                    <button id="excluir" type="submit" class="botao-excluir">EXCLUIR</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const abrirDivAdotante = document.getElementById('abrirDivAdotante');
            const fecharAdotante = document.getElementById('fecharAdotante');
            const abrirAdotanteDiv = document.getElementById('abrirAdotante');
            const abrirDivAnimal = document.getElementById('abrirDivAnimal');
            const fecharAnimal = document.getElementById('fecharAnimal');
            const abrirAnimalDiv = document.getElementById('abrirAnimal');

            abrirDivAdotante.addEventListener('click', () => {
                abrirAdotanteDiv.style.display = 'block';
            });

            fecharAdotante.addEventListener('click', () => {
                abrirAdotanteDiv.style.display = 'none';
            });

            abrirDivAnimal.addEventListener('click', () => {
                abrirAnimalDiv.style.display = 'block';
            });

            fecharAnimal.addEventListener('click', () => {
                abrirAnimalDiv.style.display = 'none';
            });
        });
        document.getElementById('fecharAprovacao').addEventListener('click', function() {
            document.getElementById('msgUsuarioAprovado').style.display = 'none';
        });

        document.getElementById('fecharRecusado').addEventListener('click', function() {
            document.getElementById('msgUsuarioRecusado').style.display = 'none';
        });
    </script>
</body>
</html>
