<?php
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    header('Location: licitacoes.php');
    exit;
}

$id = (int) $_POST['id'];

$stmt = $pdo->prepare('SELECT arquivo FROM licitacoes WHERE id_licitacao = :id');
$stmt->execute(['id' => $id]);
$licitacao = $stmt->fetch();

if (!$licitacao) {
    header('Location: licitacoes.php');
    exit;
}

try {
    $pdo->prepare('DELETE FROM licitacoes WHERE id_licitacao = :id')->execute(['id' => $id]);
    if (!empty($licitacao['arquivo'])) {
        @unlink(__DIR__ . '/assets/uploads/licitacoes/' . $licitacao['arquivo']);
    }
    header('Location: licitacoes.php?ok=excluida');
} catch (Exception $e) {
    header('Location: licitacoes.php?erro=' . urlencode('Esta licitação está vinculada a um contrato ou compra e não pode ser excluída.'));
}
exit;
