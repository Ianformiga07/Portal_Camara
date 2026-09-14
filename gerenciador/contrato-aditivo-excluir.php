<?php
require_once __DIR__ . '/includes/auth.php';

$id = (int) ($_POST['id'] ?? 0);
$idContrato = (int) ($_POST['id_contrato'] ?? 0);

if ($id) {
    $stmt = $pdo->prepare('SELECT arquivo FROM contratos_aditivos WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $aditivo = $stmt->fetch();

    $pdo->prepare('DELETE FROM contratos_aditivos WHERE id = :id')->execute(['id' => $id]);

    if ($aditivo && !empty($aditivo['arquivo'])) {
        @unlink(__DIR__ . '/assets/uploads/contratos/' . $aditivo['arquivo']);
    }
}

header('Location: contrato-form.php?id=' . $idContrato);
exit;
