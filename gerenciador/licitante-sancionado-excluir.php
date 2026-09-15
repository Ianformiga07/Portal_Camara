<?php
require_once __DIR__ . '/includes/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) { header('Location: licitantes-sancionados.php'); exit; }
$id = (int) $_POST['id'];
$pdo->prepare('DELETE FROM licitantes_sancionados WHERE id_sancao = :id')->execute(['id' => $id]);
header('Location: licitantes-sancionados.php?ok=excluido');
exit;
