<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/diario-oficial-pdf.php';

$idEdicao = (int) ($_POST['id_edicao'] ?? 0);

if (!$idEdicao) {
    header('Location: diario-oficial-edicoes.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM diario_oficial_edicoes WHERE id_edicao = :id');
$stmt->execute(['id' => $idEdicao]);
$edicao = $stmt->fetch();

if (!$edicao || $edicao['status'] !== 'rascunho') {
    header('Location: diario-oficial-editor.php?id=' . $idEdicao);
    exit;
}

$stmtAtos = $pdo->prepare('SELECT * FROM diario_oficial_atos WHERE id_edicao = :id ORDER BY ordem, id_ato');
$stmtAtos->execute(['id' => $idEdicao]);
$atos = $stmtAtos->fetchAll();

$resultado = gerarPdfEdicaoDiarioOficial($edicao, $atos);

// remove o PDF anterior desta edição, se já existia (regeração)
if (!empty($edicao['arquivo_pdf']) && $edicao['arquivo_pdf'] !== $resultado['arquivo']) {
    @unlink(__DIR__ . '/assets/uploads/diario-oficial/' . $edicao['arquivo_pdf']);
}

$pdo->prepare(
    'UPDATE diario_oficial_edicoes
     SET arquivo_pdf = :arquivo, hash_sha256 = :hash, assinado_icp = :assinado,
         assinado_em = :assinado_em
     WHERE id_edicao = :id'
)->execute([
    'arquivo'      => $resultado['arquivo'],
    'hash'         => $resultado['hash'],
    'assinado'     => $resultado['assinado'] ? 1 : 0,
    'assinado_em'  => $resultado['assinado'] ? date('Y-m-d H:i:s') : null,
    'id'           => $idEdicao,
]);

header('Location: diario-oficial-editor.php?id=' . $idEdicao . '&ok=gerado');
exit;
