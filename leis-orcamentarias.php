<?php
$page_title = 'Leis Orçamentárias — Câmara Municipal de Ananás';
require_once 'includes/funcoes.php';
include 'includes/header.php';

$tituloPaginaInterna = 'Leis Orçamentárias';
$subtituloPaginaInterna = 'Plano Plurianual (PPA), Lei de Diretrizes Orçamentárias (LDO) e Lei Orçamentária Anual (LOA).';

$grupos = [
    'ppa' => ['titulo' => 'PPA — Plano Plurianual', 'itens' => []],
    'ldo' => ['titulo' => 'LDO — Lei de Diretrizes Orçamentárias', 'itens' => []],
    'loa' => ['titulo' => 'LOA — Lei Orçamentária Anual', 'itens' => []],
];

if ($pdo) {
    foreach (array_keys($grupos) as $slug) {
        $stmt = $pdo->prepare(
            "SELECT d.id_documento, d.numero_documento, d.titulo, d.data_publicacao, d.arquivo
             FROM documentos d
             JOIN categorias_documentos c ON c.id_categoria = d.id_categoria
             WHERE c.slug = :slug AND d.status = 1
             ORDER BY d.data_publicacao DESC"
        );
        $stmt->execute(['slug' => $slug]);
        $grupos[$slug]['itens'] = $stmt->fetchAll();
    }
}

include 'includes/pagina-header.php';
?>
<section class="pagina-interna-conteudo">
  <div class="section-container">
    <?php foreach ($grupos as $grupo): ?>
      <div class="ficha-card">
        <h3><i class="fas fa-scroll"></i> <?= htmlspecialchars($grupo['titulo']) ?></h3>
        <?php if (empty($grupo['itens'])): ?>
          <p style="color:var(--texto-claro); font-size:.85rem;">Nenhum documento publicado ainda.</p>
        <?php else: ?>
          <?php foreach ($grupo['itens'] as $d): ?>
            <div class="arquivo-lista-item" style="margin-bottom:.6rem;">
              <div class="icone-pdf"><i class="fas fa-file-pdf"></i></div>
              <div class="info">
                <strong><?= $d['numero_documento'] ? 'Nº ' . htmlspecialchars($d['numero_documento']) : '' ?> <?= $d['titulo'] ? htmlspecialchars($d['titulo']) : '' ?></strong>
                <span><?= $d['data_publicacao'] ? date('d/m/Y', strtotime($d['data_publicacao'])) : '' ?></span>
              </div>
              <?php if ($d['arquivo']): ?>
                <div class="acoes"><a href="gerenciador/assets/uploads/documentos/<?= htmlspecialchars($d['arquivo']) ?>" target="_blank" title="Ver PDF"><i class="fas fa-eye"></i></a></div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
