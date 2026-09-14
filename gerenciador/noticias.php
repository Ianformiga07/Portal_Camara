<?php
require_once __DIR__ . '/includes/auth.php';

$tituloPagina = 'Notícias';

// --- Filtros ---
$busca  = trim($_GET['busca'] ?? '');
$status = $_GET['status'] ?? '';
$pagina = max(1, (int) ($_GET['pagina'] ?? 1));
$porPagina = 10;

$condicoes = [];
$parametros = [];

if ($busca !== '') {
    $condicoes[] = '(titulo LIKE :busca OR subtitulo LIKE :busca)';
    $parametros['busca'] = '%' . $busca . '%';
}
if ($status === '1' || $status === '0') {
    $condicoes[] = 'status = :status';
    $parametros['status'] = $status;
}
$whereSql = $condicoes ? 'WHERE ' . implode(' AND ', $condicoes) : '';

// --- Total para paginação ---
$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM noticias $whereSql");
$stmtTotal->execute($parametros);
$totalRegistros = (int) $stmtTotal->fetchColumn();
$totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;

// --- Registros da página atual ---
$sql = "SELECT id_noticia, titulo, subtitulo, imagem, autor, destaque, status, publicado_em
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

include __DIR__ . '/includes/header.php';

function manterFiltros(array $substituir = []): string
{
    $params = array_merge($_GET, $substituir);
    return htmlspecialchars('?' . http_build_query($params));
}
?>

<h1 class="pagina__titulo">Notícias</h1>
<p class="pagina__subtitulo">Publicações exibidas na página inicial e na seção de notícias do portal.</p>

<?php if (!empty($_GET['ok'])): ?>
  <div class="alerta alerta-sucesso" style="max-width:none;">
    <i class="fa-solid fa-circle-check"></i>
    <?= $_GET['ok'] === 'excluida' ? 'Notícia excluída com sucesso.' : 'Notícia salva com sucesso.' ?>
  </div>
<?php endif; ?>

<div class="toolbar">
  <form class="toolbar__busca" method="get">
    <input type="text" name="busca" placeholder="Buscar por título..." value="<?= htmlspecialchars($busca) ?>">
    <select name="status" onchange="this.form.submit()">
      <option value="">Todos os status</option>
      <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Publicadas</option>
      <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Inativas</option>
    </select>
    <button type="submit" class="btn btn-secundario"><i class="fa-solid fa-magnifying-glass"></i></button>
  </form>

  <a href="noticia-form.php" class="btn btn-primario" style="width:auto;">
    <i class="fa-solid fa-plus"></i> Nova notícia
  </a>
</div>

<div class="painel">
  <?php if (empty($noticias)): ?>
    <div class="vazio">
      <i class="fa-solid fa-newspaper"></i>
      <p>Nenhuma notícia encontrada<?= $busca !== '' ? ' para "' . htmlspecialchars($busca) . '"' : '' ?>.</p>
    </div>
  <?php else: ?>
    <table class="tabela-simples">
      <thead>
        <tr>
          <th style="width:70px;"></th>
          <th>Título</th>
          <th>Autor</th>
          <th>Publicada em</th>
          <th>Situação</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($noticias as $n): ?>
          <tr>
            <td>
              <?php if ($n['imagem']): ?>
                <img class="miniatura" src="assets/uploads/noticias/<?= htmlspecialchars($n['imagem']) ?>" alt="">
              <?php else: ?>
                <img class="miniatura" src="assets/img/sem-imagem.svg" alt="">
              <?php endif; ?>
            </td>
            <td>
              <div class="titulo-linha"><?= htmlspecialchars($n['titulo']) ?></div>
              <?php if ($n['subtitulo']): ?>
                <div class="descricao-linha"><?= htmlspecialchars($n['subtitulo']) ?></div>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($n['autor'] ?: '—') ?></td>
            <td><?= date('d/m/Y', strtotime($n['publicado_em'])) ?></td>
            <td>
              <?php if ($n['status']): ?>
                <span class="badge badge-ok">Publicada</span>
              <?php else: ?>
                <span class="badge badge-inativo">Inativa</span>
              <?php endif; ?>
              <?php if ($n['destaque']): ?>
                <span class="badge badge-destaque">Destaque</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="coluna-acoes">
                <a class="btn-icone" href="noticia-form.php?id=<?= $n['id_noticia'] ?>" title="Editar">
                  <i class="fa-solid fa-pen"></i>
                </a>
                <form method="post" action="noticia-excluir.php" onsubmit="return confirm('Excluir a notícia \'<?= htmlspecialchars(addslashes($n['titulo'])) ?>\'? Essa ação não pode ser desfeita.');" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $n['id_noticia'] ?>">
                  <button type="submit" class="btn-icone perigo" title="Excluir"><i class="fa-solid fa-trash"></i></button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <?php if ($totalPaginas > 1): ?>
      <div class="paginacao">
        <?php if ($pagina > 1): ?>
          <a href="<?= manterFiltros(['pagina' => $pagina - 1]) ?>"><i class="fa-solid fa-chevron-left"></i></a>
        <?php endif; ?>
        <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
          <?php if ($p === $pagina): ?>
            <span class="atual"><?= $p ?></span>
          <?php else: ?>
            <a href="<?= manterFiltros(['pagina' => $p]) ?>"><?= $p ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($pagina < $totalPaginas): ?>
          <a href="<?= manterFiltros(['pagina' => $pagina + 1]) ?>"><i class="fa-solid fa-chevron-right"></i></a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
