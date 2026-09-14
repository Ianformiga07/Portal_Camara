<?php
require_once __DIR__ . '/includes/auth.php';

$idVereador = (int) ($_POST['id_vereador'] ?? 0);
$idMandato  = $_POST['id_mandato'] ?? '';
$anoInicio  = (int) ($_POST['ano_inicio'] ?? 0);
$anoFim     = (int) ($_POST['ano_fim'] ?? 0);

if ($idVereador && $idMandato && $anoInicio && $anoFim && $anoFim >= $anoInicio) {
    $pdo->prepare(
        'INSERT INTO vereadores_mandatos_anteriores (id_vereador, id_mandato, ano_inicio, ano_fim)
         VALUES (:id_vereador, :id_mandato, :ano_inicio, :ano_fim)'
    )->execute([
        'id_vereador' => $idVereador,
        'id_mandato'  => $idMandato,
        'ano_inicio'  => $anoInicio,
        'ano_fim'     => $anoFim,
    ]);
}

header('Location: vereador-form.php?id=' . $idVereador);
exit;
