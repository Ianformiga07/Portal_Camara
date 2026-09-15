<?php
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    header('Location: execucao-orcamentaria.php');
    exit;
}

$id = (int) $_POST['id'];
$pdo->prepare('DELETE FROM execucao_orcamentaria WHERE id_execucao = :id')->execute(['id' => $id]);

header('Location: execucao-orcamentaria.php?ok=excluido');
exit;
