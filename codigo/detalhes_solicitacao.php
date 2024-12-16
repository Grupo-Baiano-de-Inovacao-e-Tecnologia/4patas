<?php
include_once 'config.php';
include_once 'Usuario.php';
include_once 'Animal.php';
include_once 'SolicitacaoAdocao.php';

$solicitacao_id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['solicitacao_id'], $_POST['acao'])) {
    $solicitacao_id = $_POST['solicitacao_id'];
    $acao = $_POST['acao'];

    $solicitacao = new SolicitacaoAdocao($conn);
    $solicitacao->id = $solicitacao_id;

    if ($acao === 'Okaprovar') {
        if ($solicitacao->update($solicitacao_id, 'aprovado')) {
            echo "<script>
                    setTimeout(function(){
                        window.location.href = 'historico_adocao.php';
                    }, 0);
                  </script>";
            exit;
        } else {
            echo "<script>alert('Erro ao aprovar a solicitação.');</script>";
        }
    } 
    
    elseif ($acao === 'recusar') {
        if ($solicitacao->update($solicitacao_id, 'recusado')) {
            echo "<script>
                    alert('Solicitação recusada com sucesso!');
                    setTimeout(function(){
                        window.location.href = 'historico_adocao.php';
                    }, 0);
                  </script>";
            exit;
        } 
    } 
}

if ($solicitacao_id) {
    $solicitacao = new SolicitacaoAdocao($conn);
    $solicitacao->id = $solicitacao_id;
    $detalhes_solicitacao = $solicitacao->readOne();

    if ($detalhes_solicitacao) {
        $usuario = new Usuario($conn);
        $usuario->id = $detalhes_solicitacao['usuario_id'];
        $usuario_detalhes = $usuario->readOne();
    
        $animal = new Animal($conn);
        $animal->id = $detalhes_solicitacao['animal_id'];
        $animal_detalhes = $animal->readOne();
    
        $nome_usuario = $detalhes_solicitacao['nome_usuario'] ?? $usuario_detalhes['nome'];
        $email_usuario = $detalhes_solicitacao['email_usuario'] ?? $usuario_detalhes['email'];
        $telefone_usuario = $detalhes_solicitacao['telefone_usuario'] ?? $usuario_detalhes['telefone'];
        $cpf_usuario = $detalhes_solicitacao['cpf_usuario'] ?? $usuario_detalhes['cpf'];
        $cep_usuario = $detalhes_solicitacao['cep_usuario'] ?? $usuario_detalhes['cep'];
        if (isset($detalhes_solicitacao['idade_usuario'])) {
            $idade_usuario = $detalhes_solicitacao['idade_usuario'];
        } else {
            $idade_usuario = (new DateTime($usuario_detalhes['data_nascimento']))->diff(new DateTime())->y;
        }
    
        $nome_animal = $detalhes_solicitacao['nome_animal'] ?? $animal_detalhes['nome'];
        if (isset($detalhes_solicitacao['idade_animal'])) {
            $idade_animal = $detalhes_solicitacao['idade_animal'];
        } else {
            $idade_animal = (new DateTime($animal_detalhes['data_nascimento']))->diff(new DateTime())->y;
        }
        $sexo_animal = $detalhes_solicitacao['sexo_animal'] ?? $animal_detalhes['sexo'];
    } else {
        echo "<script>
            alert('Solicitação não encontrada!');
            window.location.href = 'login.php';
        </script>";
        exit;
    }
} else {
    echo "<script>
        alert('ID da solicitação não fornecido.');
        window.location.href = 'login.php';
    </script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilo/detalhes_solicitacao.css">
    <link rel="stylesheet" href="estilo/div_cad.css">
    <title>Detalhes da Solicitação</title>
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

    <div id="msgAprovacao" class="aprovacaoContainer" style="display: none;">
        <div class="avisoConteudo">
            <h2>Solicitação Aprovada!</h2>
            <p>Entre em contato com o adotante: <strong><?php echo htmlspecialchars($nome_usuario); ?></strong></p>
            <p><strong>Telefone:</strong> <?php echo htmlspecialchars($telefone_usuario); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($email_usuario); ?></p>
            <p><strong>Atenção:</strong> O abrigo tem um prazo de 30 dias para cancelar a adoção caso o adotante desista ou não compareça na instituição.</p>
            <form id="aprovarForm" method="post">
                <input type="hidden" name="solicitacao_id" value="<?php echo htmlspecialchars($solicitacao_id); ?>">
                <input type="hidden" name="acao" value="Okaprovar">
                <button type="submit" id="confirmarAprovacao">OK</button>
            </form>
        </div>
    </div>

    <div id="container">
        <h1>Solicitação de Adoção</h1>
        <div class="informacoes-adotante">
            <h2>Informações do Adotante</h2>
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
                <h2>Detalhes do Adotante</h2>
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

        <p id="dt_solic">Solicitação feita em <?php echo date('d/m/Y', strtotime($detalhes_solicitacao['data_inicio'])); ?></p>
        
        <div class="botoes-acoes">
            <form action="" method="post">
                <input type="hidden" name="solicitacao_id" value="<?php echo htmlspecialchars($solicitacao_id); ?>">
                <a id="voltar" href="#" onclick="history.back();">VOLTAR</a>
                <button type="submit" name="acao" value="aprovar" id="aprovar">APROVAR</button>
                <button type="submit" name="acao" value="recusar" id="recusar">RECUSAR</button>
            </form>
        </div>
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

        document.getElementById('aprovar').onclick = function(event) {
            event.preventDefault();
            document.getElementById('msgAprovacao').style.display = 'block';
        };

        window.onclick = function(event) {
            if (event.target == document.getElementById('msgAprovacao')) {
                document.getElementById('msgAprovacao').style.display = 'none';
            }
        };
    </script>
</body>
</html>
