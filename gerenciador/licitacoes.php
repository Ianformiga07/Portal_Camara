<?php
require_once __DIR__ . '/includes/auth.php';

$tituloPagina = 'Licitações';

$busca      = trim($_GET['busca'] ?? '');
$modalidade = $_GET['modalidade'] ?? '';
$situacao   = $_GET['situacao'] ?? '';
$pagina     = max(1, (int) ($_GET['pagina'] ?? 1));
$porPagina  = 12;

$modalidades = $pdo->query('SELECT id_modalidade, descricao FROM licitacao_modalidades ORDER BY descricao')->fetchAll();
$situacoes   = $pdo->query('SELECT id_situacao, descricao FROM licitacao_situacoes ORDER BY id_situacao')->fetchAll();

$condicoes = [];
$parametros = [];

if ($busca !== '') {
    $condicoes[] = '(l.numero_processo LIKE :busca OR l.objeto LIKE :busca)';
    $parametros['busca'] = '%' . $busca . '%';
}
if ($modalidade !== '') {
    $condicoes[] = 'l.id_modalidade = :modalidade';
    $parametros['modalidade'] = $modalidade;
}
if ($situacao !== '') {
    $condicoes[] = 'l.id_situacao = :situacao';
    $parametros['situacao'] = $situacao;
}
$whereSql = $condicoes ? 'WHERE ' . implode(' AND ', $condicoes) : '';

$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM licitacoes l $whereSql");
$stmtTotal->execute($parametros);
$totalRegistros = (int) $stmtTotal->fetchColumn();
$totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;

$sql = "SELECT l.id_licitacao, l.numero_processo, l.objeto, l.data_abertura, l.valor_estimado, l.status,
               m.descricao AS modalidade_descricao, s.descricao AS situacao_descricao
        FROM licitacoes l
        LEFT JOIN licitacao_modalidades m ON m.id_modalidade = l.id_modalidade
        LEFT JOIN licitacao_situacoes s ON s.id_situacao = l.id_situacao
        $whereSql
        ORDER BY l.data_abertura DESC, l.id_licitacao DESC
        LIMIT :limite OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($parametros as $chave => $valor) {
    $stmt->bindValue($chave, $valor);
}
$stmt->bindValue('limite', $porPagina, PDO::PARAM_INT);
$stmt->bindValue('offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$licitacoes = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';

function manterFiltrosLic(array $substituir = []): string
{
    $params = array_merge($_GET, $substituir);
    return htmlspecialchars('?' . http_build_query($params));
}
?>

<h1 class="pagina__titulo">Licitações</h1>
<p class="pagina__subtitulo">Processos licitatórios publicados no portal de transparência.</p>

<?php if (!empty($_GET['ok'])): ?>
  <div class="alerta alerta-sucesso" style="max-width:none;">
    <i class="fa-solid fa-circle-check"></i>
    <?= $_GET['ok'] === 'excluida' ? 'Licitação excluída com sucesso.' : 'Licitação salva com sucesso.' ?>
  </div>
<?php endif; ?>
<?php if (!empty($_GET['erro'])): ?>
  <div class="alerta alerta-erro" style="max-width:none;">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <?= htmlspecialchars($_GET['erro']) ?>
  </div>
<?php endif; ?>

<div class="toolbar">
  <form class="toolbar__busca" method="get" style="max-width:640px;">
    <input type="text" name="busca" placeholder="Buscar por processo ou objeto..." value="<?= htmlspecialchars($busca) ?>">
    <select name="modalidade" onchange="this.form.submit()">
      <option value="">Todas as modalidades</option>
      <?php foreach ($modalidades as $m): ?>
        <option value="<?= $m['id_modalidade'] ?>" <?= (string) $modalidade === (string) $m['id_modalidade'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($m['descricao']) ?>
        </option>
      <?php endforeach; ?>
    </select>
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
    <a href="licitacao-form.php" class="btn btn-primario" style="width:auto;">
      <i class="fa-solid fa-plus"></i> Nova licitação
    </a>
    <a href="licitacoes-exportar.php<?= manterFiltrosLic() ?>" class="btn btn-secundario" style="width:auto;">
      <i class="fa-solid fa-file-csv"></i> Exportar CSV
    </a>
  </div>
</div>

<div class="painel">
  <?php if (empty($licitacoes)): ?>
    <div class="vazio">
      <i class="fa-solid fa-file-contract"></i>
      <p>Nenhuma licitação encontrada<?= $busca !== '' ? ' para "' . htmlspecialchars($busca) . '"' : '' ?>.</p>
    </div>
  <?php else: ?>
    <table class="tabela-simples">
      <thead>
        <tr>
          <th>Processo</th>
          <th>Objeto</th>
          <th>Modalidade</th>
          <th>Abertura</th>
          <th>Valor estimado</th>
          <th>Situação</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($licitacoes as $l): ?>
          <tr>
            <td class="titulo-linha"><?= htmlspecialchars($l['numero_processo'] ?: '—') ?></td>
            <td><?= htmlspecialchars(mb_strimwidth($l['objeto'] ?? '', 0, 60, '...')) ?></td>
            <td><?= htmlspecialchars($l['modalidade_descricao'] ?: '—') ?></td>
            <td><?= $l['data_abertura'] ? date('d/m/Y', strtotime($l['data_abertura'])) : '—' ?></td>
            <td><?= $l['valor_estimado'] !== null ? 'R$ ' . number_format($l['valor_estimado'], 2, ',', '.') : '—' ?></td>
            <td>
              <?php if ($l['situacao_descricao']): ?>
                <span class="badge <?= in_array($l['situacao_descricao'], ['Finalizada', 'Adjudicada']) ? 'badge-ok' : 'badge-pendente' ?>">
                  <?= htmlspecialchars($l['situacao_descricao']) ?>
                </span>
              <?php else: ?>
                —
              <?php endif; ?>
            </td>
            <td>
              <div class="coluna-acoes">
                <a class="btn-icone" href="licitacao-form.php?id=<?= $l['id_licitacao'] ?>" title="Editar">
                  <i class="fa-solid fa-pen"></i>
                </a>
                <form method="post" action="licitacao-excluir.php" onsubmit="return confirm('Excluir a licitação \'<?= htmlspecialchars(addslashes($l['numero_processo'] ?: 'sem número')) ?>\'? Essa ação não pode ser desfeita.');" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $l['id_licitacao'] ?>">
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
          <a href="<?= manterFiltrosLic(['pagina' => $pagina - 1]) ?>"><i class="fa-solid fa-chevron-left"></i></a>
        <?php endif; ?>
        <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
          <?php if ($p === $pagina): ?>
            <span class="atual"><?= $p ?></span>
          <?php else: ?>
            <a href="<?= manterFiltrosLic(['pagina' => $p]) ?>"><?= $p ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($pagina < $totalPaginas): ?>
          <a href="<?= manterFiltrosLic(['pagina' => $pagina + 1]) ?>"><i class="fa-solid fa-chevron-right"></i></a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
