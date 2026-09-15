<?php
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: diario-oficial-edicoes.php');
    exit;
}

$numeroEdicao = trim($_POST['numero_edicao'] ?? '');
$anoExercicio = (int) ($_POST['ano_exercicio'] ?? date('Y'));
$dataEdicao   = $_POST['data_edicao'] ?? date('Y-m-d');

$erros = [];
if ($numeroEdicao === '') {
    $erros[] = 'Informe o número da edição.';
}

if (empty($erros)) {
    $stmt = $pdo->prepare('SELECT id_edicao FROM diario_oficial_edicoes WHERE numero_edicao = :n AND ano_exercicio = :a');
    $stmt->execute(['n' => $numeroEdicao, 'a' => $anoExercicio]);
    if ($stmt->fetch()) {
        $erros[] = 'Já existe uma edição com esse número neste ano.';
    }
}

if (!empty($erros)) {
    header('Location: diario-oficial-edicoes.php?erro=' . urlencode(implode(' ', $erros)));
    exit;
}

$pdo->prepare(
    'INSERT INTO diario_oficial_edicoes (numero_edicao, ano_exercicio, data_edicao, status, criado_por)
     VALUES (:numero, :ano, :data, \'rascunho\', :criado_por)'
)->execute([
    'numero'      => $numeroEdicao,
    'ano'         => $anoExercicio,
    'data'        => $dataEdicao,
    'criado_por'  => $usuario['id_servidor'],
]);

header('Location: diario-oficial-editor.php?id=' . $pdo->lastInsertId());
exit;
