<?php
$page_title = 'Licitações — Câmara Municipal de Ananás';
require_once 'includes/funcoes.php';
include 'includes/header.php';

$tituloPaginaInterna = 'Licitações';
$subtituloPaginaInterna = 'Processos licitatórios da Câmara Municipal de Ananás — Portal da Transparência.';

$busca      = trim($_GET['busca'] ?? '');
$modalidade = $_GET['modalidade'] ?? '';
$pagina     = max(1, (int) ($_GET['pagina'] ?? 1));
$porPagina  = 10;

$licitacoes = [];
$modalidades = [];
$totalPaginas = 1;

if ($pdo) {
    $modalidades = $pdo->query('SELECT id_modalidade, descricao FROM licitacao_modalidades ORDER BY descricao')->fetchAll();

    $condicoes = ['l.status = 1'];
    $parametros = [];
    if ($busca !== '') {
        $condicoes[] = '(l.numero_processo LIKE :busca OR l.numero_licitacao LIKE :busca OR l.objeto LIKE :busca)';
        $parametros['busca'] = '%' . $busca . '%';
    }
    if ($modalidade !== '') {
        $condicoes[] = 'l.id_modalidade = :modalidade';
        $parametros['modalidade'] = $modalidade;
    }
    $whereSql = 'WHERE ' . implode(' AND ', $condicoes);

    $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM licitacoes l $whereSql");
    $stmtTotal->execute($parametros);
    $totalRegistros = (int) $stmtTotal->fetchColumn();
    $totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
    $pagina = min($pagina, $totalPaginas);
    $offset = ($pagina - 1) * $porPagina;

    $sql = "SELECT l.id_licitacao, l.numero_processo, l.numero_licitacao, l.ano_exercicio, l.objeto,
                   l.data_abertura, l.valor_estimado, m.descricao AS modalidade_descricao,
                   s.descricao AS situacao_descricao
            FROM licitacoes l
            LEFT JOIN licitacao_modalidades m ON m.id_modalidade = l.id_modalidade
            LEFT JOIN licitacao_situacoes s ON s.id_situacao = l.id_situacao
            $whereSql
            ORDER BY l.data_abertura DESC
            LIMIT :limite OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    foreach ($parametros as $chave => $valor) {
        $stmt->bindValue($chave, $valor);
    }
    $stmt->bindValue('limite', $porPagina, PDO::PARAM_INT);
    $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $licitacoes = $stmt->fetchAll();
}

include 'includes/pagina-header.php';

function manterFiltrosLicPub(array $substituir = []): string
{
    $params = array_merge($_GET, $substituir);
    return htmlspecialchars('?' . http_build_query($params));
}
?>

<section class="pagina-interna-conteudo">
  <div class="section-container">

    <form class="toolbar-filtro" method="get">
      <input type="text" name="busca" placeholder="Buscar por número ou objeto..." value="<?= htmlspecialchars($busca) ?>">
      <select name="modalidade" onchange="this.form.submit()">
        <option value="">Todas as modalidades</option>
        <?php foreach ($modalidades as $m): ?>
          <option value="<?= $m['id_modalidade'] ?>" <?= (string) $modalidade === (string) $m['id_modalidade'] ? 'selected' : '' ?>><?= htmlspecialchars($m['descricao']) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit"><i class="fas fa-magnifying-glass"></i> Buscar</button>
    </form>

    <?php if (empty($licitacoes)): ?>
      <div class="vazio-lista">
        <i class="fas fa-file-contract"></i>
        <p>Nenhuma licitação encontrada<?= $busca !== '' ? ' para "' . htmlspecialchars($busca) . '"' : '' ?>.</p>
      </div>
    <?php else: ?>
      <?php foreach ($licitacoes as $l): ?>
        <a href="licitacao-detalhe.php?id=<?= $l['id_licitacao'] ?>" class="arquivo-lista-item" style="text-decoration:none; color:inherit;">
          <div class="icone-pdf"><i class="fas fa-file-contract"></i></div>
          <div class="info">
            <strong>
              Processo <?= htmlspecialchars($l['numero_processo'] ?: '—') ?>
              <?= $l['numero_licitacao'] ? ' · Licitação Nº ' . htmlspecialchars($l['numero_licitacao']) . '/' . htmlspecialchars($l['ano_exercicio']) : '' ?>
            </strong>
            <span>
              <?= htmlspecialchars($l['modalidade_descricao'] ?: 'Modalidade não informada') ?>
              <?= $l['data_abertura'] ? ' · Abertura em ' . date('d/m/Y', strtotime($l['data_abertura'])) : '' ?>
              <?= $l['situacao_descricao'] ? ' · ' . htmlspecialchars($l['situacao_descricao']) : '' ?>
            </span>
            <?php if ($l['objeto']): ?>
              <p style="margin-top:.3rem; font-size:.85rem; color:var(--texto-medio);"><?= htmlspecialchars(resumirTexto($l['objeto'], 160)) ?></p>
            <?php endif; ?>
          </div>
          <div class="acoes"><span style="padding:.4rem .6rem; font-size:.8rem; color:var(--verde-medio); font-weight:600;">Ver detalhes <i class="fas fa-arrow-right"></i></span></div>
        </a>
      <?php endforeach; ?>

      <?php if ($totalPaginas > 1): ?>
        <div class="paginacao-site">
          <?php if ($pagina > 1): ?><a href="<?= manterFiltrosLicPub(['pagina' => $pagina - 1]) ?>"><i class="fas fa-chevron-left"></i></a><?php endif; ?>
          <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
            <?php if ($p === $pagina): ?><span class="atual"><?= $p ?></span><?php else: ?><a href="<?= manterFiltrosLicPub(['pagina' => $p]) ?>"><?= $p ?></a><?php endif; ?>
          <?php endfor; ?>
          <?php if ($pagina < $totalPaginas): ?><a href="<?= manterFiltrosLicPub(['pagina' => $pagina + 1]) ?>"><i class="fas fa-chevron-right"></i></a><?php endif; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>

  </div>
</section>

<?php include 'includes/footer.php'; ?>
