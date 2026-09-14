<?php
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    header('Location: documentos.php');
    exit;
}

$id = (int) $_POST['id'];

$stmt = $pdo->prepare('SELECT arquivo FROM documentos WHERE id_documento = :id');
$stmt->execute(['id' => $id]);
$documento = $stmt->fetch();

if ($documento) {
    $pdo->prepare('DELETE FROM documentos WHERE id_documento = :id')->execute(['id' => $id]);

    if (!empty($documento['arquivo'])) {
        @unlink(__DIR__ . '/assets/uploads/documentos/' . $documento['arquivo']);
    }
}

header('Location: documentos.php?ok=excluido');
exit;
