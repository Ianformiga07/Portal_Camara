<?php
require_once __DIR__ . '/includes/auth.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$editando = $id !== null;

$video = ['titulo' => '', 'data_sessao' => date('Y-m-d'), 'url_video' => '', 'descricao' => '', 'status' => 1];
$erros = [];

if ($editando) {
    $stmt = $pdo->prepare('SELECT * FROM videos_sessoes WHERE id_video = :id');
    $stmt->execute(['id' => $id]);
    $reg = $stmt->fetch();
    if (!$reg) { header('Location: videos-sessoes.php'); exit; }
    $video = $reg;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $video['titulo']      = trim($_POST['titulo'] ?? '');
    $video['data_sessao'] = $_POST['data_sessao'] ?? '';
    $video['url_video']   = trim($_POST['url_video'] ?? '');
    $video['descricao']   = trim($_POST['descricao'] ?? '');
    $video['status']      = isset($_POST['status']) ? 1 : 0;

    if ($video['titulo'] === '') $erros[] = 'Informe o título.';
    if ($video['url_video'] === '') $erros[] = 'Informe o link do vídeo (embed do YouTube, por exemplo).';

    if (empty($erros)) {
        if ($editando) {
            $stmt = $pdo->prepare('UPDATE videos_sessoes SET titulo=:titulo, data_sessao=:data_sessao, url_video=:url_video, descricao=:descricao, status=:status WHERE id_video=:id');
            $stmt->execute(['titulo' => $video['titulo'], 'data_sessao' => $video['data_sessao'] ?: null, 'url_video' => $video['url_video'], 'descricao' => $video['descricao'] ?: null, 'status' => $video['status'], 'id' => $id]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO videos_sessoes (titulo, data_sessao, url_video, descricao, status) VALUES (:titulo, :data_sessao, :url_video, :descricao, :status)');
            $stmt->execute(['titulo' => $video['titulo'], 'data_sessao' => $video['data_sessao'] ?: null, 'url_video' => $video['url_video'], 'descricao' => $video['descricao'] ?: null, 'status' => $video['status']]);
        }
        header('Location: videos-sessoes.php?ok=1');
        exit;
    }
}

include __DIR__ . '/includes/header.php';
?>
<h1 class="pagina__titulo"><?= $editando ? 'Editar vídeo' : 'Novo vídeo' ?></h1>
<p class="pagina__subtitulo"><a href="videos-sessoes.php">← Voltar para a lista</a></p>

<?php if (!empty($erros)): ?>
  <div class="alerta alerta-erro" style="max-width:none;"><i class="fa-solid fa-triangle-exclamation"></i> <?= implode('<br>', array_map('htmlspecialchars', $erros)) ?></div>
<?php endif; ?>

<form class="painel" method="post">
  <div class="form-grid">
    <div class="campo campo-largo">
      <label for="titulo">Título *</label>
      <input type="text" id="titulo" name="titulo" maxlength="200" required value="<?= htmlspecialchars($video['titulo']) ?>">
    </div>
    <div class="campo">
      <label for="data_sessao">Data da sessão</label>
      <input type="date" id="data_sessao" name="data_sessao" value="<?= htmlspecialchars($video['data_sessao'] ?? '') ?>">
    </div>
    <div class="campo campo-largo">
      <label for="url_video">Link do vídeo (embed) *</label>
      <input type="text" id="url_video" name="url_video" maxlength="255" required placeholder="https://www.youtube.com/embed/XXXXXXXXXXX" value="<?= htmlspecialchars($video['url_video']) ?>">
      <small class="ajuda">Use o link de "incorporar" do YouTube (formato .../embed/ID), não o link normal do vídeo.</small>
    </div>
    <div class="campo campo-largo">
      <label for="descricao">Descrição</label>
      <input type="text" id="descricao" name="descricao" maxlength="300" value="<?= htmlspecialchars($video['descricao'] ?? '') ?>">
      <div class="campo-checkbox">
        <input type="checkbox" id="status" name="status" <?= $video['status'] ? 'checked' : '' ?>>
        <label for="status">Ativo (visível no portal)</label>
      </div>
    </div>
  </div>
  <div class="form-acoes">
    <button type="submit" class="btn btn-primario" style="width:auto;"><i class="fa-solid fa-floppy-disk"></i> Salvar</button>
    <a href="videos-sessoes.php" class="btn btn-secundario">Cancelar</a>
  </div>
</form>
<?php include __DIR__ . '/includes/footer.php'; ?>
