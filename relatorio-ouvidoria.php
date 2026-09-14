<?php
$page_title = 'Relatórios da Ouvidoria — Câmara Municipal de Ananás';
include 'includes/header.php';

$tituloPaginaInterna = 'Relatórios da Ouvidoria';
$subtituloPaginaInterna = 'Dados estatísticos das manifestações recebidas pela Ouvidoria.';

$total = $respondidas = $pendentes = 0;
$porTipo = [];

if ($pdo) {
    $total = (int) $pdo->query('SELECT COUNT(*) FROM manifestacoes')->fetchColumn();
    $respondidas = (int) $pdo->query('SELECT COUNT(*) FROM manifestacoes WHERE respondida = 1')->fetchColumn();
    $pendentes = $total - $respondidas;
    $porTipo = $pdo->query(
        "SELECT t.descricao, COUNT(*) AS total
         FROM manifestacoes m JOIN tipos_manifestacao t ON t.id_tipo_manifestacao = m.id_tipo_manifestacao
         GROUP BY t.descricao ORDER BY total DESC"
    )->fetchAll();
}

include 'includes/pagina-header.php';
?>
<section class="pagina-interna-conteudo">
  <div class="section-container">
    <div class="grid-cards" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:1rem; margin-bottom:2rem;">
      <div class="ficha-card" style="text-align:center; margin-bottom:0;"><div style="font-size:1.8rem; font-weight:700; color:var(--verde-escuro);"><?= $total ?></div><div style="font-size:.8rem; color:var(--texto-claro);">Total recebidas</div></div>
      <div class="ficha-card" style="text-align:center; margin-bottom:0;"><div style="font-size:1.8rem; font-weight:700; color:var(--verde-escuro);"><?= $respondidas ?></div><div style="font-size:.8rem; color:var(--texto-claro);">Respondidas</div></div>
      <div class="ficha-card" style="text-align:center; margin-bottom:0;"><div style="font-size:1.8rem; font-weight:700; color:var(--verde-escuro);"><?= $pendentes ?></div><div style="font-size:.8rem; color:var(--texto-claro);">Em análise</div></div>
    </div>

    <div class="ficha-card">
      <h3><i class="fas fa-chart-simple"></i> Manifestações por tipo</h3>
      <?php if (empty($porTipo)): ?>
        <p style="color:var(--texto-claro); font-size:.85rem;">Nenhuma manifestação registrada ainda.</p>
      <?php else: ?>
        <ul class="lista-participantes">
          <?php foreach ($porTipo as $t): ?>
            <li><span><?= htmlspecialchars($t['descricao']) ?></span><span class="tag-info"><?= $t['total'] ?></span></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
