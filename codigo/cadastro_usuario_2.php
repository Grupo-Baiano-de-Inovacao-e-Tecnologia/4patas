<?php
session_start();
include_once 'config.php';
include_once 'Usuario.php';

if (!isset($_SESSION['nome']) || !isset($_SESSION['email']) || !isset($_SESSION['senha'])) {
    header("Location: cadastro_usuario_1.php");
    exit();
}

$erros = [];

function verificaCep($cep) {
    $url = "https://viacep.com.br/ws/$cep/json/";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    return isset($data['cep']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data_nascimento = $_POST['data_nascimento'];
    $telefone = $_POST['telefone'];
    $cep = $_POST['cep'];

    $data_atual = new DateTime();
    $data_nasc = new DateTime($data_nascimento);
    $idade = $data_atual->diff($data_nasc)->y;

    if ($idade < 18) {
        $erros[] = "Você deve ter pelo menos 18 anos para se cadastrar.";
    }

    if (strlen($telefone) != 19) {
        $erros[] = "O telefone não é válido.";
    }

    if (!empty($cep) && !verificaCep($cep)) {
        $erros[] = "O CEP não é válido.";
    }

    if (empty($erros)) {
        $usuario = new Usuario($conn);
        $usuario->nome = $_SESSION['nome'];
        $usuario->email = $_SESSION['email'];
        $usuario->senha = $_SESSION['senha'];
        $usuario->data_nascimento = $data_nascimento;
        $usuario->telefone = $telefone;
        $usuario->cep = $cep;

        if ($usuario->create()) {
            echo "<script>
                    alert('Usuário cadastrado com sucesso!');
                    setTimeout(function(){
                        window.location.href = 'login.php';
                    }, 0);
                  </script>";
        } else {
            echo "Erro ao cadastrar o usuário.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuário - Etapa 2</title>
    <link rel="stylesheet" href="estilo/cadastro.css">
</head>
<body>
    <a href="index.php">    
        <img id="logo" src="imagem/logo.png" alt="Logo Quatro Patas">
    </a>    
    <div id="container">
        <h2>Cadastro de Usuário</h2>
        <?php
        if (!empty($erros)) {
            echo '<div id="erro">';
            foreach ($erros as $erro) {
                echo "<p>$erro</p>";
            }
            echo '</div>';
        }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <input type="date" id="data_nascimento" name="data_nascimento" placeholder="Data de Nascimento" required value="<?php echo isset($data_nascimento) ? htmlspecialchars($data_nascimento) : ''; ?>"><br>
            <input type="tel" id="telefone" name="telefone" placeholder="Telefone" required maxlength="19" value="<?php echo isset($telefone) ? htmlspecialchars($telefone) : ''; ?>"><br>
            <input type="text" id="cep" name="cep" placeholder="CEP (Opcional)" maxlength="9" value="<?php echo isset($cep) ? htmlspecialchars($cep) : ''; ?>"><br>
            <div class="botoes">
                <a id="voltar" href="#" onclick="history.back();">VOLTAR</a>
                <button type="submit">CADASTAR</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var telefoneInput = document.getElementById('telefone');
            var cepInput = document.getElementById('cep');

            telefoneInput.addEventListener('input', function (e) {
                var value = e.target.value.replace(/\D/g, '');
                var formattedValue = '';

                if (!value.startsWith('55')) {
                    value = '' + value;
                }

                formattedValue = '+55 ';

                if (value.length > 2) {
                    formattedValue += '(' + value.substring(2, 4) + ') ';
                    value = value.substring(4);
                }

                if (value.length > 5) {
                    formattedValue += value.substring(0, 5) + '-';
                    value = value.substring(5);
                }

                formattedValue += value; 

                e.target.value = formattedValue; 
            });

            cepInput.addEventListener('input', function (e) {
                var value = e.target.value.replace(/\D/g, '');
                var formattedValue = value.substring(0, 5);
                
                if (value.length > 5) {
                    formattedValue += '-' + value.substring(5);
                }

                e.target.value = formattedValue;
            });
        });
    </script>
</body>
</html>
