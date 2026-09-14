<?php
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    header('Location: esic.php');
    exit;
}

$id = (int) $_POST['id'];

$stmt = $pdo->prepare('SELECT anexo FROM esic_solicitacoes WHERE id_esic = :id');
$stmt->execute(['id' => $id]);
$solicitacao = $stmt->fetch();

if ($solicitacao) {
    $pdo->prepare('DELETE FROM esic_solicitacoes WHERE id_esic = :id')->execute(['id' => $id]);
    if (!empty($solicitacao['anexo'])) {
        @unlink(__DIR__ . '/assets/uploads/esic/' . $solicitacao['anexo']);
    }
}

header('Location: esic.php?ok=1');
exit;
