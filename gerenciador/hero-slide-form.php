<?php
require_once __DIR__ . '/includes/auth.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$editando = $id !== null;
$tituloPagina = $editando ? 'Editar slide' : 'Novo slide';

$slide = [
    'titulo'      => '',
    'texto'       => '',
    'imagem'      => '',
    'texto_botao' => '',
    'link_botao'  => '',
    'icone_botao' => 'fa-arrow-right',
    'ordem'       => 0,
    'status'      => 1,
];
$erros = [];

if ($editando) {
    $stmt = $pdo->prepare('SELECT * FROM hero_slides WHERE id_slide = :id');
    $stmt->execute(['id' => $id]);
    $registro = $stmt->fetch();
    if (!$registro) {
        header('Location: hero-slides.php');
        exit;
    }
    $slide = $registro;
} else {
    // sugere a próxima ordem disponível
    $slide['ordem'] = (int) $pdo->query('SELECT COALESCE(MAX(ordem), 0) + 1 FROM hero_slides')->fetchColumn();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slide['titulo']      = trim($_POST['titulo'] ?? '');
    $slide['texto']       = trim($_POST['texto'] ?? '');
    $slide['texto_botao'] = trim($_POST['texto_botao'] ?? '');
    $slide['link_botao']  = trim($_POST['link_botao'] ?? '');
    $slide['icone_botao'] = trim($_POST['icone_botao'] ?? '') ?: 'fa-arrow-right';
    $slide['ordem']       = (int) ($_POST['ordem'] ?? 0);
    $slide['status']      = isset($_POST['status']) ? 1 : 0;

    if ($slide['titulo'] === '') {
        $erros[] = 'O título é obrigatório.';
    }
    if (!$editando && empty($_FILES['imagem']['name'])) {
        $erros[] = 'A imagem do slide é obrigatória.';
    }
    if ($slide['link_botao'] !== '' && $slide['texto_botao'] === '') {
        $erros[] = 'Se informar o link do botão, informe também o texto do botão.';
    }

    // --- Upload de imagem ---
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
            $novoArquivo = 'hero_' . uniqid() . '.' . $extensao;
            $pastaDestino = __DIR__ . '/assets/uploads/hero/';
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
            if ($editando && !empty($slide['imagem'])) {
                @unlink(__DIR__ . '/assets/uploads/hero/' . $slide['imagem']);
            }
            $slide['imagem'] = $novoArquivo;
        }

        if ($editando) {
            $stmt = $pdo->prepare(
                'UPDATE hero_slides SET titulo=:titulo, texto=:texto, imagem=:imagem,
                    texto_botao=:texto_botao, link_botao=:link_botao, icone_botao=:icone_botao,
                    ordem=:ordem, status=:status
                 WHERE id_slide=:id'
            );
            $stmt->execute([
                'titulo'      => $slide['titulo'],
                'texto'       => $slide['texto'],
                'imagem'      => $slide['imagem'],
                'texto_botao' => $slide['texto_botao'] ?: null,
                'link_botao'  => $slide['link_botao'] ?: null,
                'icone_botao' => $slide['icone_botao'],
                'ordem'       => $slide['ordem'],
                'status'      => $slide['status'],
                'id'          => $id,
            ]);
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO hero_slides (titulo, texto, imagem, texto_botao, link_botao, icone_botao, ordem, status)
                 VALUES (:titulo, :texto, :imagem, :texto_botao, :link_botao, :icone_botao, :ordem, :status)'
            );
            $stmt->execute([
                'titulo'      => $slide['titulo'],
                'texto'       => $slide['texto'],
                'imagem'      => $slide['imagem'],
                'texto_botao' => $slide['texto_botao'] ?: null,
                'link_botao'  => $slide['link_botao'] ?: null,
                'icone_botao' => $slide['icone_botao'],
                'ordem'       => $slide['ordem'],
                'status'      => $slide['status'],
            ]);
        }
        header('Location: hero-slides.php?ok=1');
        exit;
    }
}

include __DIR__ . '/includes/header.php';
?>

<h1 class="pagina__titulo"><?= $editando ? 'Editar slide' : 'Novo slide' ?></h1>
<p class="pagina__subtitulo"><a href="hero-slides.php">← Voltar para a lista</a></p>

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
      <input type="text" id="titulo" name="titulo" maxlength="150" required value="<?= htmlspecialchars($slide['titulo']) ?>">
    </div>

    <div class="campo campo-largo">
      <label for="texto">Texto de apoio</label>
      <input type="text" id="texto" name="texto" maxlength="300" value="<?= htmlspecialchars($slide['texto']) ?>">
      <small class="ajuda">Frase curta exibida abaixo do título, dentro do slide.</small>
    </div>

    <div class="campo">
      <label for="imagem">Imagem do slide <?= $editando ? '' : '*' ?></label>
      <div class="upload-preview" id="preview">
        <?php if (!empty($slide['imagem'])): ?>
          <img src="assets/uploads/hero/<?= htmlspecialchars($slide['imagem']) ?>" alt="">
        <?php else: ?>
          <span>Nenhuma imagem selecionada</span>
        <?php endif; ?>
      </div>
      <input type="file" id="imagem" name="imagem" accept=".jpg,.jpeg,.png,.webp">
      <small class="ajuda">JPG, PNG ou WEBP, até 4MB. Recomendado: 1920x800px (formato panorâmico).</small>
    </div>

    <div class="campo">
      <label for="ordem">Ordem de exibição</label>
      <input type="number" id="ordem" name="ordem" min="0" value="<?= (int) $slide['ordem'] ?>">
      <small class="ajuda">Menor número aparece primeiro no carrossel.</small>

      <div class="campo-checkbox">
        <input type="checkbox" id="status" name="status" <?= $slide['status'] ? 'checked' : '' ?>>
        <label for="status">Ativo (visível no portal)</label>
      </div>
    </div>

    <div class="campo">
      <label for="texto_botao">Texto do botão</label>
      <input type="text" id="texto_botao" name="texto_botao" maxlength="60" placeholder="ex: Ver Sessões ao Vivo" value="<?= htmlspecialchars($slide['texto_botao']) ?>">
    </div>

    <div class="campo">
      <label for="link_botao">Link do botão</label>
      <input type="text" id="link_botao" name="link_botao" maxlength="255" placeholder="ex: sessoes.php" value="<?= htmlspecialchars($slide['link_botao']) ?>">
    </div>

    <div class="campo">
      <label for="icone_botao">Ícone do botão (Font Awesome)</label>
      <input type="text" id="icone_botao" name="icone_botao" maxlength="40" placeholder="ex: fa-play-circle" value="<?= htmlspecialchars($slide['icone_botao']) ?>">
      <small class="ajuda">Nome da classe do ícone, sem o prefixo "fas". Deixe em branco para usar o padrão.</small>
    </div>
  </div>

  <div class="form-acoes">
    <button type="submit" class="btn btn-primario" style="width:auto;">
      <i class="fa-solid fa-floppy-disk"></i> Salvar slide
    </button>
    <a href="hero-slides.php" class="btn btn-secundario">Cancelar</a>
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
