<?php
$page_title = 'Legislação — Câmara Municipal de Ananás';
include 'includes/header.php';

$tituloPaginaInterna = 'Legislação sobre Transparência';
$subtituloPaginaInterna = 'Base legal que fundamenta a publicação das informações neste portal.';

$leis = [
    ['Constituição Federal de 1988', 'Art. 5º, XXXIII; art. 37, §3º, II; art. 216, §2º — direito de acesso à informação pública.'],
    ['Lei Federal nº 12.527/2011', 'Lei de Acesso à Informação (LAI) — regula o acesso a informações públicas.'],
    ['Decreto Federal nº 7.724/2012', 'Regulamenta a Lei de Acesso à Informação no âmbito federal, usado como referência.'],
    ['Lei Federal nº 13.709/2018', 'Lei Geral de Proteção de Dados Pessoais (LGPD).'],
    ['Lei Complementar nº 101/2000', 'Lei de Responsabilidade Fiscal (LRF).'],
    ['Lei Federal nº 4.320/1964', 'Normas gerais de direito financeiro para elaboração de orçamentos e balanços.'],
    ['Lei Federal nº 14.133/2021', 'Lei de Licitações e Contratos Administrativos.'],
    ['Resolução Atricon nº 01/2022', 'Altera as Diretrizes de Controle Externo e a Matriz de Fiscalização de Transparência Pública.'],
];

include 'includes/pagina-header.php';
?>
<section class="pagina-interna-conteudo">
  <div class="section-container">
    <div class="ficha-card">
      <?php foreach ($leis as [$lei, $desc]): ?>
        <div style="padding:.85rem 0; border-bottom:1px solid var(--cinza-borda);">
          <strong style="color:var(--verde-escuro);"><i class="fas fa-gavel"></i> <?= htmlspecialchars($lei) ?></strong>
          <p style="margin:.3rem 0 0; font-size:.88rem; color:var(--texto-medio);"><?= htmlspecialchars($desc) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
