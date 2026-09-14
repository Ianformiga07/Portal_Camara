<?php
$page_title = 'História — Câmara Municipal de Ananás';
include 'includes/header.php';

$tituloPaginaInterna = 'História de Ananás';
$subtituloPaginaInterna = 'Conheça a história e os dados do município de Ananás — Tocantins.';

$historia = $pdo ? $pdo->query('SELECT * FROM historia_municipio ORDER BY id_historia LIMIT 1')->fetch() : null;

include 'includes/pagina-header.php';
?>

<section class="pagina-interna-conteudo">
  <div class="section-container">
    <div class="vereador-perfil">
      <div class="vereador-perfil-foto" style="aspect-ratio:4/3;">
        <?php if ($historia && !empty($historia['imagem_cidade'])): ?>
          <img src="gerenciador/assets/uploads/institucional/<?= htmlspecialchars($historia['imagem_cidade']) ?>" alt="Ananás — Tocantins">
        <?php else: ?>
          <i class="fas fa-city"></i>
        <?php endif; ?>
      </div>

      <div>
        <?php if ($historia): ?>
          <div class="vereador-perfil-tags">
            <?php if ($historia['ano_fundacao']): ?><span class="tag-info"><i class="fas fa-flag"></i> Fundada em <?= htmlspecialchars($historia['ano_fundacao']) ?></span><?php endif; ?>
            <?php if ($historia['dia_aniversario'] && $historia['mes_aniversario']): ?><span class="tag-info"><i class="fas fa-cake-candles"></i> Aniversário: <?= (int) $historia['dia_aniversario'] ?> de <?= htmlspecialchars($historia['mes_aniversario']) ?></span><?php endif; ?>
            <?php if ($historia['populacao']): ?><span class="tag-info"><i class="fas fa-users"></i> <?= number_format($historia['populacao'], 0, ',', '.') ?> habitantes</span><?php endif; ?>
            <?php if ($historia['area_km2']): ?><span class="tag-info"><i class="fas fa-map"></i> <?= number_format($historia['area_km2'], 1, ',', '.') ?> km²</span><?php endif; ?>
          </div>
        <?php endif; ?>

        <?php if ($historia && !empty($historia['conteudo'])): ?>
          <?php foreach (array_filter(preg_split('/\r\n|\r|\n/', trim($historia['conteudo']))) as $p): ?>
            <p style="margin-bottom:1rem; line-height:1.75;"><?= htmlspecialchars(trim($p)) ?></p>
          <?php endforeach; ?>
        <?php else: ?>
          <p>A história do município ainda não foi cadastrada.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
