<?php
session_start();
include_once 'config.php';
include_once 'Animal.php';

$autenticado = isset($_SESSION['user_id']);
$user_id = $autenticado ? $_SESSION['user_id'] : null;

$filtros = [
    'nome' => '',
    'especie' => '',
    'sexo' => '',
    'porte' => '',
    'idade' => '',
    'castracao' => '',
    'abrigo' => ''
];

foreach ($filtros as $chave => $valor) {
    if (isset($_GET[$chave]) && !empty($_GET[$chave])) {
        $filtros[$chave] = $_GET[$chave];
    }
}

$condicoes = [];
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
if (!empty($filtros['abrigo'])) {
    $abrigos = implode(',', array_map('intval', (array)$filtros['abrigo']));
    $condicoes[] = "a.abrigo_id IN ($abrigos)";
}

$query = "SELECT a.* 
          FROM animal a 
          LEFT JOIN solicitacao_adocao sa ON a.id = sa.animal_id 
          AND sa.status IN ('analise', 'aprovado') 
          WHERE sa.id IS NULL";

if (!empty($condicoes)) {
    $query .= " AND " . implode(" AND ", $condicoes);
}

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilo/pagina_usuario.css">
    <title>Quero Adotar</title>
</head>
<body>
    <header>
        <div id="logo">
            <a href="index.php">
                <img src="imagem/logo.png" alt="Logo 4 Patas">
            </a>
        </div>
        <nav>
            <a href="<?php echo $autenticado ? 'minha_conta_usuario.php' : 'javascript:void(0);' ?>" onclick="<?php echo $autenticado ? '' : 'confirmarLogin(\'login.php\')' ?>">Minha Conta</a>
            <?php if ($autenticado): ?>
                <a href="meus_animais.php">Meus Animais</a>
                <a href="minhas_solicitacoes.php">Minhas Solicitações</a>
            <?php endif; ?>
            <a href="ver_abrigos.php">Canis e Abrigos</a>
            <a href="sair.php">Sair</a>
        </nav>
    </header>
    
    <div id="container">
        <div id="filtros">
            <form id="filtros_form" method="GET" action="">
                
                <div class="campos">
                    <h3>Filtros</h3>
                    <button id="limpar" type="reset" onclick="window.location.href='pagina_usuario.php'">Limpar todos</button>
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

                <div id="lista_abrigos" class="campos">
                    <div class="seta" onclick="toggleCampos(this)">Abrigo/Canil</div>
                    <div class="campo-oculto">
                        <label for="pesquisa_abrigo"></label>
                        <input type="text" id="pesquisa_abrigo" placeholder="Pesquisar">
                        <?php
                        $abrigoQuery = "SELECT id, nome FROM abrigo";
                        $abrigoResult = $conn->query($abrigoQuery);
                        while ($abrigo = $abrigoResult->fetch_assoc()): ?>
                            <label class="abrigo-item"><input type="checkbox" name="abrigo[]" value="<?php echo $abrigo['id']; ?>" <?php echo in_array($abrigo['id'], (array)$filtros['abrigo']) ? 'checked' : ''; ?>> <?php echo htmlspecialchars($abrigo['nome']); ?></label>
                        <?php endwhile; ?>
                    </div>
                </div>
        
                <button id="aplicar" type="submit">Aplicar</button>
            </form>
        </div>
        
        <div id="galeria">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): 
                    $animal = new Animal($conn);
                    $animal->id = $row['id'];
                    $animal->data_nascimento = $row['data_nascimento'];
                    $animal->nome = $row['nome'];
                    $animal->sexo = $row['sexo'];
                    $animal->foto = $row['foto'];
                    $idade = $animal->calcularIdade();
                    $sexo_icone = $animal->sexo == 'macho' ? 'imagem/icone_macho.png' : 'imagem/icone_femea.png';
                ?>
                <div class="animal">
                    <img src="animais/<?php echo htmlspecialchars($animal->foto); ?>" alt="<?php echo htmlspecialchars($animal->nome); ?>">
                    <div class="info">
                        <div class="detalhes">
                            <span class="nome"><?php echo htmlspecialchars($animal->nome); ?></span>
                            <span class="idade"><?php echo $idade; ?></span>
                        </div>
                        <div class="sexo">
                            <img src="<?php echo $sexo_icone; ?>" alt="<?php echo htmlspecialchars($animal->sexo); ?>">
                        </div>
                        <?php if ($autenticado): ?>
                            <button class="adotar" onclick="window.location.href='detalhes_animal.php?animal_id=<?php echo $animal->id; ?>'">ADOTAR</button>
                        <?php else: ?>
                            <button class="adotar" onclick="confirmarLogin('login.php')">ADOTAR</button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="mensagem-vazia">Não há nenhum animal disponível.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function confirmarLogin(url) {
            if (confirm("É necessário fazer o login para acessar esta funcionalidade. Deseja fazer o login agora?")) {
                window.location.href = url;
            }
        }

        document.getElementById('pesquisa_abrigo').addEventListener('input', function() {
            var filtro = this.value.toLowerCase();
            var abrigos = document.querySelectorAll('#lista_abrigos .abrigo-item');

            abrigos.forEach(function(abrigo) {
                var nome = abrigo.textContent.toLowerCase();
                if (nome.includes(filtro)) {
                    abrigo.style.display = '';
                } else {
                    abrigo.style.display = 'none';
                }
            });
         });

        function toggleCampos(element) {
            element.classList.toggle('aberta');
            var content = element.nextElementSibling;
            if (content.style.display === "block") {
                content.style.display = "none";
            } else {
                content.style.display = "block";
            }
        }
    </script>
</body>
</html>
