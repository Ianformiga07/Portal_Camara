<?php
require_once __DIR__ . '/includes/auth.php';

$tituloPagina = 'Ouvidoria';

$busca    = trim($_GET['busca'] ?? '');
$tipo     = $_GET['tipo'] ?? '';
$situacao = $_GET['situacao'] ?? '';
$pagina   = max(1, (int) ($_GET['pagina'] ?? 1));
$porPagina = 12;

$tipos = $pdo->query('SELECT id_tipo_manifestacao, descricao FROM tipos_manifestacao ORDER BY descricao')->fetchAll();

$condicoes = [];
$parametros = [];

if ($busca !== '') {
    $condicoes[] = '(m.protocolo LIKE :busca OR m.nome LIKE :busca)';
    $parametros['busca'] = '%' . $busca . '%';
}
if ($tipo !== '') {
    $condicoes[] = 'm.id_tipo_manifestacao = :tipo';
    $parametros['tipo'] = $tipo;
}
if ($situacao === '1' || $situacao === '0') {
    $condicoes[] = 'm.respondida = :situacao';
    $parametros['situacao'] = $situacao;
}
$whereSql = $condicoes ? 'WHERE ' . implode(' AND ', $condicoes) : '';

$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM manifestacoes m $whereSql");
$stmtTotal->execute($parametros);
$totalRegistros = (int) $stmtTotal->fetchColumn();
$totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;

$sql = "SELECT m.id_manifestacao, m.protocolo, m.nome, m.anonimo, m.criado_em, m.respondida,
               t.descricao AS tipo_descricao
        FROM manifestacoes m
        LEFT JOIN tipos_manifestacao t ON t.id_tipo_manifestacao = m.id_tipo_manifestacao
        $whereSql
        ORDER BY m.respondida ASC, m.criado_em DESC
        LIMIT :limite OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($parametros as $chave => $valor) {
    $stmt->bindValue($chave, $valor);
}
$stmt->bindValue('limite', $porPagina, PDO::PARAM_INT);
$stmt->bindValue('offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$manifestacoes = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';

function manterFiltrosMan(array $substituir = []): string
{
    $params = array_merge($_GET, $substituir);
    return htmlspecialchars('?' . http_build_query($params));
}
?>

<h1 class="pagina__titulo">Ouvidoria</h1>
<p class="pagina__subtitulo">Manifestações recebidas pelo canal de ouvidoria do portal.</p>

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
        <option value="<?= $t['id_tipo_manifestacao'] ?>" <?= (string) $tipo === (string) $t['id_tipo_manifestacao'] ? 'selected' : '' ?>>
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
  <?php if (empty($manifestacoes)): ?>
    <div class="vazio">
      <i class="fa-solid fa-comments"></i>
      <p>Nenhuma manifestação encontrada<?= $busca !== '' ? ' para "' . htmlspecialchars($busca) . '"' : '' ?>.</p>
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
        <?php foreach ($manifestacoes as $m): ?>
          <tr>
            <td class="titulo-linha"><?= htmlspecialchars($m['protocolo']) ?></td>
            <td><?= $m['anonimo'] ? '<em>Anônimo</em>' : htmlspecialchars($m['nome'] ?: '—') ?></td>
            <td><?= htmlspecialchars($m['tipo_descricao'] ?: '—') ?></td>
            <td><?= date('d/m/Y H:i', strtotime($m['criado_em'])) ?></td>
            <td>
              <?php if ($m['respondida']): ?>
                <span class="badge badge-ok">Respondida</span>
              <?php else: ?>
                <span class="badge badge-pendente">Pendente</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="coluna-acoes">
                <a class="btn-icone" href="manifestacao-form.php?id=<?= $m['id_manifestacao'] ?>" title="Ver e responder">
                  <i class="fa-solid fa-reply"></i>
                </a>
                <form method="post" action="manifestacao-excluir.php" onsubmit="return confirm('Excluir esta manifestação? Essa ação não pode ser desfeita.');" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $m['id_manifestacao'] ?>">
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
          <a href="<?= manterFiltrosMan(['pagina' => $pagina - 1]) ?>"><i class="fa-solid fa-chevron-left"></i></a>
        <?php endif; ?>
        <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
          <?php if ($p === $pagina): ?>
            <span class="atual"><?= $p ?></span>
          <?php else: ?>
            <a href="<?= manterFiltrosMan(['pagina' => $p]) ?>"><?= $p ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($pagina < $totalPaginas): ?>
          <a href="<?= manterFiltrosMan(['pagina' => $pagina + 1]) ?>"><i class="fa-solid fa-chevron-right"></i></a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
