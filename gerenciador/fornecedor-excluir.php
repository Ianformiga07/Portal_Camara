<?php
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    header('Location: fornecedores.php');
    exit;
}

$id = (int) $_POST['id'];

try {
    $pdo->prepare('DELETE FROM fornecedores WHERE id_fornecedor = :id')->execute(['id' => $id]);
    header('Location: fornecedores.php?ok=excluido');
} catch (Exception $e) {
    header('Location: fornecedores.php?erro=' . urlencode('Este fornecedor está vinculado a uma licitação, compra ou contrato e não pode ser excluído. Marque-o como inativo em vez de excluir.'));
}
exit;
