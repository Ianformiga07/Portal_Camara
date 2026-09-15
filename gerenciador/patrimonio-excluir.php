<?php
require_once __DIR__ . '/includes/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) { header('Location: patrimonio.php'); exit; }
$id = (int) $_POST['id'];
$pdo->prepare('DELETE FROM patrimonio WHERE id_patrimonio = :id')->execute(['id' => $id]);
header('Location: patrimonio.php?ok=excluido');
exit;
