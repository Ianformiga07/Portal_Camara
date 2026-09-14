<?php
require_once __DIR__ . '/includes/auth.php';

$idLegislatura  = (int) ($_POST['id_legislatura'] ?? 0);
$idTipoComissao = $_POST['id_tipo_comissao'] ?? '';
$idVereador     = $_POST['id_vereador'] ?? '';
$idFuncao       = $_POST['id_funcao'] ?: null;

if ($idLegislatura && $idTipoComissao && $idVereador) {
    $stmt = $pdo->prepare(
        'SELECT id_comissao FROM comissoes_membros
         WHERE id_legislatura = :leg AND id_tipo_comissao = :tipo AND id_vereador = :ver AND ativo = 1'
    );
    $stmt->execute(['leg' => $idLegislatura, 'tipo' => $idTipoComissao, 'ver' => $idVereador]);

    if (!$stmt->fetch()) {
        $pdo->prepare(
            'INSERT INTO comissoes_membros (id_vereador, id_funcao, id_legislatura, id_tipo_comissao, ativo)
             VALUES (:id_vereador, :id_funcao, :id_legislatura, :id_tipo_comissao, 1)'
        )->execute([
            'id_vereador'      => $idVereador,
            'id_funcao'        => $idFuncao,
            'id_legislatura'   => $idLegislatura,
            'id_tipo_comissao' => $idTipoComissao,
        ]);
    }
}

header('Location: comissoes.php?legislatura=' . $idLegislatura . '&ok=1');
exit;
