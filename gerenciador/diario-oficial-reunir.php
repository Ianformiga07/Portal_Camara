<?php
require_once __DIR__ . '/includes/auth.php';

$idEdicao = (int) ($_POST['id_edicao'] ?? 0);
$dataInicio = $_POST['data_inicio'] ?? '';
$dataFim    = $_POST['data_fim'] ?? '';

if (!$idEdicao || !$dataInicio || !$dataFim) {
    header('Location: diario-oficial-edicoes.php');
    exit;
}

$stmt = $pdo->prepare('SELECT status FROM diario_oficial_edicoes WHERE id_edicao = :id');
$stmt->execute(['id' => $idEdicao]);
$edicao = $stmt->fetch();
if (!$edicao || $edicao['status'] !== 'rascunho') {
    header('Location: diario-oficial-editor.php?id=' . $idEdicao);
    exit;
}

$stmtOrdem = $pdo->prepare('SELECT COALESCE(MAX(ordem), 0) FROM diario_oficial_atos WHERE id_edicao = :id');
$stmtOrdem->execute(['id' => $idEdicao]);
$ordem = (int) $stmtOrdem->fetchColumn();

$stmtInserir = $pdo->prepare(
    'INSERT IGNORE INTO diario_oficial_atos (id_edicao, ordem, origem, origem_tabela, origem_id, tipo, numero, titulo, texto)
     VALUES (:id_edicao, :ordem, \'auto\', :tabela, :origem_id, :tipo, :numero, :titulo, :texto)'
);

// --- Documentos (decretos, portarias, resoluções, leis etc. — tudo
//     exceto o próprio Diário Oficial, pra não incluir edição dentro de edição) ---
$stmtDocs = $pdo->prepare(
    "SELECT d.id_documento, d.numero_documento, d.titulo, d.descricao, c.descricao AS categoria_descricao
     FROM documentos d
     JOIN categorias_documentos c ON c.id_categoria = d.id_categoria
     WHERE c.slug <> 'diario-oficial' AND d.status = 1
       AND d.data_publicacao BETWEEN :inicio AND :fim
     ORDER BY d.data_publicacao, d.id_documento"
);
$stmtDocs->execute(['inicio' => $dataInicio, 'fim' => $dataFim]);
foreach ($stmtDocs->fetchAll() as $d) {
    $ordem++;
    $stmtInserir->execute([
        'id_edicao'  => $idEdicao,
        'ordem'      => $ordem,
        'tabela'     => 'documentos',
        'origem_id'  => $d['id_documento'],
        'tipo'       => $d['categoria_descricao'],
        'numero'     => $d['numero_documento'],
        'titulo'     => $d['titulo'],
        'texto'      => $d['descricao'],
    ]);
}

// --- Licitações abertas no período ---
$stmtLic = $pdo->prepare(
    'SELECT id_licitacao, numero_processo, numero_licitacao, objeto
     FROM licitacoes
     WHERE status = 1 AND data_abertura BETWEEN :inicio AND :fim
     ORDER BY data_abertura'
);
$stmtLic->execute(['inicio' => $dataInicio, 'fim' => $dataFim]);
foreach ($stmtLic->fetchAll() as $l) {
    $ordem++;
    $stmtInserir->execute([
        'id_edicao'  => $idEdicao,
        'ordem'      => $ordem,
        'tabela'     => 'licitacoes',
        'origem_id'  => $l['id_licitacao'],
        'tipo'       => 'Licitação',
        'numero'     => $l['numero_processo'] ?: $l['numero_licitacao'],
        'titulo'     => 'Abertura de processo licitatório',
        'texto'      => $l['objeto'],
    ]);
}

// --- Extratos de contrato publicados no período ---
$stmtCon = $pdo->prepare(
    'SELECT id_contrato, numero_contrato, objeto
     FROM contratos
     WHERE status = 1
       AND COALESCE(data_publicacao_extrato, inicio_vigencia) BETWEEN :inicio AND :fim
     ORDER BY COALESCE(data_publicacao_extrato, inicio_vigencia)'
);
$stmtCon->execute(['inicio' => $dataInicio, 'fim' => $dataFim]);
foreach ($stmtCon->fetchAll() as $c) {
    $ordem++;
    $stmtInserir->execute([
        'id_edicao'  => $idEdicao,
        'ordem'      => $ordem,
        'tabela'     => 'contratos',
        'origem_id'  => $c['id_contrato'],
        'tipo'       => 'Extrato de Contrato',
        'numero'     => $c['numero_contrato'],
        'titulo'     => 'Extrato de contrato administrativo',
        'texto'      => $c['objeto'],
    ]);
}

header('Location: diario-oficial-editor.php?id=' . $idEdicao . '&ok=reunido');
exit;
