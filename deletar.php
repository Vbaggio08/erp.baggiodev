<?php
include 'conexao.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    if ($id <= 0) {
        die("ID inválido.");
    }

    $sql = "DELETE FROM usuarios WHERE id = $id";

    if (mysqli_query($conn, $sql)) {
        header("Location: index.php");
    } else {
        echo "Erro ao excluir: " . mysqli_error($conn);
    }
} else {
    header("Location: index.php");
}
?>