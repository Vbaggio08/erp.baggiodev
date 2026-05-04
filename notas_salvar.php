<?php
include 'conexao.php';

// Verifica se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: notas_form.php?erro=Metodo%20invalido');
    exit;
}

// Recebe e prepara dados
$alunoId = isset($_POST['aluno_id']) ? (int)$_POST['aluno_id'] : 0;
$bimestre = isset($_POST['bimestre']) ? trim($_POST['bimestre']) : '';
$nota1 = isset($_POST['nota1']) ? (float)$_POST['nota1'] : -1;
$nota2 = isset($_POST['nota2']) ? (float)$_POST['nota2'] : -1;
$nota3 = isset($_POST['nota3']) ? (float)$_POST['nota3'] : -1;
$peso = isset($_POST['peso']) ? (float)$_POST['peso'] : 0;
$faltas = isset($_POST['faltas']) ? (int)$_POST['faltas'] : -1;

// Valida dados
if (
    $alunoId <= 0 || $bimestre === '' ||
    $nota1 < 0 || $nota1 > 10 || $nota2 < 0 || $nota2 > 10 || $nota3 < 0 || $nota3 > 10 ||
    $peso <= 0 || $faltas < 0
) {
    header('Location: notas_form.php?erro=Verifique%20os%20dados%20informados');
    exit;
}

// Prepara e executa INSERT
$sql = 'INSERT INTO notas_alunos (aluno_id, bimestre, nota1, nota2, nota3, peso, faltas)
        VALUES (?, ?, ?, ?, ?, ?, ?)';

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    header('Location: notas_form.php?erro=Falha%20ao%20preparar%20consulta');
    exit;
}

mysqli_stmt_bind_param($stmt, 'isddddi', $alunoId, $bimestre, $nota1, $nota2, $nota3, $peso, $faltas);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if (!$ok) {
    header('Location: notas_form.php?erro=Erro%20ao%20salvar%20notas');
    exit;
}

header('Location: notas_index.php?sucesso=1');
exit;
