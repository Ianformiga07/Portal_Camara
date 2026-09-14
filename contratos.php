<?php
$page_title = 'Contratos — Câmara Municipal de Ananás';
require_once 'includes/funcoes.php';
include 'includes/header.php';

$tituloPaginaInterna = 'Contratos';
$subtituloPaginaInterna = 'Contratos administrativos firmados pela Câmara Municipal de Ananás.';

$busca    = trim($_GET['busca'] ?? '');
$situacao = $_GET['situacao'] ?? '';
$pagina   = max(1, (int) ($_GET['pagina'] ?? 1));
$porPagina = 10;

$contratos = [];
$situacoes = [];
$totalPaginas = 1;

if ($pdo) {
    $situacoes = $pdo->query('SELECT id_situacao, descricao FROM contrato_situacoes ORDER BY id_situacao')->fetchAll();

    $condicoes = ['c.status = 1'];
    $parametros = [];
    if ($busca !== '') {
        $condicoes[] = '(c.numero_contrato LIKE :busca OR c.objeto LIKE :busca OR f.razao_social LIKE :busca)';
        $parametros['busca'] = '%' . $busca . '%';
    }
    if ($situacao !== '') {
        $condicoes[] = 'c.id_situacao = :situacao';
        $parametros['situacao'] = $situacao;
    }
    $whereSql = 'WHERE ' . implode(' AND ', $condicoes);

    $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM contratos c LEFT JOIN fornecedores f ON f.id_fornecedor = c.id_fornecedor $whereSql");
    $stmtTotal->execute($parametros);
    $totalRegistros = (int) $stmtTotal->fetchColumn();
    $totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
    $pagina = min($pagina, $totalPaginas);
    $offset = ($pagina - 1) * $porPagina;

    $sql = "SELECT c.id_contrato, c.numero_contrato, c.ano_exercicio, c.objeto, c.inicio_vigencia, c.fim_vigencia,
                   c.valor_estimado, f.razao_social, s.descricao AS situacao_descricao
            FROM contratos c
            LEFT JOIN fornecedores f ON f.id_fornecedor = c.id_fornecedor
            LEFT JOIN contrato_situacoes s ON s.id_situacao = c.id_situacao
            $whereSql
            ORDER BY c.inicio_vigencia DESC
            LIMIT :limite OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    foreach ($parametros as $chave => $valor) {
        $stmt->bindValue($chave, $valor);
    }
    $stmt->bindValue('limite', $porPagina, PDO::PARAM_INT);
    $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $contratos = $stmt->fetchAll();
}

include 'includes/pagina-header.php';

function manterFiltrosConPub(array $substituir = []): string
{
    $params = array_merge($_GET, $substituir);
    return htmlspecialchars('?' . http_build_query($params));
}
?>

<section class="pagina-interna-conteudo">
  <div class="section-container">

    <form class="toolbar-filtro" method="get">
      <input type="text" name="busca" placeholder="Buscar por número, objeto ou contratada..." value="<?= htmlspecialchars($busca) ?>">
      <select name="situacao" onchange="this.form.submit()">
        <option value="">Todas as situações</option>
        <?php foreach ($situacoes as $s): ?>
          <option value="<?= $s['id_situacao'] ?>" <?= (string) $situacao === (string) $s['id_situacao'] ? 'selected' : '' ?>><?= htmlspecialchars($s['descricao']) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit"><i class="fas fa-magnifying-glass"></i> Buscar</button>
    </form>

    <?php if (empty($contratos)): ?>
      <div class="vazio-lista">
        <i class="fas fa-file-signature"></i>
        <p>Nenhum contrato encontrado<?= $busca !== '' ? ' para "' . htmlspecialchars($busca) . '"' : '' ?>.</p>
      </div>
    <?php else: ?>
      <?php foreach ($contratos as $c): ?>
        <a href="contrato-detalhe.php?id=<?= $c['id_contrato'] ?>" class="arquivo-lista-item" style="text-decoration:none; color:inherit;">
          <div class="icone-pdf"><i class="fas fa-file-signature"></i></div>
          <div class="info">
            <strong>Contrato Nº <?= htmlspecialchars($c['numero_contrato'] ?: '—') ?>/<?= htmlspecialchars($c['ano_exercicio']) ?> — <?= htmlspecialchars($c['razao_social'] ?: 'Contratada não informada') ?></strong>
            <span>
              Vigência: <?= $c['inicio_vigencia'] ? date('d/m/Y', strtotime($c['inicio_vigencia'])) : '—' ?> a <?= $c['fim_vigencia'] ? date('d/m/Y', strtotime($c['fim_vigencia'])) : '—' ?>
              <?= $c['situacao_descricao'] ? ' · ' . htmlspecialchars($c['situacao_descricao']) : '' ?>
              <?= $c['valor_estimado'] !== null ? ' · R$ ' . number_format($c['valor_estimado'], 2, ',', '.') : '' ?>
            </span>
            <?php if ($c['objeto']): ?>
              <p style="margin-top:.3rem; font-size:.85rem; color:var(--texto-medio);"><?= htmlspecialchars(resumirTexto($c['objeto'], 160)) ?></p>
            <?php endif; ?>
          </div>
          <div class="acoes"><span style="padding:.4rem .6rem; font-size:.8rem; color:var(--verde-medio); font-weight:600;">Ver detalhes <i class="fas fa-arrow-right"></i></span></div>
        </a>
      <?php endforeach; ?>

      <?php if ($totalPaginas > 1): ?>
        <div class="paginacao-site">
          <?php if ($pagina > 1): ?><a href="<?= manterFiltrosConPub(['pagina' => $pagina - 1]) ?>"><i class="fas fa-chevron-left"></i></a><?php endif; ?>
          <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
            <?php if ($p === $pagina): ?><span class="atual"><?= $p ?></span><?php else: ?><a href="<?= manterFiltrosConPub(['pagina' => $p]) ?>"><?= $p ?></a><?php endif; ?>
          <?php endfor; ?>
          <?php if ($pagina < $totalPaginas): ?><a href="<?= manterFiltrosConPub(['pagina' => $pagina + 1]) ?>"><i class="fas fa-chevron-right"></i></a><?php endif; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>

  </div>
</section>

<?php include 'includes/footer.php'; ?>
