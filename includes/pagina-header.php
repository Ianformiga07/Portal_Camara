<?php
/**
 * Inclua depois de includes/header.php. Espera $tituloPaginaInterna e,
 * opcionalmente, $subtituloPaginaInterna definidos antes do include.
 */
?>
<div class="pagina-interna-banner">
  <div class="section-container">
    <h1><?= htmlspecialchars($tituloPaginaInterna ?? '') ?></h1>
    <?php if (!empty($subtituloPaginaInterna)): ?>
      <p><?= htmlspecialchars($subtituloPaginaInterna) ?></p>
    <?php endif; ?>
    <nav class="pagina-interna-breadcrumb">
      <a href="index.php">Início</a> <i class="fas fa-chevron-right"></i> <span><?= htmlspecialchars($tituloPaginaInterna ?? '') ?></span>
    </nav>
  </div>
</div>
