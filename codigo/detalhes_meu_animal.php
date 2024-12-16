<?php
session_start();
include_once 'config.php';
include_once 'Animal.php';

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

if (!isset($_GET['id'])) {
    echo "<script>
            alert('Animal não encontrado!');
            window.location.href = 'login.php';
          </script>";
    exit;
}

$animal_id = $_GET['id'];
$query = "SELECT a.*, ab.nome AS abrigo_nome FROM animal a 
          JOIN abrigo ab ON a.abrigo_id = ab.id 
          WHERE a.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $animal_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>
            alert('Animal não encontrado!');
            window.location.href = 'login.php';
          </script>";
    exit;
}

$row = $result->fetch_assoc();
$animal = new Animal($conn);
$animal->id = $row['id'];
$animal->data_nascimento = $row['data_nascimento'];
$animal->nome = $row['nome'];
$animal->sexo = $row['sexo'];
$animal->foto = $row['foto'];
$animal->castrado = $row['castrado'];
$animal->porte = $row['porte'];
$animal->descricao = $row['descricao'];
$abrigo_nome = $row['abrigo_nome'];

$idadeFormatada = $animal->calcularIdade();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilo/detalhes_animal.css">
    <title>Detalhes do Meu Animal</title>
</head>
<body>
    <header>
        <div id="logo">
            <a href="index.php">
                <img src="imagem/logo.png" alt="Logo 4 Patas">
            </a>
        </div>
        <nav>
            <a href="minha_conta.php">Minha Conta</a>
            <a href="minhas_solicitacoes.php">Minhas Solicitações</a>
            <a href="ver_abrigos.php">Canis e Abrigos</a>
            <a href="pagina_usuario.php">Quero Adotar</a>
            <a href="sair.php">Sair</a>
        </nav>
    </header>

    <div id="container">
        <h1><?php echo htmlspecialchars($animal->nome); ?></h1>
        
        <div class="animal-detalhes">
            <div class="imagem-container">
                <img id="uploadPreview" src="animais/<?php echo htmlspecialchars($animal->foto); ?>" alt="Foto do Animal">
                <a href="#" id="verImagem">Abrir imagem</a>
            </div>
            <div class="grupo-formulario">
                <div class="linha">
                    <input type="text" id="sexo" name="sexo" readonly value="<?php echo formatarSexo($animal->sexo); ?>">
                    <input type="text" id="castrado" name="castrado" readonly value="<?php echo formatarCastracao($animal->castrado); ?>">
                    <input type="text" id="idade" name="idade" readonly value="<?php echo $idadeFormatada; ?>">
                </div>
                <div class="linha">
                    <input type="text" id="porte" name="porte" readonly value="<?php echo formatarPorte($animal->porte); ?>">
                    <input type="text" id="abrigo" name="abrigo" readonly value="Vim do Abrigo <?php echo htmlspecialchars($abrigo_nome); ?>">
                </div>
                <textarea id="descricao" name="descricao" rows="4" readonly><?php echo htmlspecialchars($animal->descricao); ?></textarea><br>
            </div>
        </div>
        <div class="botoes">
            <a id="voltar" href="#" onclick="history.back();">VOLTAR</a>
        </div>
    </div>

    <div class="abrir" id="abrirImagem">
        <button class="fechar" id="fecharImagem">X</button>
        <img id="imagemAnimal" src="animais/<?php echo htmlspecialchars($animal->foto); ?>" alt="Foto do Animal">
    </div>

    <script>
        document.getElementById('verImagem').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('abrirImagem').style.display = 'flex';
        });

        document.getElementById('fecharImagem').addEventListener('click', function() {
            document.getElementById('abrirImagem').style.display = 'none';
        });
    </script>
</body>
</html>
