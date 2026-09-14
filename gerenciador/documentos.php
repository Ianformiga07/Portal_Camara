<?php
require_once __DIR__ . '/includes/auth.php';

$tituloPagina = 'Legislação e Documentos';

// --- Filtros ---
$busca      = trim($_GET['busca'] ?? '');
$categoria  = $_GET['categoria'] ?? '';
$status     = $_GET['status'] ?? '';
$pagina     = max(1, (int) ($_GET['pagina'] ?? 1));
$porPagina  = 12;

$categorias = $pdo->query('SELECT id_categoria, descricao, slug FROM categorias_documentos ORDER BY id_categoria')->fetchAll();
$categoriaSelecionada = null;
foreach ($categorias as $c) {
    if ($c['slug'] === $categoria) {
        $categoriaSelecionada = $c;
        break;
    }
}

$condicoes = [];
$parametros = [];

if ($busca !== '') {
    $condicoes[] = '(d.titulo LIKE :busca OR d.numero_documento LIKE :busca)';
    $parametros['busca'] = '%' . $busca . '%';
}
if ($categoriaSelecionada) {
    $condicoes[] = 'd.id_categoria = :id_categoria';
    $parametros['id_categoria'] = $categoriaSelecionada['id_categoria'];
}
if ($status === '1' || $status === '0') {
    $condicoes[] = 'd.status = :status';
    $parametros['status'] = $status;
}
$whereSql = $condicoes ? 'WHERE ' . implode(' AND ', $condicoes) : '';

$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM documentos d $whereSql");
$stmtTotal->execute($parametros);
$totalRegistros = (int) $stmtTotal->fetchColumn();
$totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;

$sql = "SELECT d.id_documento, d.numero_documento, d.titulo, d.data_publicacao, d.arquivo, d.status,
               c.descricao AS categoria_descricao
        FROM documentos d
        JOIN categorias_documentos c ON c.id_categoria = d.id_categoria
        $whereSql
        ORDER BY d.data_publicacao DESC, d.id_documento DESC
        LIMIT :limite OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($parametros as $chave => $valor) {
    $stmt->bindValue($chave, $valor);
}
$stmt->bindValue('limite', $porPagina, PDO::PARAM_INT);
$stmt->bindValue('offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$documentos = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';

function manterFiltrosDoc(array $substituir = []): string
{
    $params = array_merge($_GET, $substituir);
    return htmlspecialchars('?' . http_build_query($params));
}
?>

<h1 class="pagina__titulo"><?= $categoriaSelecionada ? htmlspecialchars($categoriaSelecionada['descricao']) : 'Legislação e Documentos' ?></h1>
<p class="pagina__subtitulo">Leis, decretos, portarias, resoluções, atas, diário oficial e demais publicações oficiais.</p>

<?php if (!empty($_GET['ok'])): ?>
  <div class="alerta alerta-sucesso" style="max-width:none;">
    <i class="fa-solid fa-circle-check"></i>
    <?= $_GET['ok'] === 'excluido' ? 'Documento excluído com sucesso.' : 'Documento salvo com sucesso.' ?>
  </div>
<?php endif; ?>

<div class="toolbar">
  <form class="toolbar__busca" method="get" style="max-width:600px;">
    <input type="text" name="busca" placeholder="Buscar por título ou número..." value="<?= htmlspecialchars($busca) ?>">
    <select name="categoria" onchange="this.form.submit()">
      <option value="">Todas as categorias</option>
      <?php foreach ($categorias as $c): ?>
        <option value="<?= htmlspecialchars($c['slug']) ?>" <?= $categoria === $c['slug'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($c['descricao']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <select name="status" onchange="this.form.submit()">
      <option value="">Todos os status</option>
      <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Publicados</option>
      <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Inativos</option>
    </select>
    <button type="submit" class="btn btn-secundario"><i class="fa-solid fa-magnifying-glass"></i></button>
  </form>

  <a href="documento-form.php<?= $categoria ? '?categoria=' . htmlspecialchars($categoria) : '' ?>" class="btn btn-primario" style="width:auto;">
    <i class="fa-solid fa-plus"></i> Novo documento
  </a>
</div>

<div class="painel">
  <?php if (empty($documentos)): ?>
    <div class="vazio">
      <i class="fa-solid fa-gavel"></i>
      <p>Nenhum documento encontrado<?= $busca !== '' ? ' para "' . htmlspecialchars($busca) . '"' : '' ?>.</p>
    </div>
  <?php else: ?>
    <table class="tabela-simples">
      <thead>
        <tr>
          <th>Categoria</th>
          <th>Número</th>
          <th>Título</th>
          <th>Publicado em</th>
          <th>Situação</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($documentos as $d): ?>
          <tr>
            <td><?= htmlspecialchars($d['categoria_descricao']) ?></td>
            <td><?= htmlspecialchars($d['numero_documento'] ?: '—') ?></td>
            <td>
              <div class="titulo-linha"><?= htmlspecialchars($d['titulo'] ?: '(sem título)') ?></div>
              <?php if ($d['arquivo']): ?>
                <div class="descricao-linha"><i class="fa-solid fa-paperclip"></i> anexo em PDF</div>
              <?php endif; ?>
            </td>
            <td><?= $d['data_publicacao'] ? date('d/m/Y', strtotime($d['data_publicacao'])) : '—' ?></td>
            <td>
              <?php if ($d['status']): ?>
                <span class="badge badge-ok">Publicado</span>
              <?php else: ?>
                <span class="badge badge-inativo">Inativo</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="coluna-acoes">
                <?php if ($d['arquivo']): ?>
                  <a class="btn-icone" href="assets/uploads/documentos/<?= htmlspecialchars($d['arquivo']) ?>" target="_blank" title="Ver PDF">
                    <i class="fa-solid fa-file-pdf"></i>
                  </a>
                <?php endif; ?>
                <a class="btn-icone" href="documento-form.php?id=<?= $d['id_documento'] ?>" title="Editar">
                  <i class="fa-solid fa-pen"></i>
                </a>
                <form method="post" action="documento-excluir.php" onsubmit="return confirm('Excluir o documento \'<?= htmlspecialchars(addslashes($d['titulo'] ?: $d['numero_documento'])) ?>\'? Essa ação não pode ser desfeita.');" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $d['id_documento'] ?>">
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
          <a href="<?= manterFiltrosDoc(['pagina' => $pagina - 1]) ?>"><i class="fa-solid fa-chevron-left"></i></a>
        <?php endif; ?>
        <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
          <?php if ($p === $pagina): ?>
            <span class="atual"><?= $p ?></span>
          <?php else: ?>
            <a href="<?= manterFiltrosDoc(['pagina' => $p]) ?>"><?= $p ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($pagina < $totalPaginas): ?>
          <a href="<?= manterFiltrosDoc(['pagina' => $pagina + 1]) ?>"><i class="fa-solid fa-chevron-right"></i></a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
