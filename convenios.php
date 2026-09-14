<?php
$page_title = 'Convênios — Câmara Municipal de Ananás';
require_once 'includes/funcoes.php';
include 'includes/header.php';

$tituloPaginaInterna = 'Convênios e Instrumentos Congêneres';
$subtituloPaginaInterna = 'Convênios, acordos de cooperação e termos firmados pela Câmara Municipal.';

$convenios = $pdo ? $pdo->query("SELECT * FROM convenios WHERE status = 1 ORDER BY data_inicio DESC")->fetchAll() : [];

include 'includes/pagina-header.php';
?>
<section class="pagina-interna-conteudo">
  <div class="section-container">
    <?php if (empty($convenios)): ?>
      <div class="vazio-lista">
        <i class="fas fa-handshake"></i>
        <p>Nenhum convênio cadastrado no momento.</p>
      </div>
    <?php else: ?>
      <?php foreach ($convenios as $c): ?>
        <div class="arquivo-lista-item">
          <div class="icone-pdf"><i class="fas fa-handshake"></i></div>
          <div class="info">
            <strong><?= htmlspecialchars($c['tipo'] ?: 'Convênio') ?> Nº <?= htmlspecialchars($c['numero'] ?: '—') ?>/<?= htmlspecialchars($c['ano_exercicio']) ?> — <?= htmlspecialchars($c['participe'] ?: '') ?></strong>
            <span>
              <?= $c['data_inicio'] ? date('d/m/Y', strtotime($c['data_inicio'])) : '—' ?> a <?= $c['data_fim'] ? date('d/m/Y', strtotime($c['data_fim'])) : '—' ?>
              <?= $c['situacao'] ? ' · ' . htmlspecialchars($c['situacao']) : '' ?>
              <?= $c['valor'] !== null ? ' · R$ ' . number_format($c['valor'], 2, ',', '.') : '' ?>
            </span>
            <?php if ($c['objeto']): ?><p style="margin-top:.3rem; font-size:.85rem; color:var(--texto-medio);"><?= htmlspecialchars(resumirTexto($c['objeto'], 160)) ?></p><?php endif; ?>
          </div>
          <?php if ($c['arquivo']): ?>
            <div class="acoes"><a href="gerenciador/assets/uploads/convenios/<?= htmlspecialchars($c['arquivo']) ?>" target="_blank" title="Ver documento"><i class="fas fa-eye"></i></a></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
