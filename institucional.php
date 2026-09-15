<?php
$page_title = 'Informações Institucionais — Câmara Municipal de Ananás';
require_once 'includes/funcoes.php';
include 'includes/header.php';

$tituloPaginaInterna = 'Informações Institucionais';
$subtituloPaginaInterna = 'Competência, carta de serviços, estrutura organizacional e identidade institucional da Câmara.';

$dados = $pdo ? $pdo->query('SELECT * FROM institucional_conteudo WHERE id = 1')->fetch() : null;
$dados = $dados ?: [];

include 'includes/pagina-header.php';
?>

<section class="pagina-interna-conteudo">
  <div class="section-container">

    <div class="ficha-card" style="margin-bottom:1.5rem;">
      <h2 style="margin-bottom:.6rem;"><i class="fas fa-scale-balanced"></i> Competência</h2>
      <p style="white-space:pre-line;"><?= nl2br(htmlspecialchars($dados['competencia'] ?? 'Informação em atualização.')) ?></p>
    </div>

    <div class="ficha-card" style="margin-bottom:1.5rem;">
      <h2 style="margin-bottom:.6rem;"><i class="fas fa-file-contract"></i> Carta de Serviços ao Usuário</h2>
      <p style="white-space:pre-line;"><?= nl2br(htmlspecialchars($dados['carta_servicos'] ?? 'Informação em atualização.')) ?></p>
    </div>

    <div class="ficha-card" style="margin-bottom:1.5rem;">
      <h2 style="margin-bottom:.6rem;"><i class="fas fa-sitemap"></i> Estrutura Organizacional</h2>
      <p style="white-space:pre-line;"><?= nl2br(htmlspecialchars($dados['estrutura_organizacional'] ?? 'Informação em atualização.')) ?></p>
    </div>

    <div class="ficha-card">
      <h2 style="margin-bottom:.6rem;"><i class="fas fa-landmark"></i> Identidade Organizacional</h2>
      <div style="display:grid; gap:1rem; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); margin-top:.8rem;">
        <div>
          <strong>Missão</strong>
          <p style="margin-top:.3rem;"><?= nl2br(htmlspecialchars($dados['identidade_missao'] ?? '—')) ?></p>
        </div>
        <div>
          <strong>Visão</strong>
          <p style="margin-top:.3rem;"><?= nl2br(htmlspecialchars($dados['identidade_visao'] ?? '—')) ?></p>
        </div>
        <div>
          <strong>Valores</strong>
          <p style="margin-top:.3rem;"><?= nl2br(htmlspecialchars($dados['identidade_valores'] ?? '—')) ?></p>
        </div>
      </div>
    </div>

  </div>
</section>

<?php include 'includes/footer.php'; ?>
