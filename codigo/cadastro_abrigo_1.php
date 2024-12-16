<?php
session_start();
include_once 'config.php';

function cnpjCpfExists($cnpj_cpf, $conn) {
    $query = "SELECT id FROM abrigo WHERE cnpj_cpf = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $cnpj_cpf);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
}

function emailExists($email, $conn) {
    $query = "SELECT id FROM abrigo WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
}

$nome = $cnpj_cpf = $email = $telefone = "";
$erros = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $cnpj_cpf = trim($_POST['cnpj_cpf']);
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);

    if (!preg_match("/^\d{11}$|^\d{14}$/", str_replace(['.', '-', '/'], '', $cnpj_cpf))) {
        $erros[] = "O CNPJ/CPF não é válido.";
    } elseif (cnpjCpfExists($cnpj_cpf, $conn)) {
        $erros[] = "O CNPJ/CPF já está em uso.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "O email não é válido.";
    } elseif (emailExists($email, $conn)) {
        $erros[] = "O email já está em uso.";
    }
    if (!preg_match("/^\+\d{2} \(\d{2}\) \d{5}-\d{4}$/", $telefone)) {
        $erros[] = "O telefone não é válido.";
    }

    if (empty($erros)) {
        $_SESSION['nome'] = $nome;
        $_SESSION['cnpj_cpf'] = $cnpj_cpf;
        $_SESSION['email'] = $email;
        $_SESSION['telefone'] = $telefone;
        header("Location: cadastro_abrigo_2.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Abrigos e Canis - Etapa 1</title>
    <link rel="stylesheet" href="estilo/cadastro.css">
</head>
<body>
    <a href="index.php">    
        <img id="logo" src="imagem/logo.png" alt="Logo Quatro Patas">
    </a>    

    <div id="container">
        <h2>1 - Cadastro de Abrigos e Canis</h2>
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
            <input type="text" id="nome" name="nome" placeholder="Nome da Instituição" required maxlength="100" value="<?php echo htmlspecialchars($nome); ?>"><br>
            <input type="text" id="cnpj_cpf" name="cnpj_cpf" placeholder="CNPJ ou CPF" required maxlength="18" value="<?php echo htmlspecialchars($cnpj_cpf); ?>"><br>
            <input type="email" id="email" name="email" placeholder="Email" required maxlength="100" value="<?php echo htmlspecialchars($email); ?>"><br>
            <input type="text" id="telefone" name="telefone" placeholder="Telefone" required maxlength="19" value="<?php echo htmlspecialchars($telefone); ?>"><br>
            <div class="botoes">
                <a id="voltar" href="#" onclick="history.back();">VOLTAR</a>
                <button type="submit">PRÓXIMO</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var telefoneInput = document.getElementById('telefone');
            var cnpjCpfInput = document.getElementById('cnpj_cpf');

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
        });
    </script>
</body>
</html>
