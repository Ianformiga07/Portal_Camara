<?php
require_once __DIR__ . '/includes/auth.php';

$id = (int) ($_POST['id'] ?? 0);
$idVereador = (int) ($_POST['id_vereador'] ?? 0);

if ($id) {
    $pdo->prepare('DELETE FROM vereadores_mandatos_anteriores WHERE id = :id')->execute(['id' => $id]);
}

header('Location: vereador-form.php?id=' . $idVereador);
exit;
