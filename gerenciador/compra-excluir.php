<?php
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    header('Location: compras.php');
    exit;
}

$id = (int) $_POST['id'];
$pdo->prepare('DELETE FROM compras WHERE id_compra = :id')->execute(['id' => $id]);

header('Location: compras.php?ok=excluida');
exit;
