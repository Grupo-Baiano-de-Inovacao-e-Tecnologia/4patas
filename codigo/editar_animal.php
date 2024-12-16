<?php
session_start();
include_once 'config.php';
include_once 'Animal.php';

if (isset($_GET['id'])) {
    $animalId = $_GET['id'];

    $sql = "SELECT * FROM animal WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $animalId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $animal = $result->fetch_assoc();
    } else {
        echo "<script>
        alert('Animal não encontrado.');
        window.location.href = 'login.php';
        </script>";
        exit();
    }

    $sqlCheckSolicitacao = "SELECT * FROM solicitacao_adocao WHERE animal_id = ? AND status = 'analise'";
    $stmtCheckSolicitacao = $conn->prepare($sqlCheckSolicitacao);
    $stmtCheckSolicitacao->bind_param('i', $animalId);
    $stmtCheckSolicitacao->execute();
    $solicitacaoResult = $stmtCheckSolicitacao->get_result();
    $temSolicitacaoEmAnalise = $solicitacaoResult->num_rows > 0;
} else {
    echo "<script>
    alert('ID de animal não fornecido.'); 
    window.location.href = 'login.php';
    </script>";
    exit();
}

$erros = [];
$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['action'] == 'editar') {

        $nome = $_POST['nome'];
        $data_nascimento = $_POST['data_nascimento'];
        $sexo = $_POST['sexo'];
        $castrado = $_POST['castrado'];
        $porte = $_POST['porte'];
        $descricao = $_POST['descricao'];

        $data_atual = date('Y-m-d');
        $data_maxima = date('Y-m-d', strtotime('-15 years'));
        
        if ($data_nascimento > $data_atual || $data_nascimento < $data_maxima) {
            $erros[] = "A data de nascimento é inválida.";
        }
    
        if ($_FILES['foto']['name']) {
            $foto = time() . '_' . $_FILES['foto']['name'];
            $target_dir = "animais/";
            $target_file = $target_dir . basename($foto);

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
                $sql = "UPDATE animal SET nome = ?, data_nascimento = ?, sexo = ?, castrado = ?, porte = ?, descricao = ?, foto = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('sssssssi', $nome, $data_nascimento, $sexo, $castrado, $porte, $descricao, $foto, $animalId);
            } else {
                $erros[] = "Erro ao fazer upload da foto.";
            }
        } else {
            $sql = "UPDATE animal SET nome = ?, data_nascimento = ?, sexo = ?, castrado = ?, porte = ?, descricao = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssssssi', $nome, $data_nascimento, $sexo, $castrado, $porte, $descricao, $animalId);
        }

        if (empty($erros)) {
            if ($stmt->execute()) {
                $mensagem = "Dados atualizados com sucesso!";
            } else {
                $erros[] = "Erro ao atualizar os dados.";
            }
        }
    } elseif ($_POST['action'] == 'excluir') {
        $sql = "DELETE FROM animal WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $animalId);

        if ($stmt->execute()) {
            $mensagem = "Animal excluído com sucesso.";
            header("Location: pagina_abrigo.php");
            exit();
        } else {
            $erros[] = "Erro ao excluir o animal.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilo/editar_animal.css">
    <title>Editar Animal</title>
</head>
<body>
    <header>
        <div id="logo">
            <a href="index.php">
                <img src="imagem/logo.png" alt="Logo 4 Patas">
            </a>
        </div>
        <nav>
            <a href="minha_conta_abrigo.php">Minha Conta</a>
            <a href="#" id="abrirDivCadastro">Cadastrar Animal</a>
            <a href="pedidos_adocao.php">Pedidos de Adoção</a>
            <a href="historico_adocao.php">Histórico</a>
            <a href="sair.php">Sair</a>
        </nav>
    </header>
    
    <div id="cadastroDiv" class="cadastroContainer" style="display: none;">
        <div class="cadastroConteudoA">
            <p class="cadastroTituloA">SELECIONE O TIPO DE ANIMAL</p>
            <a id="cadastroCao" href="selecionar_tipo.php?tipo=cao">Cão</a>
            <a id="cadastroGato" href="selecionar_tipo.php?tipo=gato">Gato</a>
            <div class="cadastroFecharA">&times;</div>
        </div>
    </div>

    <?php if (!empty($erros) || !empty($mensagem)): ?>
        <div id="mensagens">
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

    <div id="container">
        <h1>Informações do Animal</h1>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="formulario">
                <div class="upload-container">
                <label for="foto" class="upload-label">
                    <img id="uploadPreview" src="animais/<?php echo htmlspecialchars($animal['foto']); ?>" alt="Foto do Animal">
                    <p>Fazer upload</p>
                </label>

                    <input type="file" id="foto" name="foto">
                </div>
                <div class="grupo_formulario">
                    <input type="text" id="nome" name="nome" value="<?php echo $animal['nome']; ?>" required placeholder="Nome do Animal">
                    <input type="date" id="data_nascimento" name="data_nascimento" value="<?php echo $animal['data_nascimento']; ?>" required>
                </div>
            </div>
            <div class="formulario">
                <div class="upload-placeholder"></div>
                <div class="grupo_formulario">
                    <select id="sexo" name="sexo" required>
                        <option value="" disabled>Sexo</option>
                        <option value="femea" <?php echo ($animal['sexo'] == 'femea') ? 'selected' : ''; ?>>Fêmea</option>
                        <option value="macho" <?php echo ($animal['sexo'] == 'macho') ? 'selected' : ''; ?>>Macho</option>
                    </select>
                    <select id="castrado" name="castrado" required>
                        <option value="" disabled>Castrado/Não</option>
                        <option value="sim" <?php echo ($animal['castrado'] == 'sim') ? 'selected' : ''; ?>>Sim</option>
                        <option value="nao" <?php echo ($animal['castrado'] == 'nao') ? 'selected' : ''; ?>>Não</option>
                    </select>
                    <select id="porte" name="porte" required>
                        <option value="" disabled>Porte</option>
                        <?php if ($animal['tipo'] == 'cao'): ?>
                            <option value="pequeno" <?php echo ($animal['porte'] == 'pequeno') ? 'selected' : ''; ?>>Pequeno - até 35 cm</option>
                            <option value="medio" <?php echo ($animal['porte'] == 'medio') ? 'selected' : ''; ?>>Médio - 35 a 60 cm</option>
                            <option value="grande" <?php echo ($animal['porte'] == 'grande') ? 'selected' : ''; ?>>Grande - mais de 60 cm</option>
                        <?php elseif ($animal['tipo'] == 'gato'): ?>
                            <option value="pequeno" <?php echo ($animal['porte'] == 'pequeno') ? 'selected' : ''; ?>>Pequeno - até 25 cm</option>
                            <option value="medio" <?php echo ($animal['porte'] == 'medio') ? 'selected' : ''; ?>>Médio - 25 a 35 cm</option>
                            <option value="grande" <?php echo ($animal['porte'] == 'grande') ? 'selected' : ''; ?>>Grande - mais de 35 cm</option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            <div class="formulario">
                <textarea id="descricao" name="descricao" rows="4" maxlength="1000" required placeholder="Descrição detalhada sobre o animal"><?php echo $animal['descricao']; ?></textarea><br>
            </div>

            <div class="botoes">
                <a id="voltar" href="pagina_abrigo.php">VOLTAR</a>
                <button type="submit" name="action" value="editar">SALVAR</button>
                <?php if (!$temSolicitacaoEmAnalise): ?>
                    <button type="submit" name="action" value="excluir" onclick="return confirm('Tem certeza que deseja excluir este animal?')">EXCLUIR</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <script>
        document.getElementById('abrirDivCadastro').onclick = function(event) {
            event.preventDefault();
            document.getElementById('cadastroDiv').style.display = 'flex';
        };

        document.querySelector('.cadastroFecharA').onclick = function() {
            document.getElementById('cadastroDiv').style.display = 'none';
        };

        window.onclick = function(event) {
            if (event.target == document.getElementById('cadastroDiv')) {
                    document.getElementById('cadastroDiv').style.display = 'none';
            }
        };
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
