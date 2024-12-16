<?php
session_start();
include_once 'config.php';
include_once 'Usuario.php';
include_once 'Animal.php';
include_once 'SolicitacaoAdocao.php';

$error_message = '';
$success_message = '';

if (!isset($_GET['animal_id']) || !isset($_SESSION['user_id'])) {
    echo "<script>
        alert('ID do animal ou usuário não fornecido.');
        window.location.href = 'login.php';
    </script>";
    exit;
}

$usuario_id = $_SESSION['user_id'];
$animal_id = $_GET['animal_id'];

$usuario = new Usuario($conn);
$animal = new Animal($conn);
$solicitacao = new SolicitacaoAdocao($conn);

$usuario->id = $usuario_id;
if (!$usuario->readOne()) {
    echo "Erro ao obter dados do usuário.";
    exit();
}

$animal->id = $animal_id;
if (!$animal->readOne()) {
    echo "Erro ao obter dados do animal.";
    exit();
}

function cpfExistsForOtherUser($conn, $cpf, $userId) {
    $query = "SELECT id FROM usuario WHERE cpf = ? AND id != ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $cpf, $userId);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
}

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $data_nascimento = $_POST['data_nascimento'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $cpf = $_POST['cpf'];
    $cep = $_POST['cep'];
    $rua = $_POST['rua'];
    $bairro = $_POST['bairro'];
    $cidade = $_POST['cidade'];
    $estado = $_POST['estado'];
    $numero = $_POST['numero'];
    $complemento = $_POST['complemento'];
    $formulario = $_POST['formulario'];

    if (!preg_match("/^\d{3}\.\d{3}\.\d{3}-\d{2}$/", $cpf)) {
        $error_message = "O CPF não é válido.";
    } elseif (cpfExistsForOtherUser($conn, $cpf, $usuario_id)) {
        $error_message = "O CPF já está em uso por outro usuário.";
    }

    if (empty($error_message)) {
        $usuario->nome = $nome;
        $usuario->data_nascimento = $data_nascimento;
        $usuario->email = $email;
        $usuario->telefone = $telefone;
        $usuario->cpf = $cpf;
        $usuario->cep = $cep;
        $usuario->rua = $rua;
        $usuario->bairro = $bairro;
        $usuario->cidade = $cidade;
        $usuario->estado = $estado;
        $usuario->numero = $numero;
        $usuario->complemento = $complemento;

        if ($usuario->update()) {
            $solicitacao->usuario_id = $usuario_id;
            $solicitacao->animal_id = $animal_id;
            $solicitacao->nome_usuario = $usuario->nome;
            $solicitacao->email_usuario = $usuario->email;
            $solicitacao->telefone_usuario = $usuario->telefone;
            $solicitacao->cpf_usuario = $usuario->cpf;
            $solicitacao->cep_usuario = $usuario->cep;
            $solicitacao->idade_usuario = (int)date_diff(date_create($usuario->data_nascimento), date_create('today'))->y;
            $solicitacao->nome_animal = $animal->nome;
            $solicitacao->idade_animal = (int)date_diff(date_create($animal->data_nascimento), date_create('today'))->y;
            $solicitacao->sexo_animal = $animal->sexo;
            $solicitacao->formulario = $formulario;

            if ($solicitacao->solicitacaoExists()) {
                $error_message = "Você já solicitou a adoção deste animal.";
            } else {
                if ($solicitacao->create()) {
                    echo "<script>
                            alert('Solicitação enviada com sucesso!');
                            setTimeout(function(){
                                window.location.href = 'minhas_solicitacoes.php';
                            }, 0);
                            </script>";
                } else {
                    $error_message = "Erro ao enviar solicitação. Tente novamente.";
                }
            }
        }
    }
} else {
    $nome = $usuario->nome;
    $data_nascimento = $usuario->data_nascimento;
    $email = $usuario->email;
    $telefone = $usuario->telefone;
    $cpf = $usuario->cpf;
    $cep = $usuario->cep;
    $rua = $usuario->rua;
    $bairro = $usuario->bairro;
    $cidade = $usuario->cidade;
    $estado = $usuario->estado;
    $numero = $usuario->numero;
    $complemento = $usuario->complemento;
    $formulario = '';
}

$camposUsuario = [
    'nome' => !empty($usuario->nome),
    'data_nascimento' => !empty($usuario->data_nascimento),
    'cpf' => !empty($usuario->cpf),
    'email' => !empty($usuario->email),
    'telefone' => !empty($usuario->telefone),
    'cep' => !empty($usuario->cep),
    'rua' => !empty($usuario->rua),
    'bairro' => !empty($usuario->bairro),
    'cidade' => !empty($usuario->cidade),
    'estado' => !empty($usuario->estado),
    'numero' => !empty($usuario->numero),
    'complemento' => !empty($usuario->complemento)
];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilo/solicitar_adocao.css">
    <title>Formulário de Adoção</title>
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
            <a href="minhas_solicitacoes.php">Minhas Solicitações</a>
            <a href="ver_abrigos.php">Canis e Abrigos</a>
            <a href="sair.php">Sair</a>
        </nav>
    </header>

    <div class="container">
        <h1>Quero Adotar <img src="imagem/icone_pata.png" alt="Símbolo de pata"></h1>

        <?php if ($error_message): ?>
        <div id="erro">
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>
        <?php if ($success_message): ?>
        <div id="sucesso">
            <?php echo $success_message; ?>
        </div>
        <?php else: ?>

        <div class="container-azul">
            <form method="POST" action="">
                <div class="informacoes-pessoais">
                    <h2>Informações Pessoais</h2>
                    <p>Complete os campos abaixo com suas informações para prosseguir com a adoção.</p>
                    <div class="grupo_formulario">
                    <input type="text" id="nome" name="nome" placeholder="Nome Completo" value="<?php echo htmlspecialchars($nome); ?>" <?php echo $camposUsuario['nome'] ? 'readonly' : 'required'; ?>>
                    <input type="date" id="data_nascimento" name="data_nascimento" value="<?php echo htmlspecialchars($data_nascimento); ?>" <?php echo $camposUsuario['data_nascimento'] ? 'readonly' : 'required'; ?>>
                    <input type="email" id="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" <?php echo $camposUsuario['email'] ? 'readonly' : 'required'; ?>>
                    <input type="text" id="telefone" name="telefone" placeholder="Telefone" value="<?php echo htmlspecialchars($telefone); ?>" <?php echo $camposUsuario['telefone'] ? 'readonly' : 'required'; ?>>
                    <input type="text" id="cpf" name="cpf" placeholder="CPF" value="<?php echo htmlspecialchars($cpf); ?>" maxlength="14"  <?php echo $camposUsuario['cpf'] ? 'readonly' : 'required'; ?>>
                    <input type="text" id="cep" name="cep" placeholder="CEP" value="<?php echo htmlspecialchars($cep); ?>" maxlength="9"<?php echo $camposUsuario['cep'] ? 'readonly' : 'required'; ?>>
                    <input type="text" id="bairro" name="bairro" placeholder="Bairro" value="<?php echo htmlspecialchars($bairro); ?>" <?php echo $camposUsuario['bairro'] ? 'readonly' : 'required'; ?>>
                    <input type="text" id="rua" name="rua" placeholder="Rua" value="<?php echo htmlspecialchars($rua); ?>" <?php echo $camposUsuario['rua'] ? 'readonly' : 'required'; ?>>
                    <input type="text" id="numero" name="numero" placeholder="Número" value="<?php echo htmlspecialchars($numero); ?>" <?php echo $camposUsuario['numero'] ? 'readonly' : 'required'; ?>>
                    <input type="text" id="complemento" name="complemento" placeholder="Complemento" value="<?php echo htmlspecialchars($complemento); ?>" <?php echo $camposUsuario['complemento'] ? 'readonly' : ''; ?>>
                    <input type="text" id="cidade" name="cidade" placeholder="Cidade" value="<?php echo htmlspecialchars($cidade); ?>" <?php echo $camposUsuario['cidade'] ? 'readonly' : 'required'; ?>>
                    <input type="text" id="estado" name="estado" placeholder="Estado" value="<?php echo htmlspecialchars($estado); ?>" <?php echo $camposUsuario['estado'] ? 'readonly' : 'required'; ?>>
                    </div>
                </div>
                <br>

                <div class="informacoes-animal">
                    <h2 id="h2-2">Informações do Animal</h2>
                    <div class="grupo_formulario">
                    <input type="text" id="nome_animal" name="nome_animal" placeholder="Nome do Animal" value="<?php echo htmlspecialchars($animal->nome); ?>" readonly>
                    <input type="text" id="idade" name="idade_animal" placeholder="Idade do Animal" value="<?php echo htmlspecialchars($animal->calcularIdade()); ?>" readonly>
                    <input type="text" id="sexo" name="sexo_animal" placeholder="Sexo do Animal" value="<?php echo htmlspecialchars(formatarSexo($animal->sexo)); ?>" readonly>
                    <input type="text" id="castrado" name="castrado" placeholder="Castrado/Não" value="<?php echo htmlspecialchars(formatarCastracao($animal->castrado)); ?>" readonly>
                    <input type="text" id="porte" name="porte" placeholder="Porte" value="<?php echo htmlspecialchars(formatarPorte($animal->porte)); ?>" readonly>
                    <textarea id="descricao" name="descricao" rows="4" placeholder="Descrição detalhada sobre o animal" readonly><?php echo htmlspecialchars($animal->descricao); ?></textarea>
                    </div>
                </div>
                <br>

                <div class="formulario">
                    <h2>Formulário de Solicitação</h2>
                    <p>Preencha os campos abaixo  para que possamos avaliar sua solicitação de adoção.</p>
                    <textarea id="formulario" name="formulario" rows="4" placeholder="Informações adicionais, como motivo da adoção, estilo de vida, profissão, disponibilidade de cuidar do animal, etc." required></textarea>
                </div>

                <div class="botoes">
                    <button type="submit">ENVIAR</button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
        var cpfInput = document.getElementById('cpf');
        var cepInput = document.getElementById('cep');

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
    });
  </script>
</body>
</html>