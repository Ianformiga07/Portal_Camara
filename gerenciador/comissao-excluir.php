<?php
require_once __DIR__ . '/includes/auth.php';

$id = (int) ($_POST['id'] ?? 0);
$legislatura = (int) ($_POST['legislatura'] ?? 0);

if ($id) {
    $pdo->prepare('DELETE FROM comissoes_membros WHERE id_comissao = :id')->execute(['id' => $id]);
}

header('Location: comissoes.php?legislatura=' . $legislatura . '&ok=1');
exit;
