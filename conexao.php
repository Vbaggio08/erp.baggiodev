<?php
// Conexão com banco de dados MySQL
$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "crud_simples"
);

if (!$conn) {
    die("Erro ao conectar ao banco: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>