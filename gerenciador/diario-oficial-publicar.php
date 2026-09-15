<?php
require_once __DIR__ . '/includes/auth.php';

$idEdicao = (int) ($_POST['id_edicao'] ?? 0);

if (!$idEdicao) {
    header('Location: diario-oficial-edicoes.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM diario_oficial_edicoes WHERE id_edicao = :id');
$stmt->execute(['id' => $idEdicao]);
$edicao = $stmt->fetch();

if (!$edicao || $edicao['status'] !== 'rascunho' || empty($edicao['arquivo_pdf'])) {
    header('Location: diario-oficial-editor.php?id=' . $idEdicao . '&erro=' . urlencode('Gere o PDF da edição antes de publicar.'));
    exit;
}

// o site público serve os anexos de "documentos" a partir de
// assets/uploads/documentos/ — copiamos o PDF já gerado (com hash e,
// se houver certificado, já assinado) para lá, mantendo o original
// em assets/uploads/diario-oficial/ como referência desta edição.
$origem = __DIR__ . '/assets/uploads/diario-oficial/' . $edicao['arquivo_pdf'];
$nomePublico = 'diario_' . uniqid() . '.pdf';
$destino = __DIR__ . '/assets/uploads/documentos/' . $nomePublico;
if (!is_dir(dirname($destino))) {
    mkdir(dirname($destino), 0755, true);
}
if (!copy($origem, $destino)) {
    header('Location: diario-oficial-editor.php?id=' . $idEdicao . '&erro=' . urlencode('Não foi possível publicar o PDF no site.'));
    exit;
}

$idCategoria = $pdo->query("SELECT id_categoria FROM categorias_documentos WHERE slug = 'diario-oficial' LIMIT 1")->fetchColumn();

try {
    $pdo->beginTransaction();

    $pdo->prepare(
        'INSERT INTO documentos (id_categoria, numero_documento, titulo, descricao, data_publicacao, arquivo, status)
         VALUES (:id_categoria, :numero, :titulo, :descricao, :data_publicacao, :arquivo, 1)'
    )->execute([
        'id_categoria'     => $idCategoria,
        'numero'           => $edicao['numero_edicao'] . '/' . $edicao['ano_exercicio'],
        'titulo'           => 'Diário Oficial nº ' . $edicao['numero_edicao'] . '/' . $edicao['ano_exercicio'],
        'descricao'        => 'Hash SHA-256 de integridade: ' . $edicao['hash_sha256']
            . ($edicao['assinado_icp'] ? ' — assinado digitalmente (ICP-Brasil).' : ' — sem assinatura digital ICP-Brasil configurada.'),
        'data_publicacao'  => $edicao['data_edicao'],
        'arquivo'          => $nomePublico,
    ]);
    $idDocumento = $pdo->lastInsertId();

    $pdo->prepare(
        "UPDATE diario_oficial_edicoes SET status = 'publicada', id_documento = :id_documento WHERE id_edicao = :id"
    )->execute(['id_documento' => $idDocumento, 'id' => $idEdicao]);

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    @unlink($destino);
    header('Location: diario-oficial-editor.php?id=' . $idEdicao . '&erro=' . urlencode('Falha ao publicar. Tente novamente.'));
    exit;
}

header('Location: diario-oficial-edicoes.php?ok=publicado');
exit;
