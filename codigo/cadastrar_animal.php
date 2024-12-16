<?php
include_once 'config.php';
include_once 'Animal.php';

session_start();
if (!isset($_SESSION['abrigo_id'])) {
    echo "<script>
        alert('ID do abrigo não fornecido.');
        window.location.href = 'login.php';
    </script>";
    exit;
}

$message = "";

$nome = "";
$data_nascimento = "";
$sexo = "";
$castrado = "";
$porte = "";
$descricao = "";
$foto = "";
$tipo = $_SESSION['tipo_animal'];

$abrigo_id = $_SESSION['abrigo_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $data_nascimento = $_POST['data_nascimento'];
    $sexo = $_POST['sexo'];
    $castrado = $_POST['castrado'];
    $porte = trim($_POST['porte']);
    $descricao = trim($_POST['descricao']);
    $foto = $_FILES['foto']['name'];

    $target_dir = "animais/";
    $target_file = $target_dir . basename($foto);

    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
    } else {
        $message .= "<p>Erro ao fazer upload da foto.</p>";
    }

    $erros = [];
    $data_atual = date('Y-m-d');
    $data_maxima = date('Y-m-d', strtotime('-15 years'));
    
    if ($data_nascimento > $data_atual || $data_nascimento < $data_maxima) {
        $erros[] = "A data de nascimento é inválida.";
    }
    
    $sql = "SELECT * FROM animal WHERE nome = ? AND data_nascimento = ? AND sexo = ? AND castrado = ? AND porte = ? AND descricao = ? AND tipo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $nome, $data_nascimento, $sexo, $castrado, $porte, $descricao, $tipo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $erros[] = "Um animal com essas informações já está cadastrado.";
    }

    if (count($erros) == 0) {
        $animal = new Animal($conn);
        $animal->nome = $nome;
        $animal->data_nascimento = $data_nascimento;
        $animal->sexo = $sexo;
        $animal->castrado = $castrado;
        $animal->porte = $porte;
        $animal->descricao = $descricao;
        $animal->foto = $foto;
        $animal->abrigo_id = $abrigo_id;
        $animal->tipo = $tipo; 

        if ($animal->create()) {
            echo "<script>
                    alert('Animal cadastrado com sucesso!');
                    setTimeout(function(){
                        window.location.href = 'pagina_abrigo.php';
                    }, 0);
                  </script>";
        } else {
            echo "<div id='erro'>Erro ao cadastrar animal. Tente novamente.</div>";
        }
    } else {
        foreach ($erros as $erro) {
            $message .= "<div id='erro'>$erro</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilo/cadastrar_animal.css">
    <title>Cadastro de Animal</title>
</head>
<body>
    <header>
        <div id="logo">
            <a href="index.php">
                <img id="logo" src="imagem/logo.png" alt="Logo Quatro Patas">
            </a> 
        </div>
        <nav>
            <a href="minha_conta_abrigo.php">Minha Conta</a>
            <a href="pedidos_adocao.php">Pedidos de Adoção</a>
            <a href="historico_adocao.php">Histórico</a>
            <a href="pagina_abrigo.php">Meus Animais</a>
            <a href="sair.php">Sair</a>
        </nav>
    </header>

    <div id="container">
        <h1>Cadastrar Animal</h1>

        <form action="cadastrar_animal.php" method="POST" enctype="multipart/form-data">
            <div class="formulario">
                <div class="upload-container">
                    <label for="foto" class="upload-label">
                        <img id="uploadPreview" src="imagem/icone_upload.png" alt="Icone upload">
                        <p>Fazer upload</p>
                    </label>
                    <input type="file" id="foto" name="foto" required>
                </div>
                <div class="grupo_formulario">
                    <input type="text" id="nome" name="nome" required placeholder="Nome do Animal" value="<?php echo htmlspecialchars($nome); ?>">
                    <input type="date" id="data_nascimento" name="data_nascimento" required value="<?php echo htmlspecialchars($data_nascimento); ?>">
                </div>
            </div>
            <div class="formulario">
                <div class="upload-placeholder"></div>
                <div class="grupo_formulario">
                    <select id="sexo" name="sexo" required>
                        <option value="" disabled <?php if($sexo == "") echo "selected"; ?>>Sexo</option>
                        <option value="femea" <?php if($sexo == "femea") echo "selected"; ?>>Fêmea</option>
                        <option value="macho" <?php if($sexo == "macho") echo "selected"; ?>>Macho</option>
                    </select>
                    <select id="castrado" name="castrado" required>
                        <option value="" disabled <?php if($castrado == "") echo "selected"; ?>>Castrado/Não</option>
                        <option value="sim" <?php if($castrado == "sim") echo "selected"; ?>>Sim</option>
                        <option value="nao" <?php if($castrado == "nao") echo "selected"; ?>>Não</option>
                    </select>
                    <select id="porte" name="porte" required>
                        <option value="" disabled <?php if($porte == "") echo "selected"; ?>>Porte</option>
                        <?php if ($tipo == 'cao'): ?>
                            <option value="pequeno" <?php if($porte == "pequeno") echo "selected"; ?>>Pequeno - até 35 cm</option>
                            <option value="medio" <?php if($porte == "medio") echo "selected"; ?>>Médio - 35 a 60 cm</option>
                            <option value="grande" <?php if($porte == "grande") echo "selected"; ?>>Grande - mais de 60 cm</option>
                        <?php elseif ($tipo == 'gato'): ?>
                            <option value="pequeno" <?php if($porte == "pequeno") echo "selected"; ?>>Pequeno - até 25 cm</option>
                            <option value="medio" <?php if($porte == "medio") echo "selected"; ?>>Médio - 25 a 35 cm</option>
                            <option value="grande" <?php if($porte == "grande") echo "selected"; ?>>Grande - mais de 35 cm</option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            <div class="formulario">
                <textarea id="descricao" name="descricao" rows="4" maxlength="1000" placeholder="Descrição detalhada sobre o animal"><?php echo htmlspecialchars($descricao); ?></textarea><br>
            </div>
            <button type="submit">CADASTRAR</button>
        </form>
        <?php echo $message; ?>
    </div>
    
    <script>
        document.getElementById('foto').addEventListener('change', function(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.getElementById('uploadPreview');
                output.src = reader.result;
            }
            reader.readAsDataURL(event.target.files[0]);
        });
    </script>
</body>
</html>
