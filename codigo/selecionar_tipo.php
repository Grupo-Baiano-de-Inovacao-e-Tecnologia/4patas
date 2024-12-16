<?php
session_start();
if (isset($_GET['tipo'])) {
    $_SESSION['tipo_animal'] = $_GET['tipo'];
    header("Location: cadastrar_animal.php");
    exit();
} else {
    echo "Tipo de animal não especificado.";
}
?>
