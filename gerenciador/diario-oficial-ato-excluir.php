<?php
require_once __DIR__ . '/includes/auth.php';

$idAto = (int) ($_POST['id_ato'] ?? 0);
$idEdicao = (int) ($_POST['id_edicao'] ?? 0);

if ($idAto && $idEdicao) {
    // só mexe em atos de edições ainda em rascunho
    $stmt = $pdo->prepare(
        'DELETE a FROM diario_oficial_atos a
         JOIN diario_oficial_edicoes e ON e.id_edicao = a.id_edicao
         WHERE a.id_ato = :id_ato AND a.id_edicao = :id_edicao AND e.status = \'rascunho\''
    );
    $stmt->execute(['id_ato' => $idAto, 'id_edicao' => $idEdicao]);
}

header('Location: diario-oficial-editor.php?id=' . $idEdicao);
exit;
