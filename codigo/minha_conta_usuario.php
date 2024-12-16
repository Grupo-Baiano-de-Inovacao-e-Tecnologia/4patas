<?php
include_once 'config.php';
include_once 'Usuario.php';

session_start();

$usuario = new Usuario($conn);
$usuario->id = $_SESSION['user_id'];

if (!isset($_SESSION['user_id'])) {
    echo "<script>
        alert('ID do usuário não fornecido.');
        window.location.href = 'login.php';
    </script>";
    exit;
}

if ($usuario->readOne()) {
}

function hasAdoptionRequests($conn, $userId) {
    $query = "SELECT COUNT(*) FROM solicitacao_adocao WHERE usuario_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    return $count > 0;
}

$temSolicitacoes = hasAdoptionRequests($conn, $usuario->id);

function cpfExistsForOtherUser($conn, $cpf, $userId) {
    $query = "SELECT id FROM usuario WHERE cpf = ? AND id != ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $cpf, $userId);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
}

function emailExistsForOtherUser($conn, $email, $userId) {
    $query = "SELECT id FROM usuario WHERE email = ? AND id != ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $email, $userId);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
}

$mensagem = "";
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'editar') {
        $usuario->nome = trim($_POST['nome']);
        $usuario->email = trim($_POST['email']);
        $usuario->data_nascimento = $_POST['data_nascimento'];
        $usuario->telefone = $_POST['telefone'];
        $usuario->cpf = trim($_POST['cpf']);
        $usuario->cep = $_POST['cep'];
        $usuario->rua = $_POST['rua'];
        $usuario->numero = $_POST['numero'];
        $usuario->complemento = $_POST['complemento'];
        $usuario->bairro = $_POST['bairro'];
        $usuario->cidade = $_POST['cidade'];
        $usuario->estado = $_POST['estado'];

        if (!empty($usuario->cpf)) {
            if (!preg_match("/^\d{3}\.\d{3}\.\d{3}-\d{2}$/", $usuario->cpf)) {
                $erros[] = "O CPF não é válido.";
            } elseif (cpfExistsForOtherUser($conn, $usuario->cpf, $usuario->id)) {
                $erros[] = "O CPF já está em uso por outro usuário.";
            }
        }

        if (!filter_var($usuario->email, FILTER_VALIDATE_EMAIL)) {
            $erros[] = "O email não é válido.";
        } elseif (emailExistsForOtherUser($conn, $usuario->email, $usuario->id)) {
            $erros[] = "O email já está em uso.";
        }

        $nascimentoTimestamp = strtotime($usuario->data_nascimento);
        $idade = (int)date('Y') - (int)date('Y', $nascimentoTimestamp);
        if ($idade < 18) {
            $erros[] = "Você deve ter pelo menos 18 anos.";
        }

        if (!empty($_POST['senha']) && !empty($_POST['confirmar_senha'])) {
            if ($_POST['senha'] !== $_POST['confirmar_senha']) {
                $erros[] = "As senhas não coincidem.";
            } else {
                $usuario->senha = $_POST['senha']; 
            }
        }

        if (!preg_match("/^\+\d{2} \(\d{2}\) \d{5}-\d{4}$/", $usuario->telefone)) {
            $erros[] = "O telefone não é válido.";
        }

        if (empty($erros)) {
            if ($usuario->update()) {
                $mensagem = "Dados atualizados com sucesso!";
            } else {
                $mensagem = "Erro ao atualizar os dados.";
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'excluir') {
        if ($usuario->delete()) {
            $mensagem = "Conta excluída com sucesso!";
            header("Location: index.php");
            exit;
        } else {
            $mensagem = "Erro ao excluir a conta.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilo/minha_conta_usuario.css">
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
            <a href="meus_animais.php">Meus Animais</a>
            <a href="minhas_solicitacoes.php">Minhas Solicitações</a>
            <a href="ver_abrigos.php">Canis e Abrigos</a>
            <a href="pagina_usuario.php">Quero Adotar</a>
            <a href="sair.php">Sair</a>
        </nav>
    </header>

    <?php if (!empty($erros) || !empty($mensagem)): ?>
        <div class="mensagens">
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

    <div class="container">
        <h1>Minhas Informações</h1>
        <form id="form-editar" method="POST" action="">
            <input type="hidden" name="action" value="editar">
            <input type="text" id="nome" name="nome" placeholder="Nome Completo" value="<?php echo htmlspecialchars($usuario->nome); ?>" required>
            <input type="date" id="data_nascimento" name="data_nascimento" value="<?php echo htmlspecialchars($usuario->data_nascimento); ?>" required>
            <input type="email" id="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($usuario->email); ?>" required>
            <input type="text" id="telefone" name="telefone" placeholder="Telefone" value="<?php echo htmlspecialchars($usuario->telefone); ?>" required maxlength="19">
            <input type="text" id="cpf" name="cpf" placeholder="CPF" value="<?php echo htmlspecialchars($usuario->cpf); ?>" maxlength="14">
            <input type="text" id="cep" name="cep" placeholder="CEP" value="<?php echo htmlspecialchars($usuario->cep); ?>" maxlength="9">
            <input type="text" id="bairro" name="bairro" placeholder="Bairro" value="<?php echo htmlspecialchars($usuario->bairro); ?>">
            <input type="text" id="rua" name="rua" placeholder="Rua" value="<?php echo htmlspecialchars($usuario->rua); ?>">
            <input type="text" id="numero" name="numero" placeholder="Número" value="<?php echo htmlspecialchars($usuario->numero); ?>">
            <input type="text" id="complemento" name="complemento" placeholder="Complemento" value="<?php echo htmlspecialchars($usuario->complemento); ?>">
            <input type="text" id="cidade" name="cidade" placeholder="Cidade" value="<?php echo htmlspecialchars($usuario->cidade); ?>">
            <input type="text" id="estado" name="estado" placeholder="Estado" value="<?php echo htmlspecialchars($usuario->estado); ?>">
            <input type="password" id="senha" name="senha" placeholder="Senha">
            <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Confirmar Senha">
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
        document.addEventListener('DOMContentLoaded', function () {
            var cpfInput = document.getElementById('cpf');
            var cepInput = document.getElementById('cep');
            var telefoneInput = document.getElementById('telefone');

            cpfInput.addEventListener('input', function (e) {
                var value = e.target.value.replace(/\D/g, '');
                if (value.length <= 11) {
                    value = value.replace(/(\d{3})(\d)/, '$1.$2');
                    value = value.replace(/(\d{3})(\d)/, '$1.$2');
                    value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                    e.target.value = value;
                }
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
            return confirm("Tem certeza que deseja excluir a conta? Todos os dados, serão permanentemente excluídos.");
        }
    </script>
</body>
</html>
