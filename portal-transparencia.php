<?php
$page_title = 'Portal da Transparência — Câmara Municipal de Ananás';
include 'includes/header.php';

$tituloPaginaInterna = 'Portal da Transparência';
$subtituloPaginaInterna = 'Câmara Municipal de Ananás — TO. Estrutura de informações em conformidade com o padrão do Tribunal de Contas do Estado do Tocantins (TCE-TO).';

$contadores = ['licitacoes' => 0, 'contratos' => 0, 'documentos' => 0, 'servidores' => 0];
if ($pdo) {
    $contadores['licitacoes'] = (int) $pdo->query("SELECT COUNT(*) FROM licitacoes WHERE status = 1")->fetchColumn();
    $contadores['contratos']  = (int) $pdo->query("SELECT COUNT(*) FROM contratos WHERE status = 1")->fetchColumn();
    $contadores['documentos'] = (int) $pdo->query("SELECT COUNT(*) FROM documentos WHERE status = 1")->fetchColumn();
    $contadores['servidores'] = (int) $pdo->query("SELECT COUNT(*) FROM servidores WHERE ativo = 1")->fetchColumn();
}

/**
 * Estrutura de seções — mesma organização e nomenclatura usada no
 * Portal da Transparência do TCE-TO (transparencia.tceto.tc.br),
 * adaptada às competências de uma Câmara Municipal.
 * 'real' => true  : já funciona com dados do banco
 * 'real' => false : categoria criada, aguardando integração/dados
 */
$secoes = [
    'Informações Institucionais' => [
        ['icone' => 'fa-file-contract', 'nome' => 'Carta de Serviços / Competência', 'link' => 'institucional.php', 'real' => false],
        ['icone' => 'fa-address-book', 'nome' => 'Contatos', 'link' => 'contato.php', 'real' => true],
        ['icone' => 'fa-sitemap', 'nome' => 'Estrutura Organizacional', 'link' => 'institucional.php', 'real' => false],
        ['icone' => 'fa-landmark', 'nome' => 'Identidade Organizacional', 'link' => 'institucional.php', 'real' => false],
        ['icone' => 'fa-scroll', 'nome' => 'Leis Orçamentárias (PPA/LDO/LOA)', 'link' => 'leis-orcamentarias.php', 'real' => true],
    ],
    'Atividades Finalísticas' => [
        ['icone' => 'fa-video', 'nome' => 'Vídeos das Sessões Plenárias', 'link' => 'videos-sessoes.php', 'real' => false],
        ['icone' => 'fa-list-check', 'nome' => 'Pautas das Sessões', 'link' => 'pauta.php', 'real' => true],
        ['icone' => 'fa-file-signature', 'nome' => 'Atas das Sessões', 'link' => 'atas.php', 'real' => true],
    ],
    'Compras e Licitações' => [
        ['icone' => 'fa-file-contract', 'nome' => 'Licitações', 'link' => 'licitacoes.php', 'real' => true],
        ['icone' => 'fa-file-circle-xmark', 'nome' => 'Licitações Fracassadas e/ou Desertas', 'link' => 'licitacoes-fracassadas-desertas.php', 'real' => true],
        ['icone' => 'fa-bolt', 'nome' => 'Dispensas e Inexigibilidades', 'link' => 'dispensa-inexigibilidade.php', 'real' => true],
        ['icone' => 'fa-tags', 'nome' => 'Atas de Registro/Adesão de Preço', 'link' => 'atas-registro-precos.php', 'real' => true],
        ['icone' => 'fa-file-signature', 'nome' => 'Contratos', 'link' => 'contratos.php', 'real' => true],
        ['icone' => 'fa-handshake', 'nome' => 'Convênios e Instrumentos Congêneres', 'link' => 'convenios.php', 'real' => true],
        ['icone' => 'fa-calendar-check', 'nome' => 'Plano de Contratação Anual — PCA', 'link' => 'pca.php', 'real' => true],
        ['icone' => 'fa-ban', 'nome' => 'Licitantes e/ou Contratados Sancionados', 'link' => 'licitantes-sancionados.php', 'real' => false],
        ['icone' => 'fa-user-shield', 'nome' => 'Relação de Gestores e Fiscais de Contrato', 'link' => 'gestores-fiscais-contrato.php', 'real' => true],
        ['icone' => 'fa-building', 'nome' => 'Fornecedores', 'link' => 'fornecedores.php', 'real' => true],
    ],
    'Obras' => [
        ['icone' => 'fa-helmet-safety', 'nome' => 'Obras e Serviços de Engenharia', 'link' => 'obras.php', 'real' => false],
    ],
    'Recursos Humanos' => [
        ['icone' => 'fa-id-card', 'nome' => 'Quadro de Servidores', 'link' => 'servidores.php', 'real' => true],
        ['icone' => 'fa-briefcase', 'nome' => 'Cargos Comissionados e Funções', 'link' => 'recursos-humanos-complementar.php', 'real' => false],
        ['icone' => 'fa-table-list', 'nome' => 'Cargos Efetivos — Tabela Remuneratória', 'link' => 'recursos-humanos-complementar.php', 'real' => false],
        ['icone' => 'fa-money-check-dollar', 'nome' => 'Folha de Pagamento — Servidores', 'link' => 'recursos-humanos-complementar.php', 'real' => false],
        ['icone' => 'fa-user-graduate', 'nome' => 'Folha de Pagamento — Estagiários', 'link' => 'recursos-humanos-complementar.php', 'real' => false],
        ['icone' => 'fa-bullhorn', 'nome' => 'Editais de Concursos e Seleções', 'link' => 'recursos-humanos-complementar.php', 'real' => false],
        ['icone' => 'fa-people-arrows', 'nome' => 'Terceirizados', 'link' => 'recursos-humanos-complementar.php', 'real' => false],
    ],
    'Receitas e Despesas' => [
        ['icone' => 'fa-file-invoice-dollar', 'nome' => 'Empenhos', 'link' => 'receitas-despesas.php', 'real' => false],
        ['icone' => 'fa-check-double', 'nome' => 'Liquidações', 'link' => 'receitas-despesas.php', 'real' => false],
        ['icone' => 'fa-money-bill-wave', 'nome' => 'Pagamentos', 'link' => 'receitas-despesas.php', 'real' => false],
        ['icone' => 'fa-plane-departure', 'nome' => 'Diárias', 'link' => 'receitas-despesas.php', 'real' => false],
        ['icone' => 'fa-gas-pump', 'nome' => 'Gastos com Combustível', 'link' => 'receitas-despesas.php', 'real' => false],
        ['icone' => 'fa-sack-dollar', 'nome' => 'Receitas Arrecadadas', 'link' => 'receitas-despesas.php', 'real' => false],
        ['icone' => 'fa-layer-group', 'nome' => 'Informações Consolidadas', 'link' => 'receitas-despesas.php', 'real' => false],
        ['icone' => 'fa-hourglass-half', 'nome' => 'Restos a Pagar', 'link' => 'receitas-despesas.php', 'real' => false],
        ['icone' => 'fa-arrow-down-1-9', 'nome' => 'Ordem Cronológica de Pagamentos', 'link' => 'receitas-despesas.php', 'real' => false],
        ['icone' => 'fa-scale-unbalanced', 'nome' => 'Despesas Fixadas', 'link' => 'receitas-despesas.php', 'real' => false],
    ],
    'Prestação de Contas e LRF' => [
        ['icone' => 'fa-book', 'nome' => 'Demonstrativos Contábeis', 'link' => 'prestacao-contas-lrf.php', 'real' => false],
        ['icone' => 'fa-balance-scale', 'nome' => 'Relatórios — LRF (RREO/RGF)', 'link' => 'prestacao-contas-lrf.php', 'real' => false],
        ['icone' => 'fa-folder-open', 'nome' => 'Prestações de Contas', 'link' => 'prestacao-contas-lrf.php', 'real' => false],
        ['icone' => 'fa-gavel', 'nome' => 'Decisões', 'link' => 'prestacao-contas-lrf.php', 'real' => false],
    ],
    'Relatório de Gestão e Atividades' => [
        ['icone' => 'fa-calendar-days', 'nome' => 'Relatórios Anuais', 'link' => 'relatorios-gestao.php', 'real' => false],
        ['icone' => 'fa-calendar-week', 'nome' => 'Relatórios Trimestrais', 'link' => 'relatorios-gestao.php', 'real' => false],
    ],
    'Ouvidoria' => [
        ['icone' => 'fa-comments', 'nome' => 'Site da Ouvidoria', 'link' => 'ouvidoria.php', 'real' => true],
        ['icone' => 'fa-chart-simple', 'nome' => 'Relatórios da Ouvidoria', 'link' => 'relatorio-ouvidoria.php', 'real' => true],
    ],
    'SIC — Serviço de Informação ao Cidadão' => [
        ['icone' => 'fa-circle-info', 'nome' => 'Sobre o SIC', 'link' => 'sobre-sic.php', 'real' => true],
        ['icone' => 'fa-scale-balanced', 'nome' => 'Fazer Solicitação', 'link' => 'esic.php', 'real' => true],
        ['icone' => 'fa-chart-simple', 'nome' => 'Relatórios e Dados', 'link' => 'relatorio-esic.php', 'real' => true],
    ],
    'LGPD e Governo Digital' => [
        ['icone' => 'fa-shield-halved', 'nome' => 'LGPD e Dados Abertos', 'link' => 'lgpd-dados-abertos.php', 'real' => false],
    ],
    'Demais Informações' => [
        ['icone' => 'fa-gavel', 'nome' => 'Atos Normativos (Leis, Decretos, Portarias...)', 'link' => 'leis.php', 'real' => true],
        ['icone' => 'fa-boxes-stacked', 'nome' => 'Patrimônio e Almoxarifado', 'link' => 'patrimonio.php', 'real' => false],
    ],
];

include 'includes/pagina-header.php';
?>

<div class="transp-topbar">
  <div class="section-container">
    <a href="glossario.php"><i class="fas fa-book-open"></i> Glossário</a>
    <a href="legislacao-transparencia.php"><i class="fas fa-gavel"></i> Legislação</a>
    <a href="perguntas-frequentes.php"><i class="fas fa-circle-question"></i> Perguntas Frequentes</a>
    <a href="mapa-site.php"><i class="fas fa-sitemap"></i> Mapa do Site</a>
    <a href="contato.php"><i class="fas fa-envelope"></i> Fale Conosco</a>
  </div>
</div>

<section class="pagina-interna-conteudo">
  <div class="section-container">

    <div class="transp-contadores">
      <div class="transp-contador"><div class="transp-contador__icone"><i class="fas fa-file-contract"></i></div><div><div class="num"><?= $contadores['licitacoes'] ?></div><div class="lbl">Licitações publicadas</div></div></div>
      <div class="transp-contador"><div class="transp-contador__icone"><i class="fas fa-file-signature"></i></div><div><div class="num"><?= $contadores['contratos'] ?></div><div class="lbl">Contratos vigentes</div></div></div>
      <div class="transp-contador"><div class="transp-contador__icone"><i class="fas fa-file-lines"></i></div><div><div class="num"><?= $contadores['documentos'] ?></div><div class="lbl">Documentos publicados</div></div></div>
      <div class="transp-contador"><div class="transp-contador__icone"><i class="fas fa-id-card"></i></div><div><div class="num"><?= $contadores['servidores'] ?></div><div class="lbl">Servidores ativos</div></div></div>
    </div>

    <?php $i = 0; foreach ($secoes as $nomeSecao => $itens): ?>
      <div class="transp-secao transp-secao--<?= $i % 12 ?>">
        <div class="transp-secao__titulo"><i class="fas fa-layer-group"></i> <?= htmlspecialchars($nomeSecao) ?></div>
        <div class="transp-secao__corpo">
          <?php foreach ($itens as $item): ?>
            <a href="<?= htmlspecialchars($item['link']) ?>" class="transp-item <?= $item['real'] ? '' : 'pendente' ?>">
              <i class="fas <?= htmlspecialchars($item['icone']) ?>"></i>
              <span><?= htmlspecialchars($item['nome']) ?></span>
              <?php if (!$item['real']): ?><span class="badge-pendente-mini">em breve</span><?php endif; ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php $i++; endforeach; ?>

    <p style="font-size:.78rem; color:var(--texto-claro); margin-top:1rem;">
      <i class="fas fa-shield-halved"></i> Estrutura de categorias em conformidade com a Cartilha de Transparência Pública do TCE-TO e com a Lei nº 12.527/2011.
      Itens marcados "em breve" estão com a categoria criada, aguardando integração de dados.
    </p>

  </div>
</section>

<?php include 'includes/footer.php'; ?>
