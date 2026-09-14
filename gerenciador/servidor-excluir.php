<?php
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    header('Location: servidores.php');
    exit;
}

$id = (int) $_POST['id'];

$stmt = $pdo->prepare('SELECT foto_perfil FROM servidores WHERE id_servidor = :id');
$stmt->execute(['id' => $id]);
$servidor = $stmt->fetch();

if (!$servidor) {
    header('Location: servidores.php');
    exit;
}

try {
    $pdo->prepare('DELETE FROM servidores WHERE id_servidor = :id')->execute(['id' => $id]);
    if (!empty($servidor['foto_perfil'])) {
        @unlink(__DIR__ . '/assets/uploads/servidores/' . $servidor['foto_perfil']);
    }
    header('Location: servidores.php?ok=excluido');
} catch (Exception $e) {
    header('Location: servidores.php?erro=' . urlencode('Este servidor está vinculado a um vereador, contrato ou atendimento registrado, e não pode ser excluído. Marque-o como inativo em vez de excluir.'));
}
exit;
