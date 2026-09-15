<?php
$page_title = 'Vídeos das Sessões — Câmara Municipal de Ananás';
require_once 'includes/funcoes.php';
include 'includes/header.php';

$tituloPaginaInterna = 'Vídeos das Sessões Plenárias';
$subtituloPaginaInterna = 'Gravações das sessões plenárias da Câmara Municipal.';

$videos = $pdo ? $pdo->query('SELECT * FROM videos_sessoes WHERE status = 1 ORDER BY data_sessao DESC')->fetchAll() : [];

include 'includes/pagina-header.php';
?>

<section class="pagina-interna-conteudo">
  <div class="section-container">
    <?php if (empty($videos)): ?>
      <div class="vazio-lista"><i class="fas fa-video"></i><p>Nenhum vídeo publicado no momento.</p></div>
    <?php else: ?>
      <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(320px,1fr)); gap:1.5rem;">
        <?php foreach ($videos as $v): ?>
          <div class="ficha-card" style="padding:0; overflow:hidden;">
            <div style="position:relative; padding-top:56.25%;">
              <iframe src="<?= htmlspecialchars($v['url_video']) ?>" title="<?= htmlspecialchars($v['titulo']) ?>" style="position:absolute; inset:0; width:100%; height:100%; border:0;" allowfullscreen loading="lazy"></iframe>
            </div>
            <div style="padding:1rem;">
              <strong><?= htmlspecialchars($v['titulo']) ?></strong>
              <div style="font-size:.8rem; color:var(--texto-claro); margin:.2rem 0 .5rem;"><?= $v['data_sessao'] ? date('d/m/Y', strtotime($v['data_sessao'])) : '' ?></div>
              <?php if ($v['descricao']): ?><p style="font-size:.85rem; color:var(--texto-medio);"><?= htmlspecialchars($v['descricao']) ?></p><?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
