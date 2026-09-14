<?php
$page_title = 'Relatórios e Dados do e-SIC — Câmara Municipal de Ananás';
include 'includes/header.php';

$tituloPaginaInterna = 'e-SIC — Relatórios e Dados Estatísticos';
$subtituloPaginaInterna = 'Quantidade de pedidos de acesso à informação recebidos e respondidos (Lei nº 12.527/2011).';

$total = $respondidas = $pendentes = 0;

if ($pdo) {
    $total = (int) $pdo->query('SELECT COUNT(*) FROM esic_solicitacoes')->fetchColumn();
    $respondidas = (int) $pdo->query('SELECT COUNT(*) FROM esic_solicitacoes WHERE respondida = 1')->fetchColumn();
    $pendentes = $total - $respondidas;
}

include 'includes/pagina-header.php';
?>
<section class="pagina-interna-conteudo">
  <div class="section-container">
    <div class="grid-cards" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:1rem; margin-bottom:2rem;">
      <div class="ficha-card" style="text-align:center; margin-bottom:0;"><div style="font-size:1.8rem; font-weight:700; color:var(--verde-escuro);"><?= $total ?></div><div style="font-size:.8rem; color:var(--texto-claro);">Solicitações recebidas</div></div>
      <div class="ficha-card" style="text-align:center; margin-bottom:0;"><div style="font-size:1.8rem; font-weight:700; color:var(--verde-escuro);"><?= $respondidas ?></div><div style="font-size:.8rem; color:var(--texto-claro);">Respondidas</div></div>
      <div class="ficha-card" style="text-align:center; margin-bottom:0;"><div style="font-size:1.8rem; font-weight:700; color:var(--verde-escuro);"><?= $pendentes ?></div><div style="font-size:.8rem; color:var(--texto-claro);">Em análise</div></div>
    </div>
    <p style="color:var(--texto-claro); font-size:.82rem;">
      <i class="fas fa-circle-info"></i> A discriminação entre solicitações "atendidas" e "indeferidas" será disponibilizada quando esse campo for incluído no fluxo de resposta do gerenciador.
    </p>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
