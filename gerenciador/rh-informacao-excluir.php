<?php
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    header('Location: rh-informacoes.php');
    exit;
}

$id = (int) $_POST['id'];
$pdo->prepare('DELETE FROM rh_informacoes WHERE id_rh = :id')->execute(['id' => $id]);

header('Location: rh-informacoes.php?ok=excluido');
exit;
