<?php
session_start();
include_once 'config.php';
include_once 'Abrigo.php';

if (!isset($_SESSION['nome']) || !isset($_SESSION['cnpj_cpf']) || !isset($_SESSION['email']) || !isset($_SESSION['telefone']) ||
    !isset($_SESSION['cep']) || !isset($_SESSION['rua']) || !isset($_SESSION['numero']) || !isset($_SESSION['bairro']) || !isset($_SESSION['cidade']) || !isset($_SESSION['estado'])) {
    header("Location: cadastro_abrigo_1.php");
    exit();
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $senha = trim($_POST['senha']);
    $confirmar_senha = trim($_POST['confirmar_senha']);
    $site = trim($_POST['site']);

    if (empty($senha)) {
        $errors[] = "Senha é obrigatória.";
    }

    if ($senha !== $confirmar_senha) {
        $errors[] = "As senhas não coincidem.";
    }

    if (empty($errors)) {
        $_SESSION['senha'] = $senha;
        $_SESSION['site'] = $site;

        $abrigo = new Abrigo($conn);
        $abrigo->nome = $_SESSION['nome'];
        $abrigo->cnpj_cpf = $_SESSION['cnpj_cpf'];
        $abrigo->email = $_SESSION['email'];
        $abrigo->telefone = $_SESSION['telefone'];
        $abrigo->cep = $_SESSION['cep'];
        $abrigo->rua = $_SESSION['rua'];
        $abrigo->numero = $_SESSION['numero'];
        $abrigo->complemento = $_SESSION['complemento'];
        $abrigo->bairro = $_SESSION['bairro'];
        $abrigo->cidade = $_SESSION['cidade'];
        $abrigo->estado = $_SESSION['estado'];
        $abrigo->senha = $senha;
        $abrigo->site = $site;

        if ($abrigo->create()) {
            echo "<script>
            alert('Abrigo/Canil cadastrado com sucesso!');
            setTimeout(function(){
                window.location.href = 'login.php';
            }, 0);
          </script>";
        } else {
            echo "Erro ao cadastrar o abrigo.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Abrigos e Canis - Etapa 3</title>
    <link rel="stylesheet" href="estilo/cadastro.css">
</head>
<body>        
    <a href="index.php">
        <img id="logo" src="imagem/logo.png" alt="Logo Quatro Patas">
    </a>       

    <div id="container">
        <h2>3 - Cadastro de Abrigos e Canis</h2>
        <?php if (!empty($errors)): ?>
            <div id="erro">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <input type="password" id="senha" name="senha" placeholder="Senha" required maxlength="255"><br>
            <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Confirmar Senha" required maxlength="255"><br>
            <input type="url" id="site" name="site" placeholder="Link (Site/Instagram/WhatsApp)" maxlength="255"><br>
            <div class="botoes">
                <a id="voltar" href="#" onclick="history.back();">VOLTAR</a>
                <button type="submit">CADASTAR</button>
            </div>
        </form>
    </div>
</body>
</html>
