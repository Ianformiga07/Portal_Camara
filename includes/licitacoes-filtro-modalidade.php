<?php
/**
 * O arquivo que inclui define antes: $categoriaSlugLicitacao (array de
 * descrições de modalidade), $tituloPaginaInterna, $subtituloPaginaInterna
 */
require_once 'includes/funcoes.php';
include 'includes/header.php';

$busca = trim($_GET['busca'] ?? '');
$pagina = max(1, (int) ($_GET['pagina'] ?? 1));
$porPagina = 10;
$registros = [];
$totalPaginas = 1;

if ($pdo) {
    $placeholders = implode(',', array_fill(0, count($categoriaSlugLicitacao), '?'));
    $condicoes = ["l.status = 1", "m.descricao IN ($placeholders)"];
    $parametros = $categoriaSlugLicitacao;

    if ($busca !== '') {
        $condicoes[] = '(l.numero_processo LIKE ? OR l.objeto LIKE ?)';
        $parametros[] = '%' . $busca . '%';
        $parametros[] = '%' . $busca . '%';
    }
    $whereSql = 'WHERE ' . implode(' AND ', $condicoes);

    $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM licitacoes l JOIN licitacao_modalidades m ON m.id_modalidade = l.id_modalidade $whereSql");
    $stmtTotal->execute($parametros);
    $totalRegistros = (int) $stmtTotal->fetchColumn();
    $totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
    $pagina = min($pagina, $totalPaginas);
    $offset = ($pagina - 1) * $porPagina;

    $sql = "SELECT l.id_licitacao, l.numero_processo, l.objeto, l.data_abertura, l.valor_estimado,
                   m.descricao AS modalidade_descricao, s.descricao AS situacao_descricao
            FROM licitacoes l
            JOIN licitacao_modalidades m ON m.id_modalidade = l.id_modalidade
            LEFT JOIN licitacao_situacoes s ON s.id_situacao = l.id_situacao
            $whereSql
            ORDER BY l.data_abertura DESC
            LIMIT $porPagina OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);
    $registros = $stmt->fetchAll();
}

include 'includes/pagina-header.php';

function manterFiltrosLicFiltro(array $substituir = []): string
{
    $params = array_merge($_GET, $substituir);
    return htmlspecialchars('?' . http_build_query($params));
}
?>

<section class="pagina-interna-conteudo">
  <div class="section-container">

    <form class="toolbar-filtro" method="get">
      <input type="text" name="busca" placeholder="Buscar por processo ou objeto..." value="<?= htmlspecialchars($busca) ?>">
      <button type="submit"><i class="fas fa-magnifying-glass"></i> Buscar</button>
    </form>

    <?php if (empty($registros)): ?>
      <div class="vazio-lista"><i class="fas fa-file-contract"></i><p>Nenhum registro encontrado.</p></div>
    <?php else: ?>
      <?php foreach ($registros as $l): ?>
        <a href="licitacao-detalhe.php?id=<?= $l['id_licitacao'] ?>" class="arquivo-lista-item" style="text-decoration:none; color:inherit;">
          <div class="icone-pdf"><i class="fas fa-file-contract"></i></div>
          <div class="info">
            <strong>Processo <?= htmlspecialchars($l['numero_processo'] ?: '—') ?> — <?= htmlspecialchars($l['modalidade_descricao']) ?></strong>
            <span><?= $l['data_abertura'] ? date('d/m/Y', strtotime($l['data_abertura'])) : '' ?><?= $l['situacao_descricao'] ? ' · ' . htmlspecialchars($l['situacao_descricao']) : '' ?></span>
            <?php if ($l['objeto']): ?><p style="margin-top:.3rem; font-size:.85rem; color:var(--texto-medio);"><?= htmlspecialchars(resumirTexto($l['objeto'], 160)) ?></p><?php endif; ?>
          </div>
        </a>
      <?php endforeach; ?>

      <?php if ($totalPaginas > 1): ?>
        <div class="paginacao-site">
          <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
            <?php if ($p === $pagina): ?><span class="atual"><?= $p ?></span><?php else: ?><a href="<?= manterFiltrosLicFiltro(['pagina' => $p]) ?>"><?= $p ?></a><?php endif; ?>
          <?php endfor; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>

  </div>
</section>

<?php include 'includes/footer.php'; ?>
