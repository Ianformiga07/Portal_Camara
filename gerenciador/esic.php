<?php
require_once __DIR__ . '/includes/auth.php';

$tituloPagina = 'e-SIC';

$busca    = trim($_GET['busca'] ?? '');
$tipo     = $_GET['tipo'] ?? '';
$situacao = $_GET['situacao'] ?? '';
$pagina   = max(1, (int) ($_GET['pagina'] ?? 1));
$porPagina = 12;

$tipos = $pdo->query('SELECT id_tipo_esic, descricao FROM tipos_esic ORDER BY descricao')->fetchAll();

$condicoes = [];
$parametros = [];

if ($busca !== '') {
    $condicoes[] = '(e.protocolo LIKE :busca OR e.nome LIKE :busca)';
    $parametros['busca'] = '%' . $busca . '%';
}
if ($tipo !== '') {
    $condicoes[] = 'e.id_tipo_esic = :tipo';
    $parametros['tipo'] = $tipo;
}
if ($situacao === '1' || $situacao === '0') {
    $condicoes[] = 'e.respondida = :situacao';
    $parametros['situacao'] = $situacao;
}
$whereSql = $condicoes ? 'WHERE ' . implode(' AND ', $condicoes) : '';

$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM esic_solicitacoes e $whereSql");
$stmtTotal->execute($parametros);
$totalRegistros = (int) $stmtTotal->fetchColumn();
$totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;

$sql = "SELECT e.id_esic, e.protocolo, e.nome, e.anonimo, e.criado_em, e.respondida,
               t.descricao AS tipo_descricao
        FROM esic_solicitacoes e
        LEFT JOIN tipos_esic t ON t.id_tipo_esic = e.id_tipo_esic
        $whereSql
        ORDER BY e.respondida ASC, e.criado_em DESC
        LIMIT :limite OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($parametros as $chave => $valor) {
    $stmt->bindValue($chave, $valor);
}
$stmt->bindValue('limite', $porPagina, PDO::PARAM_INT);
$stmt->bindValue('offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$solicitacoes = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';

function manterFiltrosEsic(array $substituir = []): string
{
    $params = array_merge($_GET, $substituir);
    return htmlspecialchars('?' . http_build_query($params));
}

// prazo legal de resposta (LAI): 20 dias corridos, prorrogável por mais 10
function diasEmAberto(string $criadoEm): int
{
    return (int) floor((time() - strtotime($criadoEm)) / 86400);
}
?>

<h1 class="pagina__titulo">e-SIC — Serviço de Informação ao Cidadão</h1>
<p class="pagina__subtitulo">Pedidos de acesso à informação recebidos pelo portal (Lei nº 12.527/2011).</p>

<?php if (!empty($_GET['ok'])): ?>
  <div class="alerta alerta-sucesso" style="max-width:none;">
    <i class="fa-solid fa-circle-check"></i> Resposta registrada com sucesso.
  </div>
<?php endif; ?>

<div class="toolbar">
  <form class="toolbar__busca" method="get" style="max-width:600px;">
    <input type="text" name="busca" placeholder="Buscar por protocolo ou nome..." value="<?= htmlspecialchars($busca) ?>">
    <select name="tipo" onchange="this.form.submit()">
      <option value="">Todos os tipos</option>
      <?php foreach ($tipos as $t): ?>
        <option value="<?= $t['id_tipo_esic'] ?>" <?= (string) $tipo === (string) $t['id_tipo_esic'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($t['descricao']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <select name="situacao" onchange="this.form.submit()">
      <option value="">Todas as situações</option>
      <option value="0" <?= $situacao === '0' ? 'selected' : '' ?>>Pendentes</option>
      <option value="1" <?= $situacao === '1' ? 'selected' : '' ?>>Respondidas</option>
    </select>
    <button type="submit" class="btn btn-secundario"><i class="fa-solid fa-magnifying-glass"></i></button>
  </form>
</div>

<div class="painel">
  <?php if (empty($solicitacoes)): ?>
    <div class="vazio">
      <i class="fa-solid fa-scale-balanced"></i>
      <p>Nenhuma solicitação encontrada<?= $busca !== '' ? ' para "' . htmlspecialchars($busca) . '"' : '' ?>.</p>
    </div>
  <?php else: ?>
    <table class="tabela-simples">
      <thead>
        <tr>
          <th>Protocolo</th>
          <th>Nome</th>
          <th>Tipo</th>
          <th>Recebida em</th>
          <th>Situação</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($solicitacoes as $s): ?>
          <?php $dias = diasEmAberto($s['criado_em']); ?>
          <tr>
            <td class="titulo-linha"><?= htmlspecialchars($s['protocolo']) ?></td>
            <td><?= $s['anonimo'] ? '<em>Anônimo</em>' : htmlspecialchars($s['nome'] ?: '—') ?></td>
            <td><?= htmlspecialchars($s['tipo_descricao'] ?: '—') ?></td>
            <td><?= date('d/m/Y H:i', strtotime($s['criado_em'])) ?></td>
            <td>
              <?php if ($s['respondida']): ?>
                <span class="badge badge-ok">Respondida</span>
              <?php elseif ($dias >= 18): ?>
                <span class="badge" style="background:#fbe9e8; color:var(--vermelho);">Pendente (<?= $dias ?>d — prazo próximo)</span>
              <?php else: ?>
                <span class="badge badge-pendente">Pendente (<?= $dias ?>d)</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="coluna-acoes">
                <a class="btn-icone" href="esic-form.php?id=<?= $s['id_esic'] ?>" title="Ver e responder">
                  <i class="fa-solid fa-reply"></i>
                </a>
                <form method="post" action="esic-excluir.php" onsubmit="return confirm('Excluir esta solicitação? Essa ação não pode ser desfeita.');" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $s['id_esic'] ?>">
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
          <a href="<?= manterFiltrosEsic(['pagina' => $pagina - 1]) ?>"><i class="fa-solid fa-chevron-left"></i></a>
        <?php endif; ?>
        <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
          <?php if ($p === $pagina): ?>
            <span class="atual"><?= $p ?></span>
          <?php else: ?>
            <a href="<?= manterFiltrosEsic(['pagina' => $p]) ?>"><?= $p ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($pagina < $totalPaginas): ?>
          <a href="<?= manterFiltrosEsic(['pagina' => $pagina + 1]) ?>"><i class="fa-solid fa-chevron-right"></i></a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
