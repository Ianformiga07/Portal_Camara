<?php
require_once __DIR__ . '/includes/auth.php';

$tituloPagina = 'Vídeos das Sessões';

$busca = trim($_GET['busca'] ?? '');
$sql = 'SELECT * FROM videos_sessoes';
$params = [];
if ($busca !== '') {
    $sql .= ' WHERE titulo LIKE :busca';
    $params['busca'] = '%' . $busca . '%';
}
$sql .= ' ORDER BY data_sessao DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$videos = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<h1 class="pagina__titulo">Vídeos das Sessões</h1>
<p class="pagina__subtitulo">Vídeos exibidos na página pública "Vídeos das Sessões Plenárias".</p>

<?php if (!empty($_GET['ok'])): ?>
  <div class="alerta alerta-sucesso" style="max-width:none;">
    <i class="fa-solid fa-circle-check"></i>
    <?= $_GET['ok'] === 'excluido' ? 'Vídeo excluído com sucesso.' : 'Vídeo salvo com sucesso.' ?>
  </div>
<?php endif; ?>

<div class="toolbar">
  <form class="toolbar__busca" method="get">
    <input type="text" name="busca" placeholder="Buscar por título..." value="<?= htmlspecialchars($busca) ?>">
    <button type="submit" class="btn btn-secundario"><i class="fa-solid fa-magnifying-glass"></i></button>
  </form>
  <a href="video-sessao-form.php" class="btn btn-primario" style="width:auto;"><i class="fa-solid fa-plus"></i> Novo vídeo</a>
</div>

<div class="painel">
  <?php if (empty($videos)): ?>
    <div class="vazio"><i class="fa-solid fa-video"></i><p>Nenhum vídeo cadastrado.</p></div>
  <?php else: ?>
    <table class="tabela-simples">
      <thead><tr><th>Título</th><th>Data da sessão</th><th>Situação</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($videos as $v): ?>
          <tr>
            <td>
              <div class="titulo-linha"><?= htmlspecialchars($v['titulo']) ?></div>
              <?php if ($v['descricao']): ?><div class="descricao-linha"><?= htmlspecialchars($v['descricao']) ?></div><?php endif; ?>
            </td>
            <td><?= $v['data_sessao'] ? date('d/m/Y', strtotime($v['data_sessao'])) : '—' ?></td>
            <td><?= $v['status'] ? '<span class="badge badge-ok">Ativo</span>' : '<span class="badge badge-inativo">Inativo</span>' ?></td>
            <td>
              <div class="coluna-acoes">
                <a class="btn-icone" href="video-sessao-form.php?id=<?= $v['id_video'] ?>" title="Editar"><i class="fa-solid fa-pen"></i></a>
                <form method="post" action="video-sessao-excluir.php" onsubmit="return confirm('Excluir este vídeo?');" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $v['id_video'] ?>">
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
