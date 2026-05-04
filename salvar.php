<?php
include 'conexao.php';

if(
    empty($_POST['nome']) || empty($_POST['email']) || empty($_POST['telefone']) ||
    empty($_POST['idade']) || empty($_POST['cidade']) || empty($_POST['curso']) 
){
    die("Preencha todos os campos obrigatórios.");
}

$nome = mysqli_real_escape_string($conn, trim($_POST['nome']));
$email = mysqli_real_escape_string($conn, trim($_POST['email']));
$telefone = mysqli_real_escape_string($conn, trim($_POST['telefone']));
$idade = (int)$_POST['idade'];
$cidade = mysqli_real_escape_string($conn, trim($_POST['cidade']));
$curso = mysqli_real_escape_string($conn, trim($_POST['curso']));

$sql = "INSERT INTO usuarios (nome, email, telefone, idade, cidade, curso) 
        VALUES ('$nome', '$email', '$telefone', $idade, '$cidade', '$curso')";

$resultado = mysqli_query($conn, $sql);

if (!$resultado) {
    die("Erro ao salvar usuário: " . mysqli_error($conn));
}

header("Location: index.php");
exit;
?>