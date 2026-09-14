<?php
require_once __DIR__ . '/includes/auth.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$editando = $id !== null;
$tituloPagina = $editando ? 'Editar documento' : 'Novo documento';

$categorias = $pdo->query('SELECT id_categoria, descricao FROM categorias_documentos ORDER BY id_categoria')->fetchAll();
$vereadores = $pdo->query(
    "SELECT v.id_vereador, s.nome_completo
     FROM vereadores v
     JOIN servidores s ON s.id_servidor = v.id_servidor
     WHERE v.ativo = 1
     ORDER BY s.nome_completo"
)->fetchAll();

$documento = [
    'id_categoria'      => $_GET['categoria_id'] ?? '',
    'numero_documento'  => '',
    'titulo'            => '',
    'descricao'         => '',
    'data_publicacao'   => date('Y-m-d'),
    'id_autor_vereador' => '',
    'arquivo'           => '',
    'status'            => 1,
];

// se veio de "documentos.php?categoria=slug", pré-seleciona a categoria no formulário
if (!$editando && !empty($_GET['categoria'])) {
    $stmt = $pdo->prepare('SELECT id_categoria FROM categorias_documentos WHERE slug = :slug');
    $stmt->execute(['slug' => $_GET['categoria']]);
    $achado = $stmt->fetchColumn();
    if ($achado) {
        $documento['id_categoria'] = $achado;
    }
}

$erros = [];

if ($editando) {
    $stmt = $pdo->prepare('SELECT * FROM documentos WHERE id_documento = :id');
    $stmt->execute(['id' => $id]);
    $registro = $stmt->fetch();
    if (!$registro) {
        header('Location: documentos.php');
        exit;
    }
    $documento = $registro;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $documento['id_categoria']      = $_POST['id_categoria'] ?? '';
    $documento['numero_documento']  = trim($_POST['numero_documento'] ?? '');
    $documento['titulo']            = trim($_POST['titulo'] ?? '');
    $documento['descricao']         = trim($_POST['descricao'] ?? '');
    $documento['data_publicacao']   = $_POST['data_publicacao'] ?? '';
    $documento['id_autor_vereador'] = $_POST['id_autor_vereador'] ?: null;
    $documento['status']            = isset($_POST['status']) ? 1 : 0;

    if ($documento['id_categoria'] === '') {
        $erros[] = 'Selecione a categoria do documento.';
    }
    if ($documento['titulo'] === '' && $documento['numero_documento'] === '') {
        $erros[] = 'Informe pelo menos o título ou o número do documento.';
    }

    // --- Upload de PDF (opcional) ---
    $novoArquivo = null;
    if (!empty($_FILES['arquivo']['name'])) {
        $extensao = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));
        $tamanhoMaximo = 10 * 1024 * 1024; // 10MB

        if ($_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
            $erros[] = 'Falha ao enviar o arquivo. Tente novamente.';
        } elseif ($extensao !== 'pdf') {
            $erros[] = 'O anexo precisa ser um arquivo PDF.';
        } elseif ($_FILES['arquivo']['size'] > $tamanhoMaximo) {
            $erros[] = 'O arquivo deve ter no máximo 10MB.';
        } else {
            $novoArquivo = 'documento_' . uniqid() . '.pdf';
            $pastaDestino = __DIR__ . '/assets/uploads/documentos/';
            if (!is_dir($pastaDestino)) {
                mkdir($pastaDestino, 0755, true);
            }
            if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], $pastaDestino . $novoArquivo)) {
                $erros[] = 'Não foi possível salvar o arquivo no servidor.';
                $novoArquivo = null;
            }
        }
    }

    if (empty($erros)) {
        if ($novoArquivo) {
            if ($editando && !empty($documento['arquivo'])) {
                @unlink(__DIR__ . '/assets/uploads/documentos/' . $documento['arquivo']);
            }
            $documento['arquivo'] = $novoArquivo;
        }

        $parametros = [
            'id_categoria'      => $documento['id_categoria'],
            'numero_documento'  => $documento['numero_documento'] ?: null,
            'titulo'            => $documento['titulo'] ?: null,
            'descricao'         => $documento['descricao'] ?: null,
            'data_publicacao'   => $documento['data_publicacao'] ?: null,
            'id_autor_vereador' => $documento['id_autor_vereador'],
            'arquivo'           => $documento['arquivo'] ?: null,
            'status'            => $documento['status'],
        ];

        if ($editando) {
            $parametros['id'] = $id;
            $pdo->prepare(
                'UPDATE documentos SET id_categoria=:id_categoria, numero_documento=:numero_documento,
                    titulo=:titulo, descricao=:descricao, data_publicacao=:data_publicacao,
                    id_autor_vereador=:id_autor_vereador, arquivo=:arquivo, status=:status
                 WHERE id_documento=:id'
            )->execute($parametros);
        } else {
            $pdo->prepare(
                'INSERT INTO documentos (id_categoria, numero_documento, titulo, descricao, data_publicacao,
                    id_autor_vereador, arquivo, status)
                 VALUES (:id_categoria, :numero_documento, :titulo, :descricao, :data_publicacao,
                    :id_autor_vereador, :arquivo, :status)'
            )->execute($parametros);
        }
        header('Location: documentos.php?ok=1');
        exit;
    }
}

include __DIR__ . '/includes/header.php';
?>

<h1 class="pagina__titulo"><?= $editando ? 'Editar documento' : 'Novo documento' ?></h1>
<p class="pagina__subtitulo"><a href="documentos.php">← Voltar para a lista</a></p>

<?php if (!empty($erros)): ?>
  <div class="alerta alerta-erro" style="max-width:none;">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <?= implode('<br>', array_map('htmlspecialchars', $erros)) ?>
  </div>
<?php endif; ?>

<form class="painel" method="post" enctype="multipart/form-data">
  <div class="form-grid">
    <div class="campo">
      <label for="id_categoria">Categoria *</label>
      <select id="id_categoria" name="id_categoria" required>
        <option value="">Selecione...</option>
        <?php foreach ($categorias as $c): ?>
          <option value="<?= $c['id_categoria'] ?>" <?= (string) $documento['id_categoria'] === (string) $c['id_categoria'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['descricao']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="campo">
      <label for="numero_documento">Número</label>
      <input type="text" id="numero_documento" name="numero_documento" maxlength="20" placeholder="ex: 018/2025" value="<?= htmlspecialchars($documento['numero_documento'] ?? '') ?>">
    </div>

    <div class="campo campo-largo">
      <label for="titulo">Título</label>
      <input type="text" id="titulo" name="titulo" maxlength="250" value="<?= htmlspecialchars($documento['titulo'] ?? '') ?>">
    </div>

    <div class="campo campo-largo">
      <label for="descricao">Descrição / Ementa</label>
      <textarea id="descricao" name="descricao" rows="6"><?= htmlspecialchars($documento['descricao'] ?? '') ?></textarea>
    </div>

    <div class="campo">
      <label for="data_publicacao">Data de publicação</label>
      <input type="date" id="data_publicacao" name="data_publicacao" value="<?= htmlspecialchars($documento['data_publicacao'] ?? '') ?>">
    </div>

    <div class="campo">
      <label for="id_autor_vereador">Autoria (quando aplicável)</label>
      <select id="id_autor_vereador" name="id_autor_vereador">
        <option value="">Não se aplica</option>
        <?php foreach ($vereadores as $v): ?>
          <option value="<?= $v['id_vereador'] ?>" <?= (string) ($documento['id_autor_vereador'] ?? '') === (string) $v['id_vereador'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($v['nome_completo']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <?php if (empty($vereadores)): ?>
        <small class="ajuda">Nenhum vereador cadastrado ainda.</small>
      <?php endif; ?>
    </div>

    <div class="campo campo-largo">
      <label for="arquivo">Arquivo (PDF)</label>
      <?php if (!empty($documento['arquivo'])): ?>
        <p style="margin:0 0 .5rem;">
          <a href="assets/uploads/documentos/<?= htmlspecialchars($documento['arquivo']) ?>" target="_blank">
            <i class="fa-solid fa-file-pdf"></i> Ver arquivo atual
          </a>
        </p>
      <?php endif; ?>
      <input type="file" id="arquivo" name="arquivo" accept=".pdf">
      <small class="ajuda">Somente PDF, até 10MB. <?= $editando ? 'Envie um novo arquivo apenas se quiser substituir o atual.' : '' ?></small>
    </div>

    <div class="campo campo-largo">
      <div class="campo-checkbox">
        <input type="checkbox" id="status" name="status" <?= $documento['status'] ? 'checked' : '' ?>>
        <label for="status">Publicado (visível no portal)</label>
      </div>
    </div>
  </div>

  <div class="form-acoes">
    <button type="submit" class="btn btn-primario" style="width:auto;">
      <i class="fa-solid fa-floppy-disk"></i> Salvar documento
    </button>
    <a href="documentos.php" class="btn btn-secundario">Cancelar</a>
  </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
