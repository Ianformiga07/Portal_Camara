<?php
require_once __DIR__ . '/includes/auth.php';

$tituloPagina = 'Licitantes/Contratados Sancionados';

$busca = trim($_GET['busca'] ?? '');
$sql = 'SELECT * FROM licitantes_sancionados';
$params = [];
if ($busca !== '') {
    $sql .= ' WHERE nome LIKE :busca OR cnpj_cpf LIKE :busca';
    $params['busca'] = '%' . $busca . '%';
}
$sql .= ' ORDER BY data_inicio DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$sancionados = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<h1 class="pagina__titulo">Licitantes/Contratados Sancionados</h1>
<p class="pagina__subtitulo">Relação exibida na página pública "Licitantes e/ou Contratados Sancionados".</p>

<?php if (!empty($_GET['ok'])): ?>
  <div class="alerta alerta-sucesso" style="max-width:none;">
    <i class="fa-solid fa-circle-check"></i>
    <?= $_GET['ok'] === 'excluido' ? 'Registro excluído com sucesso.' : 'Registro salvo com sucesso.' ?>
  </div>
<?php endif; ?>

<div class="toolbar">
  <form class="toolbar__busca" method="get">
    <input type="text" name="busca" placeholder="Buscar por nome ou CNPJ/CPF..." value="<?= htmlspecialchars($busca) ?>">
    <button type="submit" class="btn btn-secundario"><i class="fa-solid fa-magnifying-glass"></i></button>
  </form>
  <a href="licitante-sancionado-form.php" class="btn btn-primario" style="width:auto;"><i class="fa-solid fa-plus"></i> Novo registro</a>
</div>

<div class="painel">
  <?php if (empty($sancionados)): ?>
    <div class="vazio"><i class="fa-solid fa-ban"></i><p>Nenhum registro cadastrado. Isso é o normal se não houver sanções ativas — a página pública mostrará "nenhuma sanção registrada".</p></div>
  <?php else: ?>
    <table class="tabela-simples">
      <thead><tr><th>Empresa</th><th>Tipo de sanção</th><th>Vigência</th><th>Situação</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($sancionados as $s): ?>
          <tr>
            <td>
              <div class="titulo-linha"><?= htmlspecialchars($s['nome']) ?></div>
              <div class="descricao-linha"><?= htmlspecialchars($s['cnpj_cpf'] ?: '—') ?></div>
            </td>
            <td><?= htmlspecialchars($s['tipo_sancao']) ?></td>
            <td><?= $s['data_inicio'] ? date('d/m/Y', strtotime($s['data_inicio'])) : '—' ?> a <?= $s['data_fim'] ? date('d/m/Y', strtotime($s['data_fim'])) : '—' ?></td>
            <td><?= $s['status'] ? '<span class="badge badge-ok">Ativo</span>' : '<span class="badge badge-inativo">Inativo</span>' ?></td>
            <td>
              <div class="coluna-acoes">
                <a class="btn-icone" href="licitante-sancionado-form.php?id=<?= $s['id_sancao'] ?>" title="Editar"><i class="fa-solid fa-pen"></i></a>
                <form method="post" action="licitante-sancionado-excluir.php" onsubmit="return confirm('Excluir este registro?');" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $s['id_sancao'] ?>">
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
