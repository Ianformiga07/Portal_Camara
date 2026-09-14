<?php
require_once __DIR__ . '/includes/auth.php';

$tituloPagina = 'Recursos Humanos';

$busca  = trim($_GET['busca'] ?? '');
$status = $_GET['status'] ?? '';
$pagina = max(1, (int) ($_GET['pagina'] ?? 1));
$porPagina = 12;

$condicoes = [];
$parametros = [];

if ($busca !== '') {
    $condicoes[] = '(s.nome_completo LIKE :busca OR s.cpf LIKE :busca OR s.matricula LIKE :busca)';
    $parametros['busca'] = '%' . $busca . '%';
}
if ($status === '1' || $status === '0') {
    $condicoes[] = 's.ativo = :status';
    $parametros['status'] = $status;
}
$whereSql = $condicoes ? 'WHERE ' . implode(' AND ', $condicoes) : '';

$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM servidores s $whereSql");
$stmtTotal->execute($parametros);
$totalRegistros = (int) $stmtTotal->fetchColumn();
$totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;

$sql = "SELECT s.id_servidor, s.nome_completo, s.matricula, s.ativo, s.foto_perfil,
               c.descricao AS cargo_descricao, d.descricao AS departamento_descricao
        FROM servidores s
        LEFT JOIN cargos c ON c.id_cargo = s.id_cargo
        LEFT JOIN departamentos d ON d.id_departamento = s.id_departamento
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
$servidores = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';

function manterFiltrosServ(array $substituir = []): string
{
    $params = array_merge($_GET, $substituir);
    return htmlspecialchars('?' . http_build_query($params));
}
?>

<h1 class="pagina__titulo">Recursos Humanos</h1>
<p class="pagina__subtitulo">Cadastro de servidores da Câmara (inclui vereadores, que também são servidores).</p>

<?php if (!empty($_GET['ok'])): ?>
  <div class="alerta alerta-sucesso" style="max-width:none;">
    <i class="fa-solid fa-circle-check"></i>
    <?= $_GET['ok'] === 'excluido' ? 'Servidor excluído com sucesso.' : 'Servidor salvo com sucesso.' ?>
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
    <input type="text" name="busca" placeholder="Buscar por nome, CPF ou matrícula..." value="<?= htmlspecialchars($busca) ?>">
    <select name="status" onchange="this.form.submit()">
      <option value="">Todos os status</option>
      <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Ativos</option>
      <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Inativos</option>
    </select>
    <button type="submit" class="btn btn-secundario"><i class="fa-solid fa-magnifying-glass"></i></button>
  </form>

  <a href="servidor-form.php" class="btn btn-primario" style="width:auto;">
    <i class="fa-solid fa-plus"></i> Novo servidor
  </a>
</div>

<div class="painel">
  <?php if (empty($servidores)): ?>
    <div class="vazio">
      <i class="fa-solid fa-id-card"></i>
      <p>Nenhum servidor encontrado<?= $busca !== '' ? ' para "' . htmlspecialchars($busca) . '"' : '' ?>.</p>
    </div>
  <?php else: ?>
    <table class="tabela-simples">
      <thead>
        <tr>
          <th style="width:60px;"></th>
          <th>Nome</th>
          <th>Cargo</th>
          <th>Departamento</th>
          <th>Situação</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($servidores as $s): ?>
          <tr>
            <td>
              <?php if ($s['foto_perfil']): ?>
                <img class="miniatura" style="width:32px;height:32px;border-radius:50%;object-fit:cover;" src="assets/uploads/servidores/<?= htmlspecialchars($s['foto_perfil']) ?>" alt="">
              <?php else: ?>
                <img class="miniatura" style="width:32px;height:32px;border-radius:50%;" src="assets/img/avatar-padrao.svg" alt="">
              <?php endif; ?>
            </td>
            <td class="titulo-linha"><?= htmlspecialchars($s['nome_completo']) ?></td>
            <td><?= htmlspecialchars($s['cargo_descricao'] ?: '—') ?></td>
            <td><?= htmlspecialchars($s['departamento_descricao'] ?: '—') ?></td>
            <td>
              <?php if ($s['ativo']): ?>
                <span class="badge badge-ok">Ativo</span>
              <?php else: ?>
                <span class="badge badge-inativo">Inativo</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="coluna-acoes">
                <a class="btn-icone" href="servidor-form.php?id=<?= $s['id_servidor'] ?>" title="Editar">
                  <i class="fa-solid fa-pen"></i>
                </a>
                <form method="post" action="servidor-excluir.php" onsubmit="return confirm('Excluir o servidor \'<?= htmlspecialchars(addslashes($s['nome_completo'])) ?>\'? Essa ação não pode ser desfeita.');" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $s['id_servidor'] ?>">
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
          <a href="<?= manterFiltrosServ(['pagina' => $pagina - 1]) ?>"><i class="fa-solid fa-chevron-left"></i></a>
        <?php endif; ?>
        <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
          <?php if ($p === $pagina): ?>
            <span class="atual"><?= $p ?></span>
          <?php else: ?>
            <a href="<?= manterFiltrosServ(['pagina' => $p]) ?>"><?= $p ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($pagina < $totalPaginas): ?>
          <a href="<?= manterFiltrosServ(['pagina' => $pagina + 1]) ?>"><i class="fa-solid fa-chevron-right"></i></a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
