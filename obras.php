<?php
$page_title = 'Obras — Câmara Municipal de Ananás';
require_once 'includes/funcoes.php';
include 'includes/header.php';

$tituloPaginaInterna = 'Obras';
$subtituloPaginaInterna = 'Obras e serviços de engenharia contratados pela Câmara Municipal.';

$obras = $pdo ? $pdo->query('SELECT * FROM obras WHERE status = 1 ORDER BY data_inicio DESC')->fetchAll() : [];

$situacoes = [
    'planejada'  => ['label' => 'Planejada',  'cor' => '#78847e'],
    'andamento'  => ['label' => 'Em andamento', 'cor' => '#c9a227'],
    'concluida'  => ['label' => 'Concluída',  'cor' => '#43715a'],
    'paralisada' => ['label' => 'Paralisada', 'cor' => '#b3403a'],
];

include 'includes/pagina-header.php';
?>

<section class="pagina-interna-conteudo">
  <div class="section-container">
    <?php if (empty($obras)): ?>
      <div class="vazio-lista"><i class="fas fa-helmet-safety"></i><p>Nenhuma obra cadastrada no momento.</p></div>
    <?php else: ?>
      <?php foreach ($obras as $o): ?>
        <?php $sit = $situacoes[$o['situacao']] ?? $situacoes['planejada']; ?>
        <div class="ficha-card" style="margin-bottom:1.2rem;">
          <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:.5rem;">
            <h3 style="margin:0;"><?= htmlspecialchars($o['nome']) ?></h3>
            <span style="background:<?= $sit['cor'] ?>; color:#fff; padding:.25rem .7rem; border-radius:999px; font-size:.75rem; font-weight:600;"><?= $sit['label'] ?></span>
          </div>
          <?php if ($o['descricao']): ?><p style="margin-top:.5rem; color:var(--texto-medio);"><?= htmlspecialchars($o['descricao']) ?></p><?php endif; ?>
          <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:.8rem; margin-top:.9rem; font-size:.85rem;">
            <div><strong>Empresa</strong><br><?= $o['empresa_responsavel'] ? htmlspecialchars($o['empresa_responsavel']) : '—' ?></div>
            <div><strong>Valor</strong><br><?= $o['valor'] !== null ? 'R$ ' . number_format((float) $o['valor'], 2, ',', '.') : '—' ?></div>
            <div><strong>Início</strong><br><?= $o['data_inicio'] ? date('d/m/Y', strtotime($o['data_inicio'])) : '—' ?></div>
            <div><strong>Previsão de término</strong><br><?= $o['previsao_termino'] ? date('d/m/Y', strtotime($o['previsao_termino'])) : '—' ?></div>
            <div><strong>Local</strong><br><?= $o['localizacao'] ? htmlspecialchars($o['localizacao']) : '—' ?></div>
          </div>
          <div style="margin-top:.8rem;">
            <div style="background:var(--cinza-borda); border-radius:999px; height:8px; overflow:hidden;">
              <div style="width:<?= (int) $o['percentual_execucao'] ?>%; background:var(--verde-medio); height:100%;"></div>
            </div>
            <small style="color:var(--texto-claro);"><?= (int) $o['percentual_execucao'] ?>% executado</small>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
