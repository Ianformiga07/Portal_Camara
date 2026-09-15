<?php
/**
 * Inclua no topo de cada página interna, depois de includes/auth.php.
 * Espera opcionalmente $tituloPagina definido antes do include.
 */
$tituloPagina = $tituloPagina ?? 'Gerenciador';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($tituloPagina) ?> — Gerenciador | Câmara de Ananás</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="assets/css/admin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<div class="app">
  <?php include __DIR__ . '/sidebar.php'; ?>

  <div class="conteudo">
    <header class="topbar">
      <button class="topbar__toggle" title="Abrir/fechar menu"><i class="fa-solid fa-bars"></i></button>
      <button class="topbar__sair"><i class="fa-solid fa-right-from-bracket"></i> Sair</button>
    </header>

    <main class="pagina">
