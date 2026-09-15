<?php
require_once __DIR__ . '/includes/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) { header('Location: obras.php'); exit; }
$id = (int) $_POST['id'];
$pdo->prepare('DELETE FROM obras WHERE id_obra = :id')->execute(['id' => $id]);
header('Location: obras.php?ok=excluido');
exit;
