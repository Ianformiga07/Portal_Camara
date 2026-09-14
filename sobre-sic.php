<?php
$page_title = 'Sobre o SIC — Câmara Municipal de Ananás';
include 'includes/header.php';

$tituloPaginaInterna = 'Sobre o SIC';
$subtituloPaginaInterna = 'Serviço de Informação ao Cidadão da Câmara Municipal de Ananás.';

include 'includes/pagina-header.php';
?>
<section class="pagina-interna-conteudo">
  <div class="section-container">
    <div class="ficha-card">
      <h3><i class="fas fa-scale-balanced"></i> O que é o SIC</h3>
      <p style="font-size:.92rem; color:var(--texto-medio); line-height:1.7;">
        O Serviço de Informação ao Cidadão (SIC) é o canal pelo qual qualquer pessoa pode solicitar
        acesso a informações públicas produzidas ou custodiadas pela Câmara Municipal de Ananás,
        conforme a Lei nº 12.527/2011 (Lei de Acesso à Informação).
      </p>
      <p style="font-size:.92rem; color:var(--texto-medio); line-height:1.7;">
        A solicitação não exige justificativa do motivo do pedido, nem identificação obrigatória —
        é possível solicitar de forma anônima.
      </p>
    </div>

    <div class="ficha-card">
      <h3><i class="fas fa-clock"></i> Prazos</h3>
      <p style="font-size:.92rem; color:var(--texto-medio);">Prazo de resposta: até 20 dias corridos, prorrogável por mais 10 dias mediante justificativa.</p>
    </div>

    <div class="ficha-card">
      <h3><i class="fas fa-paper-plane"></i> Como solicitar</h3>
      <p style="font-size:.92rem; color:var(--texto-medio); margin-bottom:1rem;">Preencha o formulário eletrônico disponível no link abaixo.</p>
      <a href="esic.php" class="btn-leia-mais">Fazer uma solicitação <i class="fas fa-arrow-right"></i></a>
    </div>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
