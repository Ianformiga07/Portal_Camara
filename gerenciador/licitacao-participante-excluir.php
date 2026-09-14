<?php
require_once __DIR__ . '/includes/auth.php';

$id = (int) ($_POST['id'] ?? 0);
$idLicitacao = (int) ($_POST['id_licitacao'] ?? 0);

if ($id) {
    $pdo->prepare('DELETE FROM licitacoes_participantes WHERE id = :id')->execute(['id' => $id]);
}

header('Location: licitacao-form.php?id=' . $idLicitacao);
exit;
