<?php
require_once __DIR__ . '/includes/auth.php';

$idLicitacao  = (int) ($_POST['id_licitacao'] ?? 0);
$idFornecedor = $_POST['id_fornecedor'] ?? '';
$vencedor     = isset($_POST['vencedor']) ? 1 : 0;

if ($idLicitacao && $idFornecedor) {
    try {
        $pdo->beginTransaction();

        // só pode existir uma vencedora por licitação
        if ($vencedor) {
            $pdo->prepare('UPDATE licitacoes_participantes SET vencedor = 0 WHERE id_licitacao = :id')
                ->execute(['id' => $idLicitacao]);
        }

        $pdo->prepare(
            'INSERT INTO licitacoes_participantes (id_licitacao, id_fornecedor, vencedor)
             VALUES (:id_licitacao, :id_fornecedor, :vencedor)
             ON DUPLICATE KEY UPDATE vencedor = VALUES(vencedor)'
        )->execute([
            'id_licitacao'  => $idLicitacao,
            'id_fornecedor' => $idFornecedor,
            'vencedor'      => $vencedor,
        ]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
    }
}

header('Location: licitacao-form.php?id=' . $idLicitacao);
exit;
