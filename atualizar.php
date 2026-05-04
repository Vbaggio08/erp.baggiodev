<?php
include 'conexao.php';

$id = (int)$_POST['id'];
$nome = mysqli_real_escape_string($conn, trim($_POST['nome']));
$email = mysqli_real_escape_string($conn, trim($_POST['email']));
$telefone = mysqli_real_escape_string($conn, trim($_POST['telefone']));
$idade = (int)$_POST['idade'];
$cidade = mysqli_real_escape_string($conn, trim($_POST['cidade']));
$curso = mysqli_real_escape_string($conn, trim($_POST['curso']));

if (empty($nome) || empty($email) || empty($telefone) || $idade <= 0 || empty($cidade) || empty($curso)) {
    die("Todos os campos são obrigatórios.");
}

$sql = "UPDATE usuarios SET 
  nome='$nome', email='$email', telefone='$telefone',
  idade=$idade, cidade='$cidade', curso='$curso'
WHERE id=$id";

$resultado = mysqli_query($conn, $sql);

if (!$resultado) {
    die("Erro ao atualizar usuário: " . mysqli_error($conn));
}

header("Location: index.php");
exit;
?>