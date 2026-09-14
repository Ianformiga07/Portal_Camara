<?php
$page_title = 'Perguntas Frequentes — Câmara Municipal de Ananás';
include 'includes/header.php';

$tituloPaginaInterna = 'Perguntas Frequentes';
$subtituloPaginaInterna = 'Dúvidas comuns sobre o Portal da Transparência da Câmara Municipal de Ananás.';

$perguntas = [
    ['Como faço para pedir uma informação que não está no portal?', 'Utilize o canal e-SIC. O prazo legal de resposta é de até 20 dias corridos, prorrogável por mais 10 dias mediante justificativa.'],
    ['Preciso me identificar para enviar uma manifestação ou pedido?', 'Não. Tanto a Ouvidoria quanto o e-SIC permitem manifestação anônima.'],
    ['Como acompanho minha manifestação ou solicitação?', 'Guarde o número de protocolo gerado no envio e utilize a página de consulta (Ouvidoria ou e-SIC) para acompanhar.'],
    ['Os dados de servidores mostram CPF, telefone ou salário?', 'Não. Em conformidade com a LGPD, são exibidos apenas dados funcionais de caráter público: nome, cargo e lotação.'],
    ['Por que algumas seções aparecem como "aguardando integração"?', 'Algumas informações (folha de pagamento, execução orçamentária, relatórios contábeis) dependem de integração com o sistema contábil da Câmara e estão sendo estruturadas gradualmente.'],
    ['Onde encontro os processos licitatórios oficiais?', 'Além deste portal, toda licitação, dispensa e contrato também é publicado no Portal Nacional de Contratações Públicas (PNCP), conforme exige a Lei nº 14.133/2021.'],
];

include 'includes/pagina-header.php';
?>
<section class="pagina-interna-conteudo">
  <div class="section-container">
    <div class="ficha-card">
      <?php foreach ($perguntas as [$pergunta, $resposta]): ?>
        <div style="padding:.85rem 0; border-bottom:1px solid var(--cinza-borda);">
          <strong style="color:var(--verde-escuro);"><i class="fas fa-circle-question"></i> <?= htmlspecialchars($pergunta) ?></strong>
          <p style="margin:.3rem 0 0; font-size:.88rem; color:var(--texto-medio);"><?= htmlspecialchars($resposta) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
