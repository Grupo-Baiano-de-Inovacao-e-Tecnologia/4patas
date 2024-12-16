<?php
include_once 'config.php';
include_once 'Abrigo.php';

session_start();
if (!isset($_SESSION['abrigo_id'])) {
    echo "<script>
    alert('ID do abrigo não fornecido.');
    window.location.href = 'login.php';
    </script>";
    exit;
}

function cnpjCpfExists($cnpj_cpf, $conn, $abrigo_id) {
    $sql = "SELECT COUNT(*) FROM abrigo WHERE cnpj_cpf = ? AND id != ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $cnpj_cpf, $abrigo_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count > 0;
}

function emailExistsForOtherAbrigo($conn, $email, $abrigo_id) {
    $sql = "SELECT COUNT(*) FROM abrigo WHERE email = ? AND id != ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $email, $abrigo_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count > 0;
}

if (!isset($_SESSION['abrigo_id'])) {
    echo "ID do abrigo não encontrado na sessão.";
    exit;
}

$abrigo_id = $_SESSION['abrigo_id'];
$abrigo = new Abrigo($conn);
$abrigo->id = $abrigo_id;

if (!$abrigo->readOne()) {
    echo "Erro ao obter dados do abrigo.";
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'editar') {
    $abrigo->nome = $_POST['nome'];
    $abrigo->email = $_POST['email'];
    $abrigo->telefone = $_POST['telefone'];
    $abrigo->cnpj_cpf = $_POST['cnpj_cpf'];
    $abrigo->cep = $_POST['cep'];
    $abrigo->bairro = $_POST['bairro'];
    $abrigo->rua = $_POST['rua'];
    $abrigo->numero = $_POST['numero'];
    $abrigo->complemento = $_POST['complemento'];
    $abrigo->cidade = $_POST['cidade'];
    $abrigo->estado = $_POST['estado'];
    $abrigo->site = $_POST['site'];

    $erros = [];
    if (!preg_match("/^\d{11}$|^\d{14}$/", str_replace(['.', '-', '/'], '', $abrigo->cnpj_cpf))) {
        $erros[] = "O CNPJ/CPF não é válido.";
    } elseif (cnpjCpfExists($abrigo->cnpj_cpf, $conn, $abrigo->id)) {
        $erros[] = "O CNPJ/CPF já está em uso.";
    }

    if (!filter_var($abrigo->email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "O email não é válido.";
    } elseif (emailExistsForOtherAbrigo($conn, $abrigo->email, $abrigo->id)) {
        $erros[] = "O email já está em uso.";
    }

    if (!preg_match("/^\+\d{2} \(\d{2}\) \d{5}-\d{4}$/", $abrigo->telefone)) {
        $erros[] = "O telefone não é válido.";
    }

    if (!empty($_POST['senha']) && !empty($_POST['confirmar_senha'])) {
        if ($_POST['senha'] !== $_POST['confirmar_senha']) {
            $erros[] = "As senhas não coincidem.";
        } else {
            $abrigo->senha = $_POST['senha'];
        }
    }
    
    if (empty($erros)) {
        if ($abrigo->update()) {
            $mensagem = "Dados atualizados com sucesso!";
        } else {
            $mensagem = "Erro ao atualizar os dados.";
        }
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'excluir') {
    if ($abrigo->delete()) {
        $mensagem = "Conta excluída com sucesso!";
        header("Location: index.php");
        exit;
    } else {
        $mensagem = "Erro ao excluir a conta.";
    }
}

$sql = "SELECT COUNT(*) as total FROM solicitacao_adocao WHERE animal_id IN (SELECT id FROM animal WHERE abrigo_id = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $abrigo_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$temSolicitacoes = $row['total'] > 0;

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilo/minha_conta_abrigo.css">
    <link rel="stylesheet" href="estilo/div_cad.css">
    <title>Minha Conta</title>
</head>
<body>
    <header>
        <div id="logo">
            <a href="index.php">
                <img src="imagem/logo.png" alt="Logo 4 Patas">
            </a>
        </div>
        <nav>
            <a href="#" id="abrirDivCadastro">Cadastrar Animal</a>
            <a href="pedidos_adocao.php">Pedidos de Adoção</a>
            <a href="historico_adocao.php">Histórico</a>
            <a href="pagina_abrigo.php">Meus Animais</a>
            <a href="sair.php">Sair</a>
        </nav>
    </header>

    <?php if (!empty($erros) || !empty($mensagem)): ?>
        <div id="mensagens">
        <?php
        if (!empty($erros)) {
            foreach ($erros as $erro) {
                echo '<p class="erro">' . htmlspecialchars($erro) . '</p>';
            }
        } elseif (!empty($mensagem)) {
            echo '<p class="sucesso">' . htmlspecialchars($mensagem) . '</p>';
        }
        ?>
        </div>
    <?php endif; ?>

    <div id="cadastroDiv" class="cadastroContainer" style="display: none;">
        <div class="cadastroConteudoA">
            <p class="cadastroTituloA">SELECIONE O TIPO DE ANIMAL</p>
            <a id="cadastroCao" href="selecionar_tipo.php?tipo=cao">Cão</a>
            <a id="cadastroGato" href="selecionar_tipo.php?tipo=gato">Gato</a>
            <div class="cadastroFecharA">&times;</div>
        </div>
    </div>

    <div class="container">
        <h1>Minhas Informações</h1>
        <form id="form-editar" method="POST" action="">
            <input type="hidden" name="action" value="editar">
            <input type="text" id="nome" name="nome" placeholder="Nome Completo" value="<?php echo htmlspecialchars($abrigo->nome); ?>" required>
            <input type="email" id="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($abrigo->email); ?>" required>
            <input type="text" id="telefone" name="telefone" placeholder="Telefone" value="<?php echo htmlspecialchars($abrigo->telefone); ?>" required maxlength="19">
            <input type="text" id="cnpj_cpf" name="cnpj_cpf" placeholder="CNPJ ou CPF" value="<?php echo htmlspecialchars($abrigo->cnpj_cpf); ?>" required maxlength="18">
            <input type="text" id="cep" name="cep" placeholder="CEP" value="<?php echo htmlspecialchars($abrigo->cep); ?>" required maxlength="9">
            <input type="text" id="bairro" name="bairro" placeholder="Bairro" value="<?php echo htmlspecialchars($abrigo->bairro); ?>" required>
            <input type="text" id="rua" name="rua" placeholder="Rua" value="<?php echo htmlspecialchars($abrigo->rua); ?>" required>
            <input type="text" id="numero" name="numero" placeholder="Número" value="<?php echo htmlspecialchars($abrigo->numero); ?>" required>
            <input type="text" id="complemento" name="complemento" placeholder="Complemento" value="<?php echo htmlspecialchars($abrigo->complemento); ?>">
            <input type="text" id="cidade" name="cidade" placeholder="Cidade" value="<?php echo htmlspecialchars($abrigo->cidade); ?>" required>
            <input type="text" id="estado" name="estado" placeholder="Estado" value="<?php echo htmlspecialchars($abrigo->estado); ?>" required>
            <input type="password" id="senha" name="senha" placeholder="Senha">
            <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Confirmar Senha">
            <input type="url" id="site" name="site" placeholder="Link (Site/Instagram/WhatsApp)" value="<?php echo htmlspecialchars($abrigo->site); ?>" maxlength="255">
        </form>

        <div class="botoes">
            <a id="voltar" href="#" onclick="history.back();">VOLTAR</a>
            <button type="submit" form="form-editar">SALVAR</button>
            <?php if (!$temSolicitacoes): ?>
                <form method="POST" action="" onsubmit="return confirmDelete();">
                    <input type="hidden" name="action" value="excluir">
                    <button type="submit">EXCLUIR</button>
                </form>
            <?php endif; ?>
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

        document.addEventListener('DOMContentLoaded', function () {
            var cnpjCpfInput = document.getElementById('cnpj_cpf');        
            var cepInput = document.getElementById('cep');
            var telefoneInput = document.getElementById('telefone');

            cnpjCpfInput.addEventListener('input', function (e) {
                var value = e.target.value.replace(/\D/g, '');
                var formattedValue = '';

                if (value.length <= 11) { 
                    if (value.length > 3) {
                        formattedValue += value.substring(0, 3) + '.';
                        value = value.substring(3);
                    }
                    if (value.length > 3) {
                        formattedValue += value.substring(0, 3) + '.';
                        value = value.substring(3);
                    }
                    if (value.length > 3) {
                        formattedValue += value.substring(0, 3) + '-';
                        value = value.substring(3);
                    }
                } else {
                    if (value.length > 2) {
                        formattedValue += value.substring(0, 2) + '.';
                        value = value.substring(2);
                    }
                    if (value.length > 3) {
                        formattedValue += value.substring(0, 3) + '.';
                        value = value.substring(3);
                    }
                    if (value.length > 3) {
                        formattedValue += value.substring(0, 3) + '/';
                        value = value.substring(3);
                    }
                    if (value.length > 4) {
                        formattedValue += value.substring(0, 4) + '-';
                        value = value.substring(4);
                    }
                }
                formattedValue += value;
                e.target.value = formattedValue;
            });

            cepInput.addEventListener('input', function (e) {
                var value = e.target.value.replace(/\D/g, '');
                if (value.length <= 8) {
                    value = value.replace(/^(\d{5})(\d)/, '$1-$2');
                    e.target.value = value;
                }
            });

            cepInput.addEventListener('blur', function (e) {
                var cep = e.target.value.replace(/\D/g, '');
                if (cep.length === 8) {
                    fetch('https://viacep.com.br/ws/' + cep + '/json/')
                        .then(response => response.json())
                        .then(data => {
                            if (!('erro' in data)) {
                                document.getElementById('rua').value = data.logradouro;
                                document.getElementById('bairro').value = data.bairro;
                                document.getElementById('cidade').value = data.localidade;
                                document.getElementById('estado').value = data.uf;
                            } else {
                                alert('CEP não encontrado.');
                            }
                        })
                        .catch(error => console.error('Erro ao buscar CEP:', error));
                } else {
                    alert('CEP inválido.');
                }
            });

            telefoneInput.addEventListener('input', function (e) {
                var value = e.target.value.replace(/\D/g, '');
                var formattedValue = '';

                if (!value.startsWith('55')) {
                    value = '55' + value;
                }
                if (value.length > 0) {
                    formattedValue = '+';
                }
                if (value.length > 2) {
                    formattedValue += value.substring(0, 2) + ' (';
                    value = value.substring(2);
                }
                if (value.length > 2) {
                    formattedValue += value.substring(0, 2) + ') ';
                    value = value.substring(2);
                }
                if (value.length > 5) {
                    formattedValue += value.substring(0, 5) + '-';
                    value = value.substring(5);
                }
                formattedValue += value;

                e.target.value = formattedValue;
            });
        });

        function confirmDelete() {
            return confirm("Tem certeza que deseja excluir a conta? Todos os dados, incluindo os animais cadastrados, serão permanentemente excluídos.");
        }
    </script>
</body>
</html>
