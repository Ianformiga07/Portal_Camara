<?php
require_once __DIR__ . '/includes/auth.php';

$tituloPagina = 'Vereadores';

$busca   = trim($_GET['busca'] ?? '');
$mandato = $_GET['mandato'] ?? '';
$status  = $_GET['status'] ?? '';
$pagina  = max(1, (int) ($_GET['pagina'] ?? 1));
$porPagina = 12;

$mandatos = $pdo->query('SELECT id_mandato, descricao FROM mandatos_eletivos ORDER BY id_mandato DESC')->fetchAll();

$condicoes = [];
$parametros = [];

if ($busca !== '') {
    $condicoes[] = '(s.nome_completo LIKE :busca OR v.apelido LIKE :busca OR v.partido LIKE :busca)';
    $parametros['busca'] = '%' . $busca . '%';
}
if ($mandato !== '') {
    $condicoes[] = 'v.id_mandato = :mandato';
    $parametros['mandato'] = $mandato;
}
if ($status === '1' || $status === '0') {
    $condicoes[] = 'v.ativo = :status';
    $parametros['status'] = $status;
}
$whereSql = $condicoes ? 'WHERE ' . implode(' AND ', $condicoes) : '';

$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM vereadores v JOIN servidores s ON s.id_servidor = v.id_servidor $whereSql");
$stmtTotal->execute($parametros);
$totalRegistros = (int) $stmtTotal->fetchColumn();
$totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;

$sql = "SELECT v.id_vereador, v.apelido, v.partido, v.ativo, s.nome_completo, s.foto_perfil,
               m.descricao AS mandato_descricao
        FROM vereadores v
        JOIN servidores s ON s.id_servidor = v.id_servidor
        LEFT JOIN mandatos_eletivos m ON m.id_mandato = v.id_mandato
        $whereSql
        ORDER BY s.nome_completo
        LIMIT :limite OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($parametros as $chave => $valor) {
    $stmt->bindValue($chave, $valor);
}
$stmt->bindValue('limite', $porPagina, PDO::PARAM_INT);
$stmt->bindValue('offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$vereadores = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';

function manterFiltrosVer(array $substituir = []): string
{
    $params = array_merge($_GET, $substituir);
    return htmlspecialchars('?' . http_build_query($params));
}
?>

<h1 class="pagina__titulo">Vereadores</h1>
<p class="pagina__subtitulo">Cadastro dos vereadores exibidos no portal, vinculados ao mandato eletivo atual.</p>

<?php if (!empty($_GET['ok'])): ?>
  <div class="alerta alerta-sucesso" style="max-width:none;">
    <i class="fa-solid fa-circle-check"></i>
    <?= $_GET['ok'] === 'excluido' ? 'Vereador excluído com sucesso.' : 'Vereador salvo com sucesso.' ?>
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
    <input type="text" name="busca" placeholder="Buscar por nome, apelido ou partido..." value="<?= htmlspecialchars($busca) ?>">
    <select name="mandato" onchange="this.form.submit()">
      <option value="">Todos os mandatos</option>
      <?php foreach ($mandatos as $m): ?>
        <option value="<?= $m['id_mandato'] ?>" <?= (string) $mandato === (string) $m['id_mandato'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($m['descricao']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <select name="status" onchange="this.form.submit()">
      <option value="">Todos os status</option>
      <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Em exercício</option>
      <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Inativos</option>
    </select>
    <button type="submit" class="btn btn-secundario"><i class="fa-solid fa-magnifying-glass"></i></button>
  </form>

  <a href="vereador-form.php" class="btn btn-primario" style="width:auto;">
    <i class="fa-solid fa-plus"></i> Novo vereador
  </a>
</div>

<div class="painel">
  <?php if (empty($vereadores)): ?>
    <div class="vazio">
      <i class="fa-solid fa-users"></i>
      <p>Nenhum vereador encontrado<?= $busca !== '' ? ' para "' . htmlspecialchars($busca) . '"' : '' ?>.</p>
    </div>
  <?php else: ?>
    <table class="tabela-simples">
      <thead>
        <tr>
          <th style="width:60px;"></th>
          <th>Nome</th>
          <th>Partido</th>
          <th>Mandato</th>
          <th>Situação</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($vereadores as $v): ?>
          <tr>
            <td>
              <?php if ($v['foto_perfil']): ?>
                <img class="miniatura" style="width:32px;height:32px;border-radius:50%;object-fit:cover;" src="assets/uploads/vereadores/<?= htmlspecialchars($v['foto_perfil']) ?>" alt="">
              <?php else: ?>
                <img class="miniatura" style="width:32px;height:32px;border-radius:50%;" src="assets/img/avatar-padrao.svg" alt="">
              <?php endif; ?>
            </td>
            <td>
              <div class="titulo-linha"><?= htmlspecialchars($v['nome_completo']) ?></div>
              <?php if ($v['apelido']): ?>
                <div class="descricao-linha">"<?= htmlspecialchars($v['apelido']) ?>"</div>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($v['partido'] ?: '—') ?></td>
            <td><?= htmlspecialchars($v['mandato_descricao'] ?: '—') ?></td>
            <td>
              <?php if ($v['ativo']): ?>
                <span class="badge badge-ok">Em exercício</span>
              <?php else: ?>
                <span class="badge badge-inativo">Inativo</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="coluna-acoes">
                <a class="btn-icone" href="vereador-form.php?id=<?= $v['id_vereador'] ?>" title="Editar">
                  <i class="fa-solid fa-pen"></i>
                </a>
                <form method="post" action="vereador-excluir.php" onsubmit="return confirm('Excluir o vereador \'<?= htmlspecialchars(addslashes($v['nome_completo'])) ?>\'? Essa ação não pode ser desfeita.');" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $v['id_vereador'] ?>">
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
          <a href="<?= manterFiltrosVer(['pagina' => $pagina - 1]) ?>"><i class="fa-solid fa-chevron-left"></i></a>
        <?php endif; ?>
        <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
          <?php if ($p === $pagina): ?>
            <span class="atual"><?= $p ?></span>
          <?php else: ?>
            <a href="<?= manterFiltrosVer(['pagina' => $p]) ?>"><?= $p ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($pagina < $totalPaginas): ?>
          <a href="<?= manterFiltrosVer(['pagina' => $pagina + 1]) ?>"><i class="fa-solid fa-chevron-right"></i></a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
