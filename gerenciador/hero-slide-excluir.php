<?php
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    header('Location: hero-slides.php');
    exit;
}

$id = (int) $_POST['id'];

$stmt = $pdo->prepare('SELECT imagem FROM hero_slides WHERE id_slide = :id');
$stmt->execute(['id' => $id]);
$slide = $stmt->fetch();

if ($slide) {
    $pdo->prepare('DELETE FROM hero_slides WHERE id_slide = :id')->execute(['id' => $id]);

    if (!empty($slide['imagem'])) {
        @unlink(__DIR__ . '/assets/uploads/hero/' . $slide['imagem']);
    }
}

header('Location: hero-slides.php?ok=excluido');
exit;
