<?php
require_once 'config/database.php';
require_once 'includes/funcoes.php';

$id = (int) ($_GET['id'] ?? 0);

if (!$pdo) {
    header('Location: noticias.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM noticias WHERE id_noticia = :id AND status = 1');
$stmt->execute(['id' => $id]);
$noticia = $stmt->fetch();

if (!$noticia) {
    header('Location: noticias.php');
    exit;
}

$relacionadas = $pdo->prepare(
    'SELECT id_noticia, titulo, imagem, publicado_em FROM noticias
     WHERE status = 1 AND id_noticia != :id
     ORDER BY publicado_em DESC LIMIT 3'
);
$relacionadas->execute(['id' => $id]);
$relacionadas = $relacionadas->fetchAll();

$page_title = htmlspecialchars($noticia['titulo']) . ' — Câmara Municipal de Ananás';
include 'includes/header.php';

$tituloPaginaInterna = 'Notícias';
$subtituloPaginaInterna = '';
include 'includes/pagina-header.php';
?>

<section class="pagina-interna-conteudo">
  <div class="section-container">

    <article class="noticia-artigo">
      <p style="margin-bottom:1.5rem;"><a href="noticias.php"><i class="fas fa-arrow-left"></i> Voltar para todas as notícias</a></p>

      <?php if ($noticia['imagem']): ?>
        <div class="capa">
          <img src="gerenciador/assets/uploads/noticias/<?= htmlspecialchars($noticia['imagem']) ?>" alt="<?= htmlspecialchars($noticia['titulo']) ?>">
        </div>
      <?php endif; ?>

      <h1><?= htmlspecialchars($noticia['titulo']) ?></h1>
      <?php if ($noticia['subtitulo']): ?>
        <p class="subtitulo-artigo"><?= htmlspecialchars($noticia['subtitulo']) ?></p>
      <?php endif; ?>

      <div class="meta-artigo">
        <span><i class="fas fa-calendar-alt"></i> <?= formatarDataExtenso($noticia['publicado_em']) ?></span>
        <?php if ($noticia['autor']): ?><span><i class="fas fa-user"></i> <?= htmlspecialchars($noticia['autor']) ?></span><?php endif; ?>
      </div>

      <div class="corpo-artigo">
        <?php foreach (array_filter(preg_split('/\r\n|\r|\n/', trim($noticia['conteudo'] ?? ''))) as $paragrafo): ?>
          <p><?= htmlspecialchars(trim($paragrafo)) ?></p>
        <?php endforeach; ?>
      </div>

      <?php if (!empty($relacionadas)): ?>
        <div class="noticias-relacionadas">
          <h3><i class="fas fa-newspaper"></i> Outras notícias</h3>
          <div class="noticias-grid-completa">
            <?php foreach ($relacionadas as $r): ?>
              <a href="noticia-detalhe.php?id=<?= $r['id_noticia'] ?>" class="noticia-card-grid">
                <div class="thumb">
                  <img src="<?= $r['imagem'] ? 'gerenciador/assets/uploads/noticias/' . htmlspecialchars($r['imagem']) : 'assets/img/noticias/noticia1.jpg' ?>" alt="" loading="lazy">
                </div>
                <div class="corpo">
                  <time><?= formatarDataExtenso($r['publicado_em']) ?></time>
                  <h3><?= htmlspecialchars($r['titulo']) ?></h3>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </article>

  </div>
</section>

<?php include 'includes/footer.php'; ?>
