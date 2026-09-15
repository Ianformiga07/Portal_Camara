<?php
require_once __DIR__ . '/includes/auth.php';

$id = (int) ($_POST['id'] ?? 0);

if ($id) {
    $stmt = $pdo->prepare('SELECT status, arquivo_pdf FROM diario_oficial_edicoes WHERE id_edicao = :id');
    $stmt->execute(['id' => $id]);
    $edicao = $stmt->fetch();

    // só permite excluir rascunhos — uma edição já publicada tem que
    // continuar existindo para preservar o histórico do Diário Oficial
    if ($edicao && $edicao['status'] === 'rascunho') {
        if ($edicao['arquivo_pdf']) {
            @unlink(__DIR__ . '/assets/uploads/diario-oficial/' . $edicao['arquivo_pdf']);
        }
        $pdo->prepare('DELETE FROM diario_oficial_edicoes WHERE id_edicao = :id')->execute(['id' => $id]);
    }
}

header('Location: diario-oficial-edicoes.php?ok=excluido');
exit;
