<?php
$page_title = 'Prestação de Contas e LRF — Câmara Municipal de Ananás';
require_once 'includes/funcoes.php';
include 'includes/header.php';

$tituloPaginaInterna = 'Prestação de Contas e LRF';
$subtituloPaginaInterna = 'Demonstrativos contábeis e relatórios da Lei de Responsabilidade Fiscal.';

$categorias = [
    'demonstrativos-contabeis' => 'Demonstrativos Contábeis',
    'relatorios-lrf'           => 'Relatórios — LRF (RREO/RGF)',
    'prestacoes-contas'        => 'Prestações de Contas',
    'decisoes-lrf'             => 'Decisões do TCE-TO',
];

$catAtual = $_GET['cat'] ?? 'demonstrativos-contabeis';
if (!isset($categorias[$catAtual])) {
    $catAtual = 'demonstrativos-contabeis';
}

$documentos = [];
if ($pdo) {
    $stmt = $pdo->prepare(
        "SELECT d.* FROM documentos d
         JOIN categorias_documentos c ON c.id_categoria = d.id_categoria
         WHERE c.slug = :slug AND d.status = 1
         ORDER BY d.data_publicacao DESC"
    );
    $stmt->execute(['slug' => $catAtual]);
    $documentos = $stmt->fetchAll();
}

include 'includes/pagina-header.php';
?>

<style>
  .subtabs-orc { display:flex; flex-wrap:wrap; gap:.5rem; margin-bottom:1.2rem; }
  .subtabs-orc a {
    padding:.5rem .9rem; border-radius:var(--radius-sm); font-size:.82rem; font-weight:600;
    background:var(--cinza-claro); color:var(--texto-medio); text-decoration:none; border:1px solid var(--cinza-borda);
  }
  .subtabs-orc a.ativo { background:var(--verde-medio); color:#fff; border-color:var(--verde-medio); }
</style>

<section class="pagina-interna-conteudo">
  <div class="section-container">

    <div class="subtabs-orc">
      <?php foreach ($categorias as $slug => $nome): ?>
        <a href="?cat=<?= $slug ?>" class="<?= $catAtual === $slug ? 'ativo' : '' ?>"><?= htmlspecialchars($nome) ?></a>
      <?php endforeach; ?>
    </div>

    <?php if (empty($documentos)): ?>
      <div class="vazio-lista">
        <i class="fas fa-book"></i>
        <p>Nenhum documento publicado nesta categoria ainda.</p>
      </div>
    <?php else: ?>
      <?php foreach ($documentos as $d): ?>
        <div class="arquivo-lista-item">
          <div class="icone-pdf"><i class="fas fa-file-lines"></i></div>
          <div class="info">
            <strong>
              <?= $d['numero_documento'] ? 'Nº ' . htmlspecialchars($d['numero_documento']) . ' — ' : '' ?><?= htmlspecialchars($d['titulo']) ?>
            </strong>
            <span><?= $d['data_publicacao'] ? date('d/m/Y', strtotime($d['data_publicacao'])) : '—' ?></span>
            <?php if ($d['descricao']): ?>
              <p style="margin-top:.3rem; font-size:.85rem; color:var(--texto-medio);"><?= htmlspecialchars(resumirTexto($d['descricao'], 160)) ?></p>
            <?php endif; ?>
          </div>
          <div class="acoes">
            <?php if ($d['arquivo']): ?>
              <a href="gerenciador/assets/uploads/documentos/<?= htmlspecialchars($d['arquivo']) ?>" target="_blank" title="Visualizar"><i class="fas fa-eye"></i></a>
              <a href="gerenciador/assets/uploads/documentos/<?= htmlspecialchars($d['arquivo']) ?>" download title="Download"><i class="fas fa-download"></i></a>
            <?php else: ?>
              <span style="font-size:.78rem; color:var(--texto-claro);">Sem arquivo anexado</span>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

  </div>
</section>

<?php include 'includes/footer.php'; ?>
