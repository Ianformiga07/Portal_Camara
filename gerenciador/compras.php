<?php
require_once __DIR__ . '/includes/auth.php';

$tituloPagina = 'Compras e Suprimentos';

$busca      = trim($_GET['busca'] ?? '');
$modalidade = $_GET['modalidade'] ?? '';
$pagina     = max(1, (int) ($_GET['pagina'] ?? 1));
$porPagina  = 12;

$modalidades = $pdo->query('SELECT id_modalidade, descricao FROM licitacao_modalidades ORDER BY descricao')->fetchAll();

$condicoes = [];
$parametros = [];

if ($busca !== '') {
    $condicoes[] = '(c.tipo_compra LIKE :busca OR c.descricao LIKE :busca OR f.razao_social LIKE :busca)';
    $parametros['busca'] = '%' . $busca . '%';
}
if ($modalidade !== '') {
    $condicoes[] = 'c.id_modalidade = :modalidade';
    $parametros['modalidade'] = $modalidade;
}
$whereSql = $condicoes ? 'WHERE ' . implode(' AND ', $condicoes) : '';

$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM compras c LEFT JOIN fornecedores f ON f.id_fornecedor = c.id_fornecedor $whereSql");
$stmtTotal->execute($parametros);
$totalRegistros = (int) $stmtTotal->fetchColumn();
$totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;

$sql = "SELECT c.id_compra, c.tipo_compra, c.data_compra, c.valor_final, c.status,
               f.razao_social, m.descricao AS modalidade_descricao
        FROM compras c
        LEFT JOIN fornecedores f ON f.id_fornecedor = c.id_fornecedor
        LEFT JOIN licitacao_modalidades m ON m.id_modalidade = c.id_modalidade
        $whereSql
        ORDER BY c.data_compra DESC, c.id_compra DESC
        LIMIT :limite OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($parametros as $chave => $valor) {
    $stmt->bindValue($chave, $valor);
}
$stmt->bindValue('limite', $porPagina, PDO::PARAM_INT);
$stmt->bindValue('offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$compras = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';

function manterFiltrosCompra(array $substituir = []): string
{
    $params = array_merge($_GET, $substituir);
    return htmlspecialchars('?' . http_build_query($params));
}
?>

<h1 class="pagina__titulo">Compras e Suprimentos</h1>
<p class="pagina__subtitulo">Aquisições de bens e serviços, vinculadas ou não a uma licitação formal.</p>

<?php if (!empty($_GET['ok'])): ?>
  <div class="alerta alerta-sucesso" style="max-width:none;">
    <i class="fa-solid fa-circle-check"></i>
    <?= $_GET['ok'] === 'excluida' ? 'Compra excluída com sucesso.' : 'Compra salva com sucesso.' ?>
  </div>
<?php endif; ?>

<div class="toolbar">
  <form class="toolbar__busca" method="get" style="max-width:600px;">
    <input type="text" name="busca" placeholder="Buscar por tipo, descrição ou fornecedor..." value="<?= htmlspecialchars($busca) ?>">
    <select name="modalidade" onchange="this.form.submit()">
      <option value="">Todas as modalidades</option>
      <?php foreach ($modalidades as $m): ?>
        <option value="<?= $m['id_modalidade'] ?>" <?= (string) $modalidade === (string) $m['id_modalidade'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($m['descricao']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-secundario"><i class="fa-solid fa-magnifying-glass"></i></button>
  </form>

  <div style="display:flex; gap:.6rem;">
    <a href="compra-form.php" class="btn btn-primario" style="width:auto;">
      <i class="fa-solid fa-plus"></i> Nova compra
    </a>
    <a href="compras-exportar.php<?= manterFiltrosCompra() ?>" class="btn btn-secundario" style="width:auto;">
      <i class="fa-solid fa-file-csv"></i> Exportar CSV
    </a>
  </div>
</div>

<div class="painel">
  <?php if (empty($compras)): ?>
    <div class="vazio">
      <i class="fa-solid fa-cart-shopping"></i>
      <p>Nenhuma compra encontrada<?= $busca !== '' ? ' para "' . htmlspecialchars($busca) . '"' : '' ?>.</p>
    </div>
  <?php else: ?>
    <table class="tabela-simples">
      <thead>
        <tr>
          <th>Tipo</th>
          <th>Fornecedor</th>
          <th>Modalidade</th>
          <th>Data</th>
          <th>Valor final</th>
          <th>Situação</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($compras as $c): ?>
          <tr>
            <td class="titulo-linha"><?= htmlspecialchars($c['tipo_compra'] ?: '—') ?></td>
            <td><?= htmlspecialchars($c['razao_social'] ?: '—') ?></td>
            <td><?= htmlspecialchars($c['modalidade_descricao'] ?: '—') ?></td>
            <td><?= $c['data_compra'] ? date('d/m/Y', strtotime($c['data_compra'])) : '—' ?></td>
            <td><?= $c['valor_final'] !== null ? 'R$ ' . number_format($c['valor_final'], 2, ',', '.') : '—' ?></td>
            <td>
              <?php if ($c['status']): ?>
                <span class="badge badge-ok">Publicada</span>
              <?php else: ?>
                <span class="badge badge-inativo">Inativa</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="coluna-acoes">
                <a class="btn-icone" href="compra-form.php?id=<?= $c['id_compra'] ?>" title="Editar">
                  <i class="fa-solid fa-pen"></i>
                </a>
                <form method="post" action="compra-excluir.php" onsubmit="return confirm('Excluir esta compra? Essa ação não pode ser desfeita.');" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $c['id_compra'] ?>">
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
          <a href="<?= manterFiltrosCompra(['pagina' => $pagina - 1]) ?>"><i class="fa-solid fa-chevron-left"></i></a>
        <?php endif; ?>
        <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
          <?php if ($p === $pagina): ?>
            <span class="atual"><?= $p ?></span>
          <?php else: ?>
            <a href="<?= manterFiltrosCompra(['pagina' => $p]) ?>"><?= $p ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($pagina < $totalPaginas): ?>
          <a href="<?= manterFiltrosCompra(['pagina' => $pagina + 1]) ?>"><i class="fa-solid fa-chevron-right"></i></a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
