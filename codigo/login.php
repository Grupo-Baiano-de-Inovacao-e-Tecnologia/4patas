<?php
session_start();
include_once 'config.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, nome, senha, 'usuario' AS tipo FROM usuario WHERE email = ? UNION SELECT id, nome, senha, 'abrigo' AS tipo FROM abrigo WHERE email = ?");
        $stmt->bind_param('ss', $email, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && $user['senha'] === $senha) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nome'];
            $_SESSION['user_type'] = $user['tipo'];

            if ($user['tipo'] == 'usuario') {
                header("Location: pagina_usuario.php");
            } else {
                $_SESSION['abrigo_id'] = $user['id'];
                header("Location: pagina_abrigo.php");
            }
            exit();
        } else {
            $errors[] = "Email ou senha incorretos.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="estilo/login.css">
    <link rel="stylesheet" href="estilo/div_cad.css">
</head>
<body>
    <header>
        <a href="index.php">
            <img id="logo" src="imagem/logo.png" alt="Logo Quatro Patas">
        </a>        
        <nav>    
            <a href="#" onclick="history.back();">Voltar</a>
        </nav>
    </header>

    <?php if (!empty($errors)): ?>
        <div id="erro">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div id="divCadastro" class="cadastroContainer" style="display: none;">
        <div class="cadastroConteudo">
            <p class="cadastroTitulo">SELECIONE O TIPO DE CADASTRO</p>
            <a id="cadastroUsuario" href="cadastro_usuario_1.php">Usuário</a>
            <a id="cadastroAbrigo" href="cadastro_abrigo_1.php">Abrigo/Canil</a>
            <div class="cadastroFechar">&times;</div>
        </div>
    </div>

    <div id="icone"></div>
    <div id="container">
        <h2>LOGIN</h2>
        <p>Coloque suas informações de acesso ao sistema</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <input type="email" id="email" name="email" placeholder="Email" required maxlength="255"><br>
            <input type="password" id="senha" name="senha" placeholder="Senha" required maxlength="255"><br>
            <a id="abrirDivCadastro" href="#">Não tenho cadastro</a>
            <button type="submit">ENTRAR</button>
        </form>
    </div>

    <script>
        document.getElementById('abrirDivCadastro').onclick = function(event) {
            event.preventDefault();
            document.getElementById('divCadastro').style.display = 'flex';
        };

        document.querySelector('.cadastroFechar').onclick = function() {
            document.getElementById('divCadastro').style.display = 'none';
        };

        window.onclick = function(event) {
            var modal = document.getElementById('divCadastro');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        };
    </script>
</body>
</html>
