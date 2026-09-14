<?php
require_once __DIR__ . '/includes/auth.php';

$idContrato = (int) ($_POST['id_contrato'] ?? 0);
$numeroAditivo = trim($_POST['numero_aditivo'] ?? '');
$dataAditivo   = $_POST['data_aditivo'] ?: null;
$descricao     = trim($_POST['descricao'] ?? '');

if ($idContrato) {
    $arquivo = null;
    if (!empty($_FILES['arquivo']['name']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
        $extensao = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));
        if ($extensao === 'pdf' && $_FILES['arquivo']['size'] <= 10 * 1024 * 1024) {
            $arquivo = 'aditivo_' . uniqid() . '.pdf';
            $pastaDestino = __DIR__ . '/assets/uploads/contratos/';
            if (!is_dir($pastaDestino)) {
                mkdir($pastaDestino, 0755, true);
            }
            if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], $pastaDestino . $arquivo)) {
                $arquivo = null;
            }
        }
    }

    if ($numeroAditivo !== '' || $descricao !== '' || $arquivo) {
        $pdo->prepare(
            'INSERT INTO contratos_aditivos (id_contrato, numero_aditivo, data_aditivo, descricao, arquivo)
             VALUES (:id_contrato, :numero_aditivo, :data_aditivo, :descricao, :arquivo)'
        )->execute([
            'id_contrato'    => $idContrato,
            'numero_aditivo' => $numeroAditivo ?: null,
            'data_aditivo'   => $dataAditivo,
            'descricao'      => $descricao ?: null,
            'arquivo'        => $arquivo,
        ]);
    }
}

header('Location: contrato-form.php?id=' . $idContrato);
exit;
