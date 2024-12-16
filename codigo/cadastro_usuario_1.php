<?php
session_start();
include_once 'config.php';

function emailExists($email, $conn) {
    $query = "SELECT id FROM usuario WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
}

$nome = $email = $senha = $confirma_senha = "";
$erros = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $confirma_senha = $_POST['confirma_senha'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "O email não é válido.";
    } elseif (emailExists($email, $conn)) {
        $erros[] = "O email já está em uso.";
    }
    if ($senha !== $confirma_senha) {
        $erros[] = "As senhas não coincidem.";
    }

    if (empty($erros)) {
        $_SESSION['nome'] = $nome;
        $_SESSION['email'] = $email;
        $_SESSION['senha'] = $senha;
        header("Location: cadastro_usuario_2.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuário - Etapa 1</title>
    <link rel="stylesheet" href="estilo/cadastro.css">
</head>
<body>
    <a href="index.php">    
        <img id="logo" src="imagem/logo.png" alt="Logo Quatro Patas">
    </a>    
    <div id="container">
        <h2>1 - Cadastro de Usuário</h2>
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
            <input type="text" id="nome" name="nome" placeholder="Nome Completo" required value="<?php echo htmlspecialchars($nome); ?>"><br>
            <input type="email" id="email" name="email" placeholder="Email" required value="<?php echo htmlspecialchars($email); ?>"><br>
            <input type="password" id="senha" name="senha" placeholder="Senha" required value="<?php echo htmlspecialchars($senha); ?>"><br>
            <input type="password" id="confirma_senha" name="confirma_senha" placeholder="Confirme a Senha" required value="<?php echo htmlspecialchars($confirma_senha); ?>"><br>
            <div class="botoes">
                <a id="voltar" href="#" onclick="history.back();">VOLTAR</a>
                <button type="submit">PRÓXIMO</button>
            </div>
        </form>
    </div>
</body>
</html>
