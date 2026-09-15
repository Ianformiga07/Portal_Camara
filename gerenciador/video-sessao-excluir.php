<?php
require_once __DIR__ . '/includes/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) { header('Location: videos-sessoes.php'); exit; }
$id = (int) $_POST['id'];
$pdo->prepare('DELETE FROM videos_sessoes WHERE id_video = :id')->execute(['id' => $id]);
header('Location: videos-sessoes.php?ok=excluido');
exit;
