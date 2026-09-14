<?php
$page_title = 'Mapa do Site — Câmara Municipal de Ananás';
include 'includes/header.php';

$tituloPaginaInterna = 'Mapa do Site';
$subtituloPaginaInterna = 'Todas as páginas disponíveis neste portal, organizadas por categoria.';

$mapa = [
    'Institucional' => ['index.php' => 'Início', 'historia.php' => 'História', 'vereadores.php' => 'Vereadores', 'mesa-diretora.php' => 'Mesa Diretora', 'comissoes.php' => 'Comissões', 'institucional.php' => 'Informações Institucionais'],
    'Legislação' => ['leis.php' => 'Leis Municipais', 'decretos.php' => 'Decretos', 'portarias.php' => 'Portarias', 'resolucoes.php' => 'Resoluções', 'projetos.php' => 'Projetos de Lei', 'lei-organica.php' => 'Lei Orgânica', 'regimento.php' => 'Regimento Interno', 'atas.php' => 'Atas das Sessões', 'pauta.php' => 'Pauta das Sessões', 'diario-oficial.php' => 'Diário Oficial'],
    'Compras e Licitações' => ['licitacoes.php' => 'Licitações', 'licitacoes-fracassadas-desertas.php' => 'Licitações Fracassadas/Desertas', 'dispensa-inexigibilidade.php' => 'Dispensas e Inexigibilidades', 'atas-registro-precos.php' => 'Atas de Registro de Preços', 'contratos.php' => 'Contratos', 'convenios.php' => 'Convênios', 'pca.php' => 'Plano de Contratação Anual', 'licitantes-sancionados.php' => 'Licitantes Sancionados', 'gestores-fiscais-contrato.php' => 'Gestores e Fiscais de Contrato', 'fornecedores.php' => 'Fornecedores'],
    'Obras' => ['obras.php' => 'Obras e Serviços de Engenharia'],
    'Recursos Humanos' => ['servidores.php' => 'Quadro de Servidores', 'recursos-humanos-complementar.php' => 'Cargos, Remuneração e Folha de Pagamento'],
    'Receitas e Despesas' => ['receitas-despesas.php' => 'Execução Orçamentária', 'leis-orcamentarias.php' => 'Leis Orçamentárias'],
    'Prestação de Contas' => ['prestacao-contas-lrf.php' => 'Prestação de Contas e LRF'],
    'Relatórios' => ['relatorios-gestao.php' => 'Relatórios de Gestão e Atividades'],
    'Ouvidoria' => ['ouvidoria.php' => 'Fazer manifestação', 'ouvidoria-consultar.php' => 'Consultar manifestação', 'relatorio-ouvidoria.php' => 'Relatórios da Ouvidoria'],
    'SIC' => ['sobre-sic.php' => 'Sobre o SIC', 'esic.php' => 'Fazer solicitação', 'esic-consultar.php' => 'Consultar solicitação', 'relatorio-esic.php' => 'Relatórios e Dados'],
    'Outros' => ['portal-transparencia.php' => 'Portal da Transparência', 'noticias.php' => 'Notícias', 'contato.php' => 'Fale Conosco', 'glossario.php' => 'Glossário', 'legislacao-transparencia.php' => 'Legislação sobre Transparência', 'perguntas-frequentes.php' => 'Perguntas Frequentes', 'lgpd-dados-abertos.php' => 'LGPD e Governo Digital', 'patrimonio.php' => 'Patrimônio e Almoxarifado'],
];

include 'includes/pagina-header.php';
?>
<section class="pagina-interna-conteudo">
  <div class="section-container">
    <?php foreach ($mapa as $categoria => $paginas): ?>
      <div class="ficha-card">
        <h3><i class="fas fa-folder"></i> <?= htmlspecialchars($categoria) ?></h3>
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px,1fr)); gap:.5rem;">
          <?php foreach ($paginas as $url => $nome): ?>
            <a href="<?= htmlspecialchars($url) ?>" style="font-size:.88rem; color:var(--verde-medio); padding:.3rem 0;"><i class="fas fa-angle-right"></i> <?= htmlspecialchars($nome) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
