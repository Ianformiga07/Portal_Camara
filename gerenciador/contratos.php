<?php
require_once __DIR__ . '/includes/auth.php';

$tituloPagina = 'Contratos';

$busca    = trim($_GET['busca'] ?? '');
$situacao = $_GET['situacao'] ?? '';
$pagina   = max(1, (int) ($_GET['pagina'] ?? 1));
$porPagina = 12;

$situacoes = $pdo->query('SELECT id_situacao, descricao FROM contrato_situacoes ORDER BY id_situacao')->fetchAll();

$condicoes = [];
$parametros = [];

if ($busca !== '') {
    $condicoes[] = '(c.numero_contrato LIKE :busca OR c.objeto LIKE :busca OR f.razao_social LIKE :busca)';
    $parametros['busca'] = '%' . $busca . '%';
}
if ($situacao !== '') {
    $condicoes[] = 'c.id_situacao = :situacao';
    $parametros['situacao'] = $situacao;
}
$whereSql = $condicoes ? 'WHERE ' . implode(' AND ', $condicoes) : '';

$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM contratos c LEFT JOIN fornecedores f ON f.id_fornecedor = c.id_fornecedor $whereSql");
$stmtTotal->execute($parametros);
$totalRegistros = (int) $stmtTotal->fetchColumn();
$totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;

$sql = "SELECT c.id_contrato, c.numero_contrato, c.ano_exercicio, c.objeto, c.inicio_vigencia, c.fim_vigencia,
               c.valor_estimado, c.status, f.razao_social, s.descricao AS situacao_descricao
        FROM contratos c
        LEFT JOIN fornecedores f ON f.id_fornecedor = c.id_fornecedor
        LEFT JOIN contrato_situacoes s ON s.id_situacao = c.id_situacao
        $whereSql
        ORDER BY c.inicio_vigencia DESC, c.id_contrato DESC
        LIMIT :limite OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($parametros as $chave => $valor) {
    $stmt->bindValue($chave, $valor);
}
$stmt->bindValue('limite', $porPagina, PDO::PARAM_INT);
$stmt->bindValue('offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$contratos = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';

function manterFiltrosCon(array $substituir = []): string
{
    $params = array_merge($_GET, $substituir);
    return htmlspecialchars('?' . http_build_query($params));
}
?>

<h1 class="pagina__titulo">Contratos</h1>
<p class="pagina__subtitulo">Contratos administrativos firmados pela Câmara, decorrentes de licitação ou contratação direta.</p>

<?php if (!empty($_GET['ok'])): ?>
  <div class="alerta alerta-sucesso" style="max-width:none;">
    <i class="fa-solid fa-circle-check"></i>
    <?= $_GET['ok'] === 'excluido' ? 'Contrato excluído com sucesso.' : 'Contrato salvo com sucesso.' ?>
  </div>
<?php endif; ?>

<div class="toolbar">
  <form class="toolbar__busca" method="get" style="max-width:600px;">
    <input type="text" name="busca" placeholder="Buscar por número, objeto ou contratada..." value="<?= htmlspecialchars($busca) ?>">
    <select name="situacao" onchange="this.form.submit()">
      <option value="">Todas as situações</option>
      <?php foreach ($situacoes as $s): ?>
        <option value="<?= $s['id_situacao'] ?>" <?= (string) $situacao === (string) $s['id_situacao'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($s['descricao']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-secundario"><i class="fa-solid fa-magnifying-glass"></i></button>
  </form>

  <div style="display:flex; gap:.6rem;">
    <a href="contrato-form.php" class="btn btn-primario" style="width:auto;">
      <i class="fa-solid fa-plus"></i> Novo contrato
    </a>
    <a href="contratos-exportar.php<?= manterFiltrosCon() ?>" class="btn btn-secundario" style="width:auto;">
      <i class="fa-solid fa-file-csv"></i> Exportar CSV
    </a>
  </div>
</div>

<div class="painel">
  <?php if (empty($contratos)): ?>
    <div class="vazio">
      <i class="fa-solid fa-file-signature"></i>
      <p>Nenhum contrato encontrado<?= $busca !== '' ? ' para "' . htmlspecialchars($busca) . '"' : '' ?>.</p>
    </div>
  <?php else: ?>
    <table class="tabela-simples">
      <thead>
        <tr>
          <th>Número</th>
          <th>Contratada</th>
          <th>Objeto</th>
          <th>Vigência</th>
          <th>Valor</th>
          <th>Situação</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($contratos as $c): ?>
          <tr>
            <td class="titulo-linha"><?= htmlspecialchars($c['numero_contrato'] ?: '—') ?><?= $c['ano_exercicio'] ? '/' . $c['ano_exercicio'] : '' ?></td>
            <td><?= htmlspecialchars($c['razao_social'] ?: '—') ?></td>
            <td><?= htmlspecialchars(mb_strimwidth($c['objeto'] ?? '', 0, 50, '...')) ?></td>
            <td>
              <?= $c['inicio_vigencia'] ? date('d/m/Y', strtotime($c['inicio_vigencia'])) : '—' ?>
              a
              <?= $c['fim_vigencia'] ? date('d/m/Y', strtotime($c['fim_vigencia'])) : '—' ?>
            </td>
            <td><?= $c['valor_estimado'] !== null ? 'R$ ' . number_format($c['valor_estimado'], 2, ',', '.') : '—' ?></td>
            <td>
              <?php if ($c['situacao_descricao']): ?>
                <span class="badge <?= $c['situacao_descricao'] === 'Vigente' ? 'badge-ok' : 'badge-inativo' ?>">
                  <?= htmlspecialchars($c['situacao_descricao']) ?>
                </span>
              <?php else: ?>
                —
              <?php endif; ?>
            </td>
            <td>
              <div class="coluna-acoes">
                <a class="btn-icone" href="contrato-form.php?id=<?= $c['id_contrato'] ?>" title="Editar">
                  <i class="fa-solid fa-pen"></i>
                </a>
                <form method="post" action="contrato-excluir.php" onsubmit="return confirm('Excluir o contrato \'<?= htmlspecialchars(addslashes($c['numero_contrato'] ?: 'sem número')) ?>\'? Essa ação não pode ser desfeita.');" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $c['id_contrato'] ?>">
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
          <a href="<?= manterFiltrosCon(['pagina' => $pagina - 1]) ?>"><i class="fa-solid fa-chevron-left"></i></a>
        <?php endif; ?>
        <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
          <?php if ($p === $pagina): ?>
            <span class="atual"><?= $p ?></span>
          <?php else: ?>
            <a href="<?= manterFiltrosCon(['pagina' => $p]) ?>"><?= $p ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($pagina < $totalPaginas): ?>
          <a href="<?= manterFiltrosCon(['pagina' => $pagina + 1]) ?>"><i class="fa-solid fa-chevron-right"></i></a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
