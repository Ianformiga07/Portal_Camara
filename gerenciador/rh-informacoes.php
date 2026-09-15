<?php
require_once __DIR__ . '/includes/auth.php';

$tituloPagina = 'Recursos Humanos — Informações Complementares';

$categorias = [
    'cargos-comissionados' => 'Cargos Comissionados',
    'tabela-remuneratoria' => 'Tabela Remuneratória',
    'folha-servidores'     => 'Folha — Servidores',
    'folha-estagiarios'    => 'Folha — Estagiários',
    'terceirizados'        => 'Terceirizados',
];

$catFiltro = $_GET['cat'] ?? '';
$busca = trim($_GET['busca'] ?? '');

$condicoes = [];
$parametros = [];
if ($catFiltro !== '' && isset($categorias[$catFiltro])) {
    $condicoes[] = 'categoria = :cat';
    $parametros['cat'] = $catFiltro;
}
if ($busca !== '') {
    $condicoes[] = '(referencia LIKE :busca OR descricao LIKE :busca)';
    $parametros['busca'] = '%' . $busca . '%';
}
$whereSql = $condicoes ? 'WHERE ' . implode(' AND ', $condicoes) : '';

$stmt = $pdo->prepare("SELECT * FROM rh_informacoes $whereSql ORDER BY categoria, id_rh DESC");
$stmt->execute($parametros);
$registros = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<h1 class="pagina__titulo">Recursos Humanos — Informações Complementares</h1>
<p class="pagina__subtitulo">Cargos comissionados, tabela remuneratória, folha de pagamento e terceirizados exibidos no Portal da Transparência. Editais de concurso são cadastrados em Legislação/Documentos.</p>

<?php if (!empty($_GET['ok'])): ?>
  <div class="alerta alerta-sucesso" style="max-width:none;">
    <i class="fa-solid fa-circle-check"></i>
    <?= $_GET['ok'] === 'excluido' ? 'Registro excluído com sucesso.' : 'Registro salvo com sucesso.' ?>
  </div>
<?php endif; ?>

<div class="toolbar">
  <form class="toolbar__busca" method="get">
    <input type="text" name="busca" placeholder="Buscar por cargo/referência..." value="<?= htmlspecialchars($busca) ?>">
    <select name="cat" onchange="this.form.submit()">
      <option value="">Todas as categorias</option>
      <?php foreach ($categorias as $slug => $nome): ?>
        <option value="<?= $slug ?>" <?= $catFiltro === $slug ? 'selected' : '' ?>><?= htmlspecialchars($nome) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-secundario"><i class="fa-solid fa-magnifying-glass"></i></button>
  </form>

  <a href="rh-informacao-form.php" class="btn btn-primario" style="width:auto;">
    <i class="fa-solid fa-plus"></i> Novo registro
  </a>
</div>

<div class="painel">
  <?php if (empty($registros)): ?>
    <div class="vazio"><i class="fa-solid fa-id-card"></i><p>Nenhum registro encontrado.</p></div>
  <?php else: ?>
    <table class="tabela-simples">
      <thead>
        <tr>
          <th>Categoria</th>
          <th>Referência</th>
          <th>Competência</th>
          <th>Valor</th>
          <th>Situação</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($registros as $r): ?>
          <tr>
            <td><?= htmlspecialchars($categorias[$r['categoria']] ?? $r['categoria']) ?></td>
            <td>
              <div class="titulo-linha"><?= htmlspecialchars($r['referencia']) ?></div>
              <?php if ($r['descricao']): ?><div class="descricao-linha"><?= htmlspecialchars($r['descricao']) ?></div><?php endif; ?>
            </td>
            <td><?= $r['competencia'] ? htmlspecialchars($r['competencia']) : '—' ?></td>
            <td><?= $r['valor'] !== null ? 'R$ ' . number_format((float) $r['valor'], 2, ',', '.') : '—' ?></td>
            <td><?= $r['status'] ? '<span class="badge badge-ok">Ativo</span>' : '<span class="badge badge-inativo">Inativo</span>' ?></td>
            <td>
              <div class="coluna-acoes">
                <a class="btn-icone" href="rh-informacao-form.php?id=<?= $r['id_rh'] ?>" title="Editar"><i class="fa-solid fa-pen"></i></a>
                <form method="post" action="rh-informacao-excluir.php" onsubmit="return confirm('Excluir este registro?');" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $r['id_rh'] ?>">
                  <button type="submit" class="btn-icone perigo" title="Excluir"><i class="fa-solid fa-trash"></i></button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
