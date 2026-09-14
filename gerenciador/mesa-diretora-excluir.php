<?php
require_once __DIR__ . '/includes/auth.php';

$id = (int) ($_POST['id'] ?? 0);
$legislatura = (int) ($_POST['legislatura'] ?? 0);

if ($id) {
    $pdo->prepare('DELETE FROM mesa_diretora WHERE id = :id')->execute(['id' => $id]);
}

header('Location: mesa-diretora.php?legislatura=' . $legislatura . '&ok=1');
exit;
