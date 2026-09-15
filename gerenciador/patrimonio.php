<?php
require_once __DIR__ . '/includes/auth.php';

$tituloPagina = 'Patrimônio';

$busca = trim($_GET['busca'] ?? '');
$sql = 'SELECT * FROM patrimonio';
$params = [];
if ($busca !== '') {
    $sql .= ' WHERE descricao LIKE :busca OR codigo LIKE :busca';
    $params['busca'] = '%' . $busca . '%';
}
$sql .= ' ORDER BY codigo ASC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$itens = $stmt->fetchAll();

$situacoes = ['em_uso' => 'Em uso', 'manutencao' => 'Em manutenção', 'baixado' => 'Baixado'];

include __DIR__ . '/includes/header.php';
?>

<h1 class="pagina__titulo">Patrimônio</h1>
<p class="pagina__subtitulo">Bens patrimoniais exibidos na página pública "Patrimônio e Almoxarifado".</p>

<?php if (!empty($_GET['ok'])): ?>
  <div class="alerta alerta-sucesso" style="max-width:none;">
    <i class="fa-solid fa-circle-check"></i>
    <?= $_GET['ok'] === 'excluido' ? 'Item excluído com sucesso.' : 'Item salvo com sucesso.' ?>
  </div>
<?php endif; ?>

<div class="toolbar">
  <form class="toolbar__busca" method="get">
    <input type="text" name="busca" placeholder="Buscar por código ou descrição..." value="<?= htmlspecialchars($busca) ?>">
    <button type="submit" class="btn btn-secundario"><i class="fa-solid fa-magnifying-glass"></i></button>
  </form>
  <a href="patrimonio-form.php" class="btn btn-primario" style="width:auto;"><i class="fa-solid fa-plus"></i> Novo item</a>
</div>

<div class="painel">
  <?php if (empty($itens)): ?>
    <div class="vazio"><i class="fa-solid fa-boxes-stacked"></i><p>Nenhum item cadastrado.</p></div>
  <?php else: ?>
    <table class="tabela-simples">
      <thead><tr><th>Código</th><th>Descrição</th><th>Situação</th><th>Valor</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($itens as $p): ?>
          <tr>
            <td><span class="badge badge-ok"><?= htmlspecialchars($p['codigo']) ?></span></td>
            <td>
              <div class="titulo-linha"><?= htmlspecialchars($p['descricao']) ?></div>
              <div class="descricao-linha"><?= htmlspecialchars($p['categoria'] ?: '—') ?> · <?= htmlspecialchars($p['localizacao'] ?: '—') ?></div>
            </td>
            <td><?= htmlspecialchars($situacoes[$p['situacao']] ?? $p['situacao']) ?></td>
            <td><?= $p['valor_aquisicao'] !== null ? 'R$ ' . number_format((float) $p['valor_aquisicao'], 2, ',', '.') : '—' ?></td>
            <td>
              <div class="coluna-acoes">
                <a class="btn-icone" href="patrimonio-form.php?id=<?= $p['id_patrimonio'] ?>" title="Editar"><i class="fa-solid fa-pen"></i></a>
                <form method="post" action="patrimonio-excluir.php" onsubmit="return confirm('Excluir este item?');" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $p['id_patrimonio'] ?>">
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
