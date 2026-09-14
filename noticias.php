<?php
$page_title = 'Notícias — Câmara Municipal de Ananás';
require_once 'includes/funcoes.php';
include 'includes/header.php';

$tituloPaginaInterna = 'Notícias';
$subtituloPaginaInterna = 'Acompanhe as últimas notícias da Câmara Municipal de Ananás.';

$busca = trim($_GET['busca'] ?? '');
$pagina = max(1, (int) ($_GET['pagina'] ?? 1));
$porPagina = 9;

$noticias = [];
$totalPaginas = 1;

if ($pdo) {
    $condicoes = ['status = 1'];
    $parametros = [];
    if ($busca !== '') {
        $condicoes[] = '(titulo LIKE :busca OR subtitulo LIKE :busca OR conteudo LIKE :busca)';
        $parametros['busca'] = '%' . $busca . '%';
    }
    $whereSql = 'WHERE ' . implode(' AND ', $condicoes);

    $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM noticias $whereSql");
    $stmtTotal->execute($parametros);
    $totalRegistros = (int) $stmtTotal->fetchColumn();
    $totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
    $pagina = min($pagina, $totalPaginas);
    $offset = ($pagina - 1) * $porPagina;

    $sql = "SELECT id_noticia, titulo, subtitulo, imagem, publicado_em
            FROM noticias
            $whereSql
            ORDER BY publicado_em DESC
            LIMIT :limite OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    foreach ($parametros as $chave => $valor) {
        $stmt->bindValue($chave, $valor);
    }
    $stmt->bindValue('limite', $porPagina, PDO::PARAM_INT);
    $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $noticias = $stmt->fetchAll();
}

include 'includes/pagina-header.php';

function manterFiltrosNotPub(array $substituir = []): string
{
    $params = array_merge($_GET, $substituir);
    return htmlspecialchars('?' . http_build_query($params));
}
?>

<section class="pagina-interna-conteudo">
  <div class="section-container">

    <form class="toolbar-filtro" method="get">
      <input type="text" name="busca" placeholder="Buscar notícias..." value="<?= htmlspecialchars($busca) ?>">
      <button type="submit"><i class="fas fa-magnifying-glass"></i> Buscar</button>
    </form>

    <?php if (empty($noticias)): ?>
      <div class="vazio-lista">
        <i class="fas fa-newspaper"></i>
        <p>Nenhuma notícia encontrada<?= $busca !== '' ? ' para "' . htmlspecialchars($busca) . '"' : '' ?>.</p>
      </div>
    <?php else: ?>
      <div class="noticias-grid-completa">
        <?php foreach ($noticias as $n): ?>
          <a href="noticia-detalhe.php?id=<?= $n['id_noticia'] ?>" class="noticia-card-grid">
            <div class="thumb">
              <img src="<?= $n['imagem'] ? 'gerenciador/assets/uploads/noticias/' . htmlspecialchars($n['imagem']) : 'assets/img/noticias/noticia1.jpg' ?>" alt="<?= htmlspecialchars($n['titulo']) ?>" loading="lazy">
            </div>
            <div class="corpo">
              <time><?= formatarDataExtenso($n['publicado_em']) ?></time>
              <h3><?= htmlspecialchars($n['titulo']) ?></h3>
              <p><?= htmlspecialchars(resumirTexto($n['subtitulo'], 110)) ?></p>
            </div>
          </a>
        <?php endforeach; ?>
      </div>

      <?php if ($totalPaginas > 1): ?>
        <div class="paginacao-site">
          <?php if ($pagina > 1): ?><a href="<?= manterFiltrosNotPub(['pagina' => $pagina - 1]) ?>"><i class="fas fa-chevron-left"></i></a><?php endif; ?>
          <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
            <?php if ($p === $pagina): ?><span class="atual"><?= $p ?></span><?php else: ?><a href="<?= manterFiltrosNotPub(['pagina' => $p]) ?>"><?= $p ?></a><?php endif; ?>
          <?php endfor; ?>
          <?php if ($pagina < $totalPaginas): ?><a href="<?= manterFiltrosNotPub(['pagina' => $pagina + 1]) ?>"><i class="fas fa-chevron-right"></i></a><?php endif; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>

  </div>
</section>

<?php include 'includes/footer.php'; ?>
