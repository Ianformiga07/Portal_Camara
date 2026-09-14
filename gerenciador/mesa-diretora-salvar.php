<?php
require_once __DIR__ . '/includes/auth.php';

$idLegislatura = (int) ($_POST['id_legislatura'] ?? 0);
$idVereador    = $_POST['id_vereador'] ?? '';
$idFuncao      = $_POST['id_funcao'] ?? '';
$observacao    = trim($_POST['observacao'] ?? '');

if ($idLegislatura && $idVereador && $idFuncao) {
    // evita o mesmo vereador duplicado na mesma legislatura
    $stmt = $pdo->prepare(
        'SELECT id FROM mesa_diretora WHERE id_legislatura = :leg AND id_vereador = :ver AND ativo = 1'
    );
    $stmt->execute(['leg' => $idLegislatura, 'ver' => $idVereador]);

    if (!$stmt->fetch()) {
        $pdo->prepare(
            'INSERT INTO mesa_diretora (id_vereador, id_legislatura, id_funcao, membro_mesa, observacao, ativo)
             VALUES (:id_vereador, :id_legislatura, :id_funcao, 1, :observacao, 1)'
        )->execute([
            'id_vereador'    => $idVereador,
            'id_legislatura' => $idLegislatura,
            'id_funcao'      => $idFuncao,
            'observacao'     => $observacao ?: null,
        ]);
    }
}

header('Location: mesa-diretora.php?legislatura=' . $idLegislatura . '&ok=1');
exit;
