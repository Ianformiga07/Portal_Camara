<?php
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    header('Location: manifestacoes.php');
    exit;
}

$id = (int) $_POST['id'];

$stmt = $pdo->prepare('SELECT anexo FROM manifestacoes WHERE id_manifestacao = :id');
$stmt->execute(['id' => $id]);
$manifestacao = $stmt->fetch();

if ($manifestacao) {
    $pdo->prepare('DELETE FROM manifestacoes WHERE id_manifestacao = :id')->execute(['id' => $id]);
    if (!empty($manifestacao['anexo'])) {
        @unlink(__DIR__ . '/assets/uploads/manifestacoes/' . $manifestacao['anexo']);
    }
}

header('Location: manifestacoes.php?ok=1');
exit;
