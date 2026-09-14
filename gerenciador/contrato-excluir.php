<?php
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    header('Location: contratos.php');
    exit;
}

$id = (int) $_POST['id'];

$stmt = $pdo->prepare('SELECT arquivo FROM contratos WHERE id_contrato = :id');
$stmt->execute(['id' => $id]);
$contrato = $stmt->fetch();

if ($contrato) {
    $stmtAdit = $pdo->prepare('SELECT arquivo FROM contratos_aditivos WHERE id_contrato = :id');
    $stmtAdit->execute(['id' => $id]);
    $aditivos = $stmtAdit->fetchAll();

    // aditivos são removidos automaticamente por ON DELETE CASCADE
    $pdo->prepare('DELETE FROM contratos WHERE id_contrato = :id')->execute(['id' => $id]);

    if (!empty($contrato['arquivo'])) {
        @unlink(__DIR__ . '/assets/uploads/contratos/' . $contrato['arquivo']);
    }
    foreach ($aditivos as $a) {
        if (!empty($a['arquivo'])) {
            @unlink(__DIR__ . '/assets/uploads/contratos/' . $a['arquivo']);
        }
    }
}

header('Location: contratos.php?ok=excluido');
exit;
