<?php
// Coloque este bloco de PHP em algum ponto ANTES do <section class="hero-carousel">,
// junto com os outros $pdo->query(...) que já existem no topo do index.php.
$heroSlides = $pdo
    ? $pdo->query("SELECT * FROM hero_slides WHERE status = 1 ORDER BY ordem ASC, id_slide ASC")->fetchAll()
    : [];
?>

<!-- SUBSTITUA da linha "<section class="hero-carousel"" até "<!-- /Banner -->" por isto: -->

<section class="hero-carousel" id="banner" aria-label="Destaques">

<?php if (!empty($heroSlides)): ?>
  <?php foreach ($heroSlides as $i => $s): ?>
    <div class="slide<?= $i === 0 ? ' active' : '' ?>" style="background-image: url('<?= htmlspecialchars($s['imagem'] ? 'gerenciador/assets/uploads/hero/' . $s['imagem'] : 'assets/img/hero-carousel/slide1.jpg') ?>');">
      <div class="slide-overlay"></div>
      <div class="slide-content">
        <h2><?= htmlspecialchars($s['titulo']) ?></h2>
        <?php if ($s['texto']): ?>
          <p><?= htmlspecialchars($s['texto']) ?></p>
        <?php endif; ?>
        <?php if ($s['link_botao'] && $s['texto_botao']): ?>
          <a href="<?= htmlspecialchars($s['link_botao']) ?>" class="btn-slide">
            <i class="fas <?= htmlspecialchars($s['icone_botao'] ?: 'fa-arrow-right') ?>"></i> <?= htmlspecialchars($s['texto_botao']) ?>
          </a>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
<?php else: ?>
  <!-- Nenhum slide cadastrado no gerenciador ainda: mantém o slide padrão de fallback -->
  <div class="slide active" style="background-image: url('assets/img/hero-carousel/slide1.jpg');">
    <div class="slide-overlay"></div>
    <div class="slide-content">
      <h2>Transparência e Democracia a Serviço do Cidadão</h2>
      <p>Acompanhe as atividades legislativas, sessões plenárias e decisões que moldam o futuro de Ananás.</p>
    </div>
  </div>
<?php endif; ?>

  <div class="carousel-controles">
    <button class="carousel-prev" aria-label="Anterior"><i class="fas fa-chevron-left"></i></button>
    <div class="carousel-dots">
      <?php $totalSlides = max(1, count($heroSlides)); ?>
      <?php for ($i = 0; $i < $totalSlides; $i++): ?>
        <button class="dot<?= $i === 0 ? ' active' : '' ?>" aria-label="Slide <?= $i + 1 ?>"></button>
      <?php endfor; ?>
    </div>
    <button class="carousel-next" aria-label="Próximo"><i class="fas fa-chevron-right"></i></button>
  </div>

</section>
<!-- /Banner -->
