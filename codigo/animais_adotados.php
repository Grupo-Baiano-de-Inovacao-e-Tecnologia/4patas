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
 
$abrigo_id = $_SESSION['abrigo_id'];

$filtros = [
    'nome' => '',
    'especie' => '',
    'sexo' => '',
    'porte' => '',
    'idade' => '',
    'castracao' => ''
];

foreach ($filtros as $chave => $valor) {
    if (isset($_GET[$chave]) && !empty($_GET[$chave])) {
        $filtros[$chave] = $_GET[$chave];
    }
}

$condicoes = ["a.abrigo_id = $abrigo_id"];

$subquery = "SELECT a.id FROM animal a
LEFT JOIN solicitacao_adocao s ON a.id = s.animal_id
WHERE s.status = 'Aprovado'";

$condicoes[] = "a.id IN ($subquery)";

if (!empty($filtros['nome'])) {
    $condicoes[] = "a.nome LIKE '%" . $conn->real_escape_string($filtros['nome']) . "%'";
}
if (!empty($filtros['especie'])) {
    $especies = implode("','", array_map([$conn, 'real_escape_string'], (array)$filtros['especie']));
    $condicoes[] = "a.tipo IN ('$especies')";
}
if (!empty($filtros['sexo'])) {
    $sexos = implode("','", array_map([$conn, 'real_escape_string'], (array)$filtros['sexo']));
    $condicoes[] = "a.sexo IN ('$sexos')";
}
if (!empty($filtros['porte'])) {
    $portes = implode("','", array_map([$conn, 'real_escape_string'], (array)$filtros['porte']));
    $condicoes[] = "a.porte IN ('$portes')";
}
if (!empty($filtros['idade'])) {
    $idade_condicoes = [];
    foreach ((array)$filtros['idade'] as $idade) {
        switch ($idade) {
            case '0-1':
                $idade_condicoes[] = "TIMESTAMPDIFF(YEAR, a.data_nascimento, CURDATE()) <= 1";
                break;
            case '1-3':
                $idade_condicoes[] = "TIMESTAMPDIFF(YEAR, a.data_nascimento, CURDATE()) BETWEEN 1 AND 3";
                break;
            case '3-5':
                $idade_condicoes[] = "TIMESTAMPDIFF(YEAR, a.data_nascimento, CURDATE()) BETWEEN 3 AND 5";
                break;
            case '5-8':
                $idade_condicoes[] = "TIMESTAMPDIFF(YEAR, a.data_nascimento, CURDATE()) BETWEEN 5 AND 8";
                break;
            case '8+':
                $idade_condicoes[] = "TIMESTAMPDIFF(YEAR, a.data_nascimento, CURDATE()) >= 8";
                break;
        }
    }
    if (!empty($idade_condicoes)) {
        $condicoes[] = '(' . implode(' OR ', $idade_condicoes) . ')';
    }
}
if (!empty($filtros['castracao'])) {
    $castracao = implode("','", array_map([$conn, 'real_escape_string'], (array)$filtros['castracao']));
    $condicoes[] = "a.castrado IN ('$castracao')";
}

$query = "SELECT a.* FROM animal a";
if (!empty($condicoes)) {
    $query .= " WHERE " . implode(" AND ", $condicoes);
}

$result = $conn->query($query);
$animais = [];
while ($row = $result->fetch_assoc()) {
    $animal = new Animal($conn);
    $animal->id = $row['id'];
    $animal->data_nascimento = $row['data_nascimento'];
    $animal->nome = $row['nome'];
    $animal->sexo = $row['sexo'];
    $animal->foto = $row['foto'];
    $animal->abrigo_id = $row['abrigo_id'];
    $animais[] = $animal;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilo/pagina_abrigo.css">
    <link rel="stylesheet" href="estilo/div_cad.css">
    <title>Animais Adotados</title>
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
    
    <div id="container">
        <div id="filtros">
            <form id="filtros_form" method="GET" action="">
                <div class="campos">
                    <h3>Filtros</h3>
                    <button id="limpar" type="reset" onclick="window.location.href='pagina_abrigo.php'">Limpar todos</button>
                    <input type="text" id="nome" placeholder="Pesquisar nome" name="nome" value="<?php echo htmlspecialchars($filtros['nome']); ?>">
                </div>

                <div class="campos">
                    <div class="seta" onclick="toggleCampos(this)">Espécie</div>
                    <div class="campo-oculto">
                        <label><input type="checkbox" name="especie[]" value="cao" <?php echo in_array('cao', (array)$filtros['especie']) ? 'checked' : ''; ?>> Cão</label>
                        <label><input type="checkbox" name="especie[]" value="gato" <?php echo in_array('gato', (array)$filtros['especie']) ? 'checked' : ''; ?>> Gato</label>
                    </div>
                </div>

                <div class="campos">
                    <div class="seta" onclick="toggleCampos(this)">Sexo</div>
                    <div class="campo-oculto">
                        <label><input type="checkbox" name="sexo[]" value="macho" <?php echo in_array('macho', (array)$filtros['sexo']) ? 'checked' : ''; ?>> Macho</label>
                        <label><input type="checkbox" name="sexo[]" value="femea" <?php echo in_array('femea', (array)$filtros['sexo']) ? 'checked' : ''; ?>> Fêmea</label>
                    </div>
                </div>

                <div class="campos">
                    <div class="seta" onclick="toggleCampos(this)">Porte</div>
                    <div class="campo-oculto">
                        <label><input type="checkbox" name="porte[]" value="pequeno" <?php echo in_array('pequeno', (array)$filtros['porte']) ? 'checked' : ''; ?>> Pequeno</label>
                        <label><input type="checkbox" name="porte[]" value="medio" <?php echo in_array('medio', (array)$filtros['porte']) ? 'checked' : ''; ?>> Médio</label>
                        <label><input type="checkbox" name="porte[]" value="grande" <?php echo in_array('grande', (array)$filtros['porte']) ? 'checked' : ''; ?>> Grande</label>
                    </div>
                </div>

                <div class="campos">
                    <div class="seta" onclick="toggleCampos(this)">Idade</div>
                    <div class="campo-oculto">
                        <label><input type="checkbox" name="idade[]" value="0-1" <?php echo in_array('0-1', (array)$filtros['idade']) ? 'checked' : ''; ?>> 0-1 ano</label>
                        <label><input type="checkbox" name="idade[]" value="1-3" <?php echo in_array('1-3', (array)$filtros['idade']) ? 'checked' : ''; ?>> 1-3 anos</label>
                        <label><input type="checkbox" name="idade[]" value="3-5" <?php echo in_array('3-5', (array)$filtros['idade']) ? 'checked' : ''; ?>> 3-5 anos</label>
                        <label><input type="checkbox" name="idade[]" value="5-8" <?php echo in_array('5-8', (array)$filtros['idade']) ? 'checked' : ''; ?>> 5-8 anos</label>
                        <label><input type="checkbox" name="idade[]" value="8+" <?php echo in_array('8+', (array)$filtros['idade']) ? 'checked' : ''; ?>> 8+ anos</label>
                    </div>
                </div>

                <div class="campos">
                    <div class="seta" onclick="toggleCampos(this)">Castração</div>
                    <div class="campo-oculto">
                        <label><input type="checkbox" name="castracao[]" value="sim" <?php echo in_array('sim', (array)$filtros['castracao']) ? 'checked' : ''; ?>> Castrado</label>
                        <label><input type="checkbox" name="castracao[]" value="nao" <?php echo in_array('nao', (array)$filtros['castracao']) ? 'checked' : ''; ?>> Não Castrado</label>
                    </div>
                </div>
                
                <button id="aplicar" type="submit">Aplicar</button>              
            </form>
        </div>
        
        <div id="galeria">
            <?php if (count($animais) > 0): ?>
                <?php foreach ($animais as $animal): 
                    $idade = $animal->calcularIdade();
                    $sexo_icone = $animal->sexo == 'macho' ? 'imagem/icone_macho.png' : 'imagem/icone_femea.png';
                ?>
                <div class="animal">
                    <img src="animais/<?php echo htmlspecialchars($animal->foto); ?>" alt="Foto do Animal">
                    <div class="info">
                        <div class="detalhes">
                            <span class="nome"><?php echo htmlspecialchars($animal->nome); ?></span>
                            <span class="idade"><?php echo $idade; ?></span>
                        </div>
                        <div class="sexo">
                            <img src="<?php echo $sexo_icone; ?>" alt="<?php echo htmlspecialchars($animal->sexo); ?>">
                        </div>
                        <button class="mais" onclick="window.location.href='detalhes_adotados.php?animal_id=<?php echo $animal->id; ?>'">VER MAIS</button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="mensagem-vazia">Não há animais adotados.</p>
            <?php endif; ?>
        </div>
        <div class="btn-voltar">            
            <a id="voltar" href="#" onclick="history.back();">VOLTAR</a>
        </div> 
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

        function toggleCampos(elemento) {
            var campoOculto = elemento.nextElementSibling;
            if (campoOculto.style.display === "none" || campoOculto.style.display === "") {
                campoOculto.style.display = "block";
            } else {
                campoOculto.style.display = "none";
            }
        }
    </script>
</body>
</html>
