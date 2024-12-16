<?php
session_start();

if (!isset($_SESSION['nome']) || !isset($_SESSION['cnpj_cpf']) || !isset($_SESSION['email']) || !isset($_SESSION['telefone'])) {
    header("Location: cadastro_abrigo_1.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cep = trim($_POST['cep']);
    $rua = trim($_POST['rua']);
    $numero = trim($_POST['numero']);
    $complemento = trim($_POST['complemento']);
    $bairro = trim($_POST['bairro']);
    $cidade = trim($_POST['cidade']);
    $estado = trim($_POST['estado']);

    $_SESSION['cep'] = $cep;
    $_SESSION['rua'] = $rua;
    $_SESSION['numero'] = $numero;
    $_SESSION['complemento'] = $complemento;
    $_SESSION['bairro'] = $bairro;
    $_SESSION['cidade'] = $cidade;
    $_SESSION['estado'] = $estado;

    header("Location: cadastro_abrigo_3.php");
    exit();
}

unset($_SESSION['cep']);
unset($_SESSION['rua']);
unset($_SESSION['numero']);
unset($_SESSION['complemento']);
unset($_SESSION['bairro']);
unset($_SESSION['cidade']);
unset($_SESSION['estado']);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Abrigos e Canis - Etapa 2</title>
    <link rel="stylesheet" href="estilo/cadastro.css">

</head>
<body>
    <a href="index.php">    
        <img id="logo" src="imagem/logo.png" alt="Logo Quatro Patas">
    </a>    

    <div id="container2">
        <h2>2 - Cadastro de Abrigos e Canis</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <input type="text" id="cep" name="cep" placeholder="CEP" required maxlength="9" value="<?php echo isset($_SESSION['cep']) ? htmlspecialchars($_SESSION['cep']) : ''; ?>"><br>
            <div class="grupo_formulario">
                <input type="text" id="cidade" name="cidade" placeholder="Cidade" required value="<?php echo isset($_SESSION['cidade']) ? htmlspecialchars($_SESSION['cidade']) : ''; ?>">
                <input type="text" id="estado" name="estado" placeholder="Estado" required maxlength="2" value="<?php echo isset($_SESSION['estado']) ? htmlspecialchars($_SESSION['estado']) : ''; ?>">
            </div>
            <div class="grupo_formulario">
                <input type="text" id="rua" name="rua" placeholder="Rua" required value="<?php echo isset($_SESSION['rua']) ? htmlspecialchars($_SESSION['rua']) : ''; ?>">
                <input type="text" id="numero" name="numero" placeholder="Número" required maxlength="10" value="<?php echo isset($_SESSION['numero']) ? htmlspecialchars($_SESSION['numero']) : ''; ?>">
            </div>
            <input type="text" id="bairro" name="bairro" placeholder="Bairro" required value="<?php echo isset($_SESSION['bairro']) ? htmlspecialchars($_SESSION['bairro']) : ''; ?>"><br>
            <input type="text" id="complemento" name="complemento" placeholder="Complemento (opcional)" value="<?php echo isset($_SESSION['complemento']) ? htmlspecialchars($_SESSION['complemento']) : ''; ?>"><br>
            <div class="botoes">
                <a id="voltar" href="#" onclick="history.back();">VOLTAR</a>
                <button type="submit">PRÓXIMO</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var cepInput = document.getElementById('cep');

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
