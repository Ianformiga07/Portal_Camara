<?php
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    header('Location: noticias.php');
    exit;
}

$id = (int) $_POST['id'];

$stmt = $pdo->prepare('SELECT imagem FROM noticias WHERE id_noticia = :id');
$stmt->execute(['id' => $id]);
$noticia = $stmt->fetch();

if ($noticia) {
    $pdo->prepare('DELETE FROM noticias WHERE id_noticia = :id')->execute(['id' => $id]);

    if (!empty($noticia['imagem'])) {
        @unlink(__DIR__ . '/assets/uploads/noticias/' . $noticia['imagem']);
    }
}

header('Location: noticias.php?ok=excluida');
exit;
