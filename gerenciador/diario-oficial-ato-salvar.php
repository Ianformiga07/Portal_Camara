<?php
require_once __DIR__ . '/includes/auth.php';

$idEdicao = (int) ($_POST['id_edicao'] ?? 0);
$tipo     = trim($_POST['tipo'] ?? '');
$numero   = trim($_POST['numero'] ?? '');
$titulo   = trim($_POST['titulo'] ?? '');
$texto    = trim($_POST['texto'] ?? '');

if ($idEdicao && $tipo !== '' && $texto !== '') {
    $stmt = $pdo->prepare('SELECT status FROM diario_oficial_edicoes WHERE id_edicao = :id');
    $stmt->execute(['id' => $idEdicao]);
    $edicao = $stmt->fetch();

    if ($edicao && $edicao['status'] === 'rascunho') {
        $stmtOrdem = $pdo->prepare('SELECT COALESCE(MAX(ordem), 0) + 1 FROM diario_oficial_atos WHERE id_edicao = :id');
        $stmtOrdem->execute(['id' => $idEdicao]);
        $ordem = (int) $stmtOrdem->fetchColumn();

        $pdo->prepare(
            'INSERT INTO diario_oficial_atos (id_edicao, ordem, origem, tipo, numero, titulo, texto)
             VALUES (:id_edicao, :ordem, \'manual\', :tipo, :numero, :titulo, :texto)'
        )->execute([
            'id_edicao' => $idEdicao,
            'ordem'     => $ordem,
            'tipo'      => $tipo,
            'numero'    => $numero ?: null,
            'titulo'    => $titulo ?: null,
            'texto'     => $texto,
        ]);
    }
}

header('Location: diario-oficial-editor.php?id=' . $idEdicao);
exit;
