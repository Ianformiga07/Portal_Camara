<?php
require_once __DIR__ . '/includes/auth.php';

$tituloPagina = 'Obras';

$busca = trim($_GET['busca'] ?? '');
$sql = 'SELECT * FROM obras';
$params = [];
if ($busca !== '') {
    $sql .= ' WHERE nome LIKE :busca';
    $params['busca'] = '%' . $busca . '%';
}
$sql .= ' ORDER BY data_inicio DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$obras = $stmt->fetchAll();

$situacoes = ['planejada' => 'Planejada', 'andamento' => 'Em andamento', 'concluida' => 'Concluída', 'paralisada' => 'Paralisada'];

include __DIR__ . '/includes/header.php';
?>

<h1 class="pagina__titulo">Obras</h1>
<p class="pagina__subtitulo">Obras e serviços de engenharia exibidos na página pública "Obras".</p>

<?php if (!empty($_GET['ok'])): ?>
  <div class="alerta alerta-sucesso" style="max-width:none;">
    <i class="fa-solid fa-circle-check"></i>
    <?= $_GET['ok'] === 'excluido' ? 'Obra excluída com sucesso.' : 'Obra salva com sucesso.' ?>
  </div>
<?php endif; ?>

<div class="toolbar">
  <form class="toolbar__busca" method="get">
    <input type="text" name="busca" placeholder="Buscar por nome..." value="<?= htmlspecialchars($busca) ?>">
    <button type="submit" class="btn btn-secundario"><i class="fa-solid fa-magnifying-glass"></i></button>
  </form>
  <a href="obra-form.php" class="btn btn-primario" style="width:auto;"><i class="fa-solid fa-plus"></i> Nova obra</a>
</div>

<div class="painel">
  <?php if (empty($obras)): ?>
    <div class="vazio"><i class="fa-solid fa-helmet-safety"></i><p>Nenhuma obra cadastrada.</p></div>
  <?php else: ?>
    <table class="tabela-simples">
      <thead><tr><th>Obra</th><th>Situação</th><th>Execução</th><th>Valor</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($obras as $o): ?>
          <tr>
            <td>
              <div class="titulo-linha"><?= htmlspecialchars($o['nome']) ?></div>
              <div class="descricao-linha"><?= htmlspecialchars($o['localizacao'] ?: '—') ?></div>
            </td>
            <td><?= htmlspecialchars($situacoes[$o['situacao']] ?? $o['situacao']) ?></td>
            <td><?= (int) $o['percentual_execucao'] ?>%</td>
            <td><?= $o['valor'] !== null ? 'R$ ' . number_format((float) $o['valor'], 2, ',', '.') : '—' ?></td>
            <td>
              <div class="coluna-acoes">
                <a class="btn-icone" href="obra-form.php?id=<?= $o['id_obra'] ?>" title="Editar"><i class="fa-solid fa-pen"></i></a>
                <form method="post" action="obra-excluir.php" onsubmit="return confirm('Excluir esta obra?');" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $o['id_obra'] ?>">
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
