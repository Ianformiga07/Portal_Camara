<?php
/**
 * Template de "categoria pendente". O arquivo que inclui define antes:
 *   $tituloPaginaInterna, $subtituloPaginaInterna
 *   $itensPendentes = [['titulo' => '...', 'desc' => '...'], ...]
 */
include 'includes/header.php';
include 'includes/pagina-header.php';
?>
<section class="pagina-interna-conteudo">
  <div class="section-container">
    <div class="alerta-portal aviso" style="max-width:none;">
      <i class="fas fa-circle-info"></i>
      Estas informações dependem de integração com o sistema contábil/administrativo da Câmara e estão sendo estruturadas.
      Enquanto isso, podem ser solicitadas pelo <a href="esic.php">e-SIC</a>.
    </div>
    <?php foreach ($itensPendentes as $item): ?>
      <div class="ficha-card" style="display:flex; align-items:center; gap:1rem;">
        <div style="width:42px; height:42px; border-radius:var(--radius-sm); background:var(--cinza-claro); color:var(--texto-claro); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
          <i class="fas fa-clock"></i>
        </div>
        <div>
          <strong style="display:block; margin-bottom:.2rem;"><?= htmlspecialchars($item['titulo']) ?></strong>
          <span style="font-size:.85rem; color:var(--texto-claro);"><?= htmlspecialchars($item['desc'] ?? 'Aguardando dados.') ?></span>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
