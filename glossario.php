<?php
$page_title = 'Glossário — Câmara Municipal de Ananás';
include 'includes/header.php';

$tituloPaginaInterna = 'Glossário';
$subtituloPaginaInterna = 'Significado dos termos técnicos usados neste Portal da Transparência.';

$termos = [
    ['Ata de Registro de Preços', 'Documento que registra os preços de bens/serviços vencedores de uma licitação, para contratações futuras dentro da validade da ata.'],
    ['Contrato', 'Instrumento que formaliza a contratação de uma empresa ou pessoa física após licitação, dispensa ou inexigibilidade.'],
    ['Diária', 'Valor pago a servidor para custear despesas de deslocamento a serviço da Câmara.'],
    ['Diário Oficial', 'Veículo oficial de publicação dos atos administrativos e legislativos da Câmara.'],
    ['Dispensa de Licitação', 'Contratação direta permitida por lei em situações específicas, sem necessidade de licitação.'],
    ['e-SIC', 'Serviço de Informação ao Cidadão — canal para solicitar acesso a informações públicas, conforme a Lei nº 12.527/2011.'],
    ['Empenho', 'Ato que reserva o valor de uma despesa no orçamento, antes do pagamento efetivo.'],
    ['Fiscal de Contrato', 'Servidor designado para acompanhar a execução de um contrato.'],
    ['Gestor de Contrato', 'Servidor responsável pela gestão administrativa de um contrato.'],
    ['Inexigibilidade', 'Contratação direta quando a licitação é inviável, por exemplo por fornecedor exclusivo.'],
    ['LAI', 'Lei de Acesso à Informação (Lei nº 12.527/2011) — garante ao cidadão o direito de acessar informações públicas.'],
    ['LGPD', 'Lei Geral de Proteção de Dados Pessoais (Lei nº 13.709/2018).'],
    ['Licitação', 'Processo público para selecionar a melhor proposta na contratação de bens, serviços ou obras.'],
    ['Liquidação', 'Etapa que confirma que um bem foi entregue ou serviço prestado, antes do pagamento.'],
    ['LRF', 'Lei de Responsabilidade Fiscal (Lei Complementar nº 101/2000).'],
    ['Ouvidoria', 'Canal para o cidadão registrar denúncias, elogios, reclamações e sugestões.'],
    ['PCA', 'Plano de Contratação Anual — planejamento das contratações previstas para o exercício.'],
    ['Pregoeiro(a)', 'Servidor(a) responsável por conduzir o processo de pregão.'],
    ['Protocolo', 'Número único gerado para acompanhar uma manifestação ou solicitação enviada pelo cidadão.'],
];

include 'includes/pagina-header.php';
?>
<section class="pagina-interna-conteudo">
  <div class="section-container">
    <div class="ficha-card">
      <?php foreach ($termos as [$termo, $definicao]): ?>
        <div style="padding:.85rem 0; border-bottom:1px solid var(--cinza-borda);">
          <strong style="color:var(--verde-escuro);"><?= htmlspecialchars($termo) ?></strong>
          <p style="margin:.3rem 0 0; font-size:.88rem; color:var(--texto-medio);"><?= htmlspecialchars($definicao) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
