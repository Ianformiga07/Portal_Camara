<?php
require_once __DIR__ . '/includes/auth.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$editando = $id !== null;
$tituloPagina = $editando ? 'Editar notícia' : 'Nova notícia';

$noticia = [
    'titulo'    => '',
    'subtitulo' => '',
    'conteudo'  => '',
    'imagem'    => '',
    'autor'     => $usuario['nome_completo'],
    'destaque'  => 0,
    'status'    => 1,
];
$erros = [];

if ($editando) {
    $stmt = $pdo->prepare('SELECT * FROM noticias WHERE id_noticia = :id');
    $stmt->execute(['id' => $id]);
    $registro = $stmt->fetch();
    if (!$registro) {
        header('Location: noticias.php');
        exit;
    }
    $noticia = $registro;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $noticia['titulo']    = trim($_POST['titulo'] ?? '');
    $noticia['subtitulo'] = trim($_POST['subtitulo'] ?? '');
    $noticia['conteudo']  = trim($_POST['conteudo'] ?? '');
    $noticia['autor']     = trim($_POST['autor'] ?? '');
    $noticia['destaque']  = isset($_POST['destaque']) ? 1 : 0;
    $noticia['status']    = isset($_POST['status']) ? 1 : 0;

    if ($noticia['titulo'] === '') {
        $erros[] = 'O título é obrigatório.';
    }
    if ($noticia['conteudo'] === '') {
        $erros[] = 'O conteúdo da notícia é obrigatório.';
    }

    // --- Upload de imagem (opcional) ---
    $novoArquivo = null;
    if (!empty($_FILES['imagem']['name'])) {
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
        $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
        $tamanhoMaximo = 4 * 1024 * 1024; // 4MB

        if ($_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
            $erros[] = 'Falha ao enviar a imagem. Tente novamente.';
        } elseif (!in_array($extensao, $extensoesPermitidas, true)) {
            $erros[] = 'Formato de imagem inválido. Use JPG, PNG ou WEBP.';
        } elseif ($_FILES['imagem']['size'] > $tamanhoMaximo) {
            $erros[] = 'A imagem deve ter no máximo 4MB.';
        } else {
            $novoArquivo = 'noticia_' . uniqid() . '.' . $extensao;
            $pastaDestino = __DIR__ . '/assets/uploads/noticias/';
            if (!is_dir($pastaDestino)) {
                mkdir($pastaDestino, 0755, true);
            }
            if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $pastaDestino . $novoArquivo)) {
                $erros[] = 'Não foi possível salvar a imagem no servidor.';
                $novoArquivo = null;
            }
        }
    }

    if (empty($erros)) {
        if ($novoArquivo) {
            // remove a imagem antiga ao trocar
            if ($editando && !empty($noticia['imagem'])) {
                @unlink(__DIR__ . '/assets/uploads/noticias/' . $noticia['imagem']);
            }
            $noticia['imagem'] = $novoArquivo;
        }

        if ($editando) {
            $stmt = $pdo->prepare(
                'UPDATE noticias SET titulo=:titulo, subtitulo=:subtitulo, conteudo=:conteudo,
                    imagem=:imagem, autor=:autor, destaque=:destaque, status=:status
                 WHERE id_noticia=:id'
            );
            $stmt->execute([
                'titulo'    => $noticia['titulo'],
                'subtitulo' => $noticia['subtitulo'],
                'conteudo'  => $noticia['conteudo'],
                'imagem'    => $noticia['imagem'],
                'autor'     => $noticia['autor'],
                'destaque'  => $noticia['destaque'],
                'status'    => $noticia['status'],
                'id'        => $id,
            ]);
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO noticias (titulo, subtitulo, conteudo, imagem, autor, destaque, status, publicado_em)
                 VALUES (:titulo, :subtitulo, :conteudo, :imagem, :autor, :destaque, :status, NOW())'
            );
            $stmt->execute([
                'titulo'    => $noticia['titulo'],
                'subtitulo' => $noticia['subtitulo'],
                'conteudo'  => $noticia['conteudo'],
                'imagem'    => $noticia['imagem'] ?: null,
                'autor'     => $noticia['autor'],
                'destaque'  => $noticia['destaque'],
                'status'    => $noticia['status'],
            ]);
        }
        header('Location: noticias.php?ok=1');
        exit;
    }
}

include __DIR__ . '/includes/header.php';
?>

<h1 class="pagina__titulo"><?= $editando ? 'Editar notícia' : 'Nova notícia' ?></h1>
<p class="pagina__subtitulo"><a href="noticias.php">← Voltar para a lista</a></p>

<?php if (!empty($erros)): ?>
  <div class="alerta alerta-erro" style="max-width:none;">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <?= implode('<br>', array_map('htmlspecialchars', $erros)) ?>
  </div>
<?php endif; ?>

<form class="painel" method="post" enctype="multipart/form-data">
  <div class="form-grid">
    <div class="campo campo-largo">
      <label for="titulo">Título *</label>
      <input type="text" id="titulo" name="titulo" maxlength="255" required value="<?= htmlspecialchars($noticia['titulo']) ?>">
    </div>

    <div class="campo campo-largo">
      <label for="subtitulo">Subtítulo</label>
      <input type="text" id="subtitulo" name="subtitulo" maxlength="255" value="<?= htmlspecialchars($noticia['subtitulo']) ?>">
    </div>

    <div class="campo campo-largo">
      <label for="conteudo">Conteúdo *</label>
      <textarea id="conteudo" name="conteudo" rows="10" required><?= htmlspecialchars($noticia['conteudo']) ?></textarea>
      <small class="ajuda">Parágrafos são reconhecidos automaticamente ao publicar no portal.</small>
    </div>

    <div class="campo">
      <label for="imagem">Imagem de capa</label>
      <div class="upload-preview" id="preview">
        <?php if (!empty($noticia['imagem'])): ?>
          <img src="assets/uploads/noticias/<?= htmlspecialchars($noticia['imagem']) ?>" alt="">
        <?php else: ?>
          <span>Nenhuma imagem selecionada</span>
        <?php endif; ?>
      </div>
      <input type="file" id="imagem" name="imagem" accept=".jpg,.jpeg,.png,.webp">
      <small class="ajuda">JPG, PNG ou WEBP, até 4MB.</small>
    </div>

    <div class="campo">
      <label for="autor">Autor</label>
      <input type="text" id="autor" name="autor" maxlength="100" value="<?= htmlspecialchars($noticia['autor']) ?>">

      <div class="campo-checkbox">
        <input type="checkbox" id="destaque" name="destaque" <?= $noticia['destaque'] ? 'checked' : '' ?>>
        <label for="destaque">Exibir em destaque na página inicial</label>
      </div>

      <div class="campo-checkbox">
        <input type="checkbox" id="status" name="status" <?= $noticia['status'] ? 'checked' : '' ?>>
        <label for="status">Publicada (visível no portal)</label>
      </div>
    </div>
  </div>

  <div class="form-acoes">
    <button type="submit" class="btn btn-primario" style="width:auto;">
      <i class="fa-solid fa-floppy-disk"></i> Salvar notícia
    </button>
    <a href="noticias.php" class="btn btn-secundario">Cancelar</a>
  </div>
</form>

<script>
  document.getElementById('imagem').addEventListener('change', function (e) {
    var arquivo = e.target.files[0];
    if (!arquivo) return;
    var preview = document.getElementById('preview');
    var leitor = new FileReader();
    leitor.onload = function (ev) {
      preview.innerHTML = '<img src="' + ev.target.result + '" alt="">';
    };
    leitor.readAsDataURL(arquivo);
  });
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
