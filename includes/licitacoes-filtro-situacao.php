<?php
/**
 * O arquivo que inclui define antes: $situacaoFiltro (array de descrições
 * de situação), $tituloPaginaInterna, $subtituloPaginaInterna
 */
require_once 'includes/funcoes.php';
include 'includes/header.php';

$pagina = max(1, (int) ($_GET['pagina'] ?? 1));
$porPagina = 10;
$registros = [];
$totalPaginas = 1;

if ($pdo) {
    $placeholders = implode(',', array_fill(0, count($situacaoFiltro), '?'));
    $sql = "SELECT l.id_licitacao, l.numero_processo, l.objeto, l.data_abertura,
                   m.descricao AS modalidade_descricao, s.descricao AS situacao_descricao
            FROM licitacoes l
            JOIN licitacao_situacoes s ON s.id_situacao = l.id_situacao
            LEFT JOIN licitacao_modalidades m ON m.id_modalidade = l.id_modalidade
            WHERE l.status = 1 AND s.descricao IN ($placeholders)
            ORDER BY l.data_abertura DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($situacaoFiltro);
    $registros = $stmt->fetchAll();
}

include 'includes/pagina-header.php';
?>

<section class="pagina-interna-conteudo">
  <div class="section-container">
    <?php if (empty($registros)): ?>
      <div class="vazio-lista"><i class="fas fa-file-circle-xmark"></i><p>Nenhum registro encontrado nesta situação.</p></div>
    <?php else: ?>
      <?php foreach ($registros as $l): ?>
        <a href="licitacao-detalhe.php?id=<?= $l['id_licitacao'] ?>" class="arquivo-lista-item" style="text-decoration:none; color:inherit;">
          <div class="icone-pdf"><i class="fas fa-file-contract"></i></div>
          <div class="info">
            <strong>Processo <?= htmlspecialchars($l['numero_processo'] ?: '—') ?> — <?= htmlspecialchars($l['modalidade_descricao'] ?: '') ?></strong>
            <span><?= $l['data_abertura'] ? date('d/m/Y', strtotime($l['data_abertura'])) : '' ?> · <?= htmlspecialchars($l['situacao_descricao']) ?></span>
            <?php if ($l['objeto']): ?><p style="margin-top:.3rem; font-size:.85rem; color:var(--texto-medio);"><?= htmlspecialchars(resumirTexto($l['objeto'], 160)) ?></p><?php endif; ?>
          </div>
        </a>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
