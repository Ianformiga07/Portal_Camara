<?php
$page_title = 'Receitas e Despesas — Câmara Municipal de Ananás';
require_once 'includes/funcoes.php';
include 'includes/header.php';

$tituloPaginaInterna = 'Receitas e Despesas';
$subtituloPaginaInterna = 'Execução orçamentária da Câmara Municipal de Ananás.';

$categorias = [
    'empenhos'                 => 'Empenhos',
    'liquidacoes'               => 'Liquidações',
    'pagamentos'                => 'Pagamentos',
    'diarias'                   => 'Diárias',
    'combustivel'                => 'Gastos com Combustível',
    'receitas-arrecadadas'       => 'Receitas Arrecadadas',
    'informacoes-consolidadas'    => 'Informações Consolidadas',
    'restos-a-pagar'             => 'Restos a Pagar',
    'ordem-cronologica'          => 'Ordem Cronológica de Pagamentos',
    'despesas-fixadas'          => 'Despesas Fixadas',
];

$catAtual = $_GET['cat'] ?? 'empenhos';
if (!isset($categorias[$catAtual])) {
    $catAtual = 'empenhos';
}
$ano = $_GET['ano'] ?? '';

$registros = [];
$anosDisponiveis = [];
$total = 0;

if ($pdo) {
    $stmtAnos = $pdo->prepare('SELECT DISTINCT ano FROM execucao_orcamentaria WHERE categoria = :cat ORDER BY ano DESC');
    $stmtAnos->execute(['cat' => $catAtual]);
    $anosDisponiveis = $stmtAnos->fetchAll(PDO::FETCH_COLUMN);

    $condicoes = ['categoria = :cat', 'status = 1'];
    $parametros = ['cat' => $catAtual];
    if ($ano !== '') {
        $condicoes[] = 'ano = :ano';
        $parametros['ano'] = $ano;
    }
    $whereSql = 'WHERE ' . implode(' AND ', $condicoes);

    $stmt = $pdo->prepare("SELECT * FROM execucao_orcamentaria $whereSql ORDER BY data_referencia DESC, id_execucao DESC");
    $stmt->execute($parametros);
    $registros = $stmt->fetchAll();

    foreach ($registros as $r) {
        $total += (float) $r['valor'];
    }
}

include 'includes/pagina-header.php';

function manterFiltrosOrc(array $substituir = []): string
{
    $params = array_merge($_GET, $substituir);
    return htmlspecialchars('?' . http_build_query($params));
}
?>

<style>
  .subtabs-orc { display:flex; flex-wrap:wrap; gap:.5rem; margin-bottom:1.2rem; }
  .subtabs-orc a {
    padding:.5rem .9rem; border-radius:var(--radius-sm); font-size:.82rem; font-weight:600;
    background:var(--cinza-claro); color:var(--texto-medio); text-decoration:none; border:1px solid var(--cinza-borda);
  }
  .subtabs-orc a.ativo { background:var(--verde-medio); color:#fff; border-color:var(--verde-medio); }
  .orc-total { background:var(--verde-suave); border:1px solid var(--verde-borda); border-radius:var(--radius-sm); padding:.9rem 1.1rem; margin-bottom:1.2rem; font-size:.95rem; }
  .orc-total strong { color:var(--verde-escuro); }
</style>

<section class="pagina-interna-conteudo">
  <div class="section-container">

    <div class="alerta-portal aviso" style="max-width:none; margin-bottom:1.2rem;">
      <i class="fas fa-circle-info"></i>
      Estrutura em conformidade com o padrão do TCE-TO. Os valores exibidos aqui refletem os lançamentos cadastrados pelo setor financeiro da Câmara.
    </div>

    <div class="subtabs-orc">
      <?php foreach ($categorias as $slug => $nome): ?>
        <a href="?cat=<?= $slug ?>" class="<?= $catAtual === $slug ? 'ativo' : '' ?>"><?= htmlspecialchars($nome) ?></a>
      <?php endforeach; ?>
    </div>

    <form method="get" class="toolbar-filtro" style="margin-bottom:1rem;">
      <input type="hidden" name="cat" value="<?= htmlspecialchars($catAtual) ?>">
      <select name="ano" onchange="this.form.submit()">
        <option value="">Todos os anos</option>
        <?php foreach ($anosDisponiveis as $a): ?>
          <option value="<?= $a ?>" <?= (string) $ano === (string) $a ? 'selected' : '' ?>><?= $a ?></option>
        <?php endforeach; ?>
      </select>
    </form>

    <div class="orc-total">
      <i class="fas fa-sack-dollar"></i> Total em <strong><?= htmlspecialchars($categorias[$catAtual]) ?></strong><?= $ano !== '' ? ' em ' . htmlspecialchars($ano) : '' ?>:
      <strong>R$ <?= number_format($total, 2, ',', '.') ?></strong> (<?= count($registros) ?> lançamento<?= count($registros) === 1 ? '' : 's' ?>)
    </div>

    <?php if (empty($registros)): ?>
      <div class="vazio-lista">
        <i class="fas fa-file-invoice-dollar"></i>
        <p>Nenhum lançamento encontrado nesta categoria<?= $ano !== '' ? ' para ' . htmlspecialchars($ano) : '' ?>.</p>
      </div>
    <?php else: ?>
      <div class="tabela-publica-wrap">
        <div class="tabela-publica-scroll">
          <table class="tabela-publica" style="width:100%;">
            <thead>
              <tr>
                <th>Nº</th>
                <th>Descrição</th>
                <th>Favorecido</th>
                <th>Data</th>
                <th>Valor</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($registros as $r): ?>
                <tr class="linha-sem-clique">
                  <td><?php if ($r['numero']): ?><span class="badge-pill">Nº <?= htmlspecialchars($r['numero']) ?></span><?php else: ?>—<?php endif; ?></td>
                  <td><?= htmlspecialchars($r['descricao']) ?></td>
                  <td><?= $r['favorecido'] ? htmlspecialchars($r['favorecido']) : '—' ?></td>
                  <td><?= $r['data_referencia'] ? date('d/m/Y', strtotime($r['data_referencia'])) : '—' ?></td>
                  <td>R$ <?= number_format((float) $r['valor'], 2, ',', '.') ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php include 'includes/footer.php'; ?>
