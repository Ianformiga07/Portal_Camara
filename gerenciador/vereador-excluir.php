<?php
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    header('Location: vereadores.php');
    exit;
}

$id = (int) $_POST['id'];

$stmt = $pdo->prepare('SELECT id_servidor FROM vereadores WHERE id_vereador = :id');
$stmt->execute(['id' => $id]);
$vereador = $stmt->fetch();

if (!$vereador) {
    header('Location: vereadores.php');
    exit;
}

$stmtServidor = $pdo->prepare('SELECT foto_perfil FROM servidores WHERE id_servidor = :id');
$stmtServidor->execute(['id' => $vereador['id_servidor']]);
$servidor = $stmtServidor->fetch();

// Exclui o vínculo de vereador (mesa diretora, comissões e mandatos
// anteriores são removidos automaticamente por ON DELETE CASCADE).
$pdo->prepare('DELETE FROM vereadores WHERE id_vereador = :id')->execute(['id' => $id]);

// Tenta remover também o cadastro de servidor. Se ele estiver vinculado
// a outra coisa (ex: fiscal de contrato), a exclusão é bloqueada pela
// chave estrangeira e o registro de servidor simplesmente permanece.
try {
    $pdo->prepare('DELETE FROM servidores WHERE id_servidor = :id')->execute(['id' => $vereador['id_servidor']]);
    if ($servidor && !empty($servidor['foto_perfil'])) {
        @unlink(__DIR__ . '/assets/uploads/vereadores/' . $servidor['foto_perfil']);
    }
} catch (Exception $e) {
    // servidor mantido por estar referenciado em outro módulo — segue normalmente
}

header('Location: vereadores.php?ok=excluido');
exit;
