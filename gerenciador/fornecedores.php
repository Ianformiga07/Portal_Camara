<?php
require_once __DIR__ . '/includes/auth.php';

$tituloPagina = 'Fornecedores';

$busca  = trim($_GET['busca'] ?? '');
$status = $_GET['status'] ?? '';
$pagina = max(1, (int) ($_GET['pagina'] ?? 1));
$porPagina = 12;

$condicoes = [];
$parametros = [];

if ($busca !== '') {
    $condicoes[] = '(razao_social LIKE :busca OR nome_fantasia LIKE :busca OR cnpj_cpf LIKE :busca)';
    $parametros['busca'] = '%' . $busca . '%';
}
if ($status === '1' || $status === '0') {
    $condicoes[] = 'ativo = :status';
    $parametros['status'] = $status;
}
$whereSql = $condicoes ? 'WHERE ' . implode(' AND ', $condicoes) : '';

$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM fornecedores $whereSql");
$stmtTotal->execute($parametros);
$totalRegistros = (int) $stmtTotal->fetchColumn();
$totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;

$sql = "SELECT id_fornecedor, razao_social, nome_fantasia, cnpj_cpf, ativo
        FROM fornecedores
        $whereSql
        ORDER BY razao_social
        LIMIT :limite OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($parametros as $chave => $valor) {
    $stmt->bindValue($chave, $valor);
}
$stmt->bindValue('limite', $porPagina, PDO::PARAM_INT);
$stmt->bindValue('offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$fornecedores = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';

function manterFiltrosForn(array $substituir = []): string
{
    $params = array_merge($_GET, $substituir);
    return htmlspecialchars('?' . http_build_query($params));
}
?>

<h1 class="pagina__titulo">Fornecedores</h1>
<p class="pagina__subtitulo">Empresas e prestadores vinculados a licitações e contratos.</p>

<?php if (!empty($_GET['ok'])): ?>
  <div class="alerta alerta-sucesso" style="max-width:none;">
    <i class="fa-solid fa-circle-check"></i>
    <?= $_GET['ok'] === 'excluido' ? 'Fornecedor excluído com sucesso.' : 'Fornecedor salvo com sucesso.' ?>
  </div>
<?php endif; ?>
<?php if (!empty($_GET['erro'])): ?>
  <div class="alerta alerta-erro" style="max-width:none;">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <?= htmlspecialchars($_GET['erro']) ?>
  </div>
<?php endif; ?>

<div class="toolbar">
  <form class="toolbar__busca" method="get" style="max-width:600px;">
    <input type="text" name="busca" placeholder="Buscar por razão social, nome ou CNPJ..." value="<?= htmlspecialchars($busca) ?>">
    <select name="status" onchange="this.form.submit()">
      <option value="">Todos os status</option>
      <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Ativos</option>
      <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Inativos</option>
    </select>
    <button type="submit" class="btn btn-secundario"><i class="fa-solid fa-magnifying-glass"></i></button>
  </form>

  <a href="fornecedor-form.php" class="btn btn-primario" style="width:auto;">
    <i class="fa-solid fa-plus"></i> Novo fornecedor
  </a>
</div>

<div class="painel">
  <?php if (empty($fornecedores)): ?>
    <div class="vazio">
      <i class="fa-solid fa-building"></i>
      <p>Nenhum fornecedor encontrado<?= $busca !== '' ? ' para "' . htmlspecialchars($busca) . '"' : '' ?>.</p>
    </div>
  <?php else: ?>
    <table class="tabela-simples">
      <thead>
        <tr>
          <th>Razão social</th>
          <th>Nome fantasia</th>
          <th>CNPJ/CPF</th>
          <th>Situação</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($fornecedores as $f): ?>
          <tr>
            <td class="titulo-linha"><?= htmlspecialchars($f['razao_social'] ?: '—') ?></td>
            <td><?= htmlspecialchars($f['nome_fantasia'] ?: '—') ?></td>
            <td><?= htmlspecialchars($f['cnpj_cpf'] ?: '—') ?></td>
            <td>
              <?php if ($f['ativo']): ?>
                <span class="badge badge-ok">Ativo</span>
              <?php else: ?>
                <span class="badge badge-inativo">Inativo</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="coluna-acoes">
                <a class="btn-icone" href="fornecedor-form.php?id=<?= $f['id_fornecedor'] ?>" title="Editar">
                  <i class="fa-solid fa-pen"></i>
                </a>
                <form method="post" action="fornecedor-excluir.php" onsubmit="return confirm('Excluir o fornecedor \'<?= htmlspecialchars(addslashes($f['razao_social'] ?: 'sem nome')) ?>\'? Essa ação não pode ser desfeita.');" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $f['id_fornecedor'] ?>">
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
          <a href="<?= manterFiltrosForn(['pagina' => $pagina - 1]) ?>"><i class="fa-solid fa-chevron-left"></i></a>
        <?php endif; ?>
        <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
          <?php if ($p === $pagina): ?>
            <span class="atual"><?= $p ?></span>
          <?php else: ?>
            <a href="<?= manterFiltrosForn(['pagina' => $p]) ?>"><?= $p ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($pagina < $totalPaginas): ?>
          <a href="<?= manterFiltrosForn(['pagina' => $pagina + 1]) ?>"><i class="fa-solid fa-chevron-right"></i></a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
