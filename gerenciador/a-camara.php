<?php
require_once __DIR__ . '/includes/auth.php';

$tituloPagina = 'A Câmara';

$meses = [
    'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
    'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro',
];

$dados = [
    'id_historia' => null, 'ano_fundacao' => '', 'dia_aniversario' => '', 'mes_aniversario' => '',
    'populacao' => '', 'area_km2' => '', 'conteudo' => '', 'imagem_cidade' => '', 'imagem_brasao' => '',
];

$registro = $pdo->query('SELECT * FROM historia_municipio ORDER BY id_historia LIMIT 1')->fetch();
if ($registro) {
    $dados = $registro;
}

$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados['ano_fundacao']     = trim($_POST['ano_fundacao'] ?? '');
    $dados['dia_aniversario']  = $_POST['dia_aniversario'] ?: null;
    $dados['mes_aniversario']  = $_POST['mes_aniversario'] ?: null;
    $dados['populacao']        = $_POST['populacao'] !== '' ? str_replace('.', '', str_replace(',', '.', $_POST['populacao'])) : null;
    $dados['area_km2']         = $_POST['area_km2'] !== '' ? str_replace(',', '.', $_POST['area_km2']) : null;
    $dados['conteudo']         = trim($_POST['conteudo'] ?? '');

    // --- Upload das duas imagens (cada uma opcional, substitui a anterior) ---
    foreach (['imagem_cidade' => 'cidade', 'imagem_brasao' => 'brasao'] as $campo => $prefixo) {
        if (!empty($_FILES[$campo]['name'])) {
            $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
            $extensao = strtolower(pathinfo($_FILES[$campo]['name'], PATHINFO_EXTENSION));
            $tamanhoMaximo = 4 * 1024 * 1024;

            if ($_FILES[$campo]['error'] !== UPLOAD_ERR_OK) {
                $erros[] = 'Falha ao enviar a imagem. Tente novamente.';
            } elseif (!in_array($extensao, $extensoesPermitidas, true)) {
                $erros[] = 'Formato de imagem inválido. Use JPG, PNG, WEBP ou SVG.';
            } elseif ($_FILES[$campo]['size'] > $tamanhoMaximo) {
                $erros[] = 'A imagem deve ter no máximo 4MB.';
            } else {
                $novoArquivo = $prefixo . '_' . uniqid() . '.' . $extensao;
                $pastaDestino = __DIR__ . '/assets/uploads/institucional/';
                if (!is_dir($pastaDestino)) {
                    mkdir($pastaDestino, 0755, true);
                }
                if (move_uploaded_file($_FILES[$campo]['tmp_name'], $pastaDestino . $novoArquivo)) {
                    if (!empty($dados[$campo])) {
                        @unlink(__DIR__ . '/assets/uploads/institucional/' . $dados[$campo]);
                    }
                    $dados[$campo] = $novoArquivo;
                } else {
                    $erros[] = 'Não foi possível salvar a imagem no servidor.';
                }
            }
        }
    }

    if (empty($erros)) {
        $parametros = [
            'ano_fundacao'    => $dados['ano_fundacao'] ?: null,
            'dia_aniversario' => $dados['dia_aniversario'],
            'mes_aniversario' => $dados['mes_aniversario'],
            'populacao'       => $dados['populacao'],
            'area_km2'        => $dados['area_km2'],
            'conteudo'        => $dados['conteudo'] ?: null,
            'imagem_cidade'   => $dados['imagem_cidade'] ?: null,
            'imagem_brasao'   => $dados['imagem_brasao'] ?: null,
        ];

        if ($dados['id_historia']) {
            $parametros['id'] = $dados['id_historia'];
            $pdo->prepare(
                'UPDATE historia_municipio SET ano_fundacao=:ano_fundacao, dia_aniversario=:dia_aniversario,
                    mes_aniversario=:mes_aniversario, populacao=:populacao, area_km2=:area_km2, conteudo=:conteudo,
                    imagem_cidade=:imagem_cidade, imagem_brasao=:imagem_brasao
                 WHERE id_historia=:id'
            )->execute($parametros);
        } else {
            $pdo->prepare(
                'INSERT INTO historia_municipio (ano_fundacao, dia_aniversario, mes_aniversario, populacao, area_km2,
                    conteudo, imagem_cidade, imagem_brasao)
                 VALUES (:ano_fundacao, :dia_aniversario, :mes_aniversario, :populacao, :area_km2, :conteudo,
                    :imagem_cidade, :imagem_brasao)'
            )->execute($parametros);
        }
        header('Location: a-camara.php?ok=1');
        exit;
    }
}

include __DIR__ . '/includes/header.php';
?>

<h1 class="pagina__titulo">A Câmara</h1>
<p class="pagina__subtitulo">Informações institucionais exibidas na página "A Câmara" do portal: história, brasão e dados do município.</p>

<?php if (!empty($_GET['ok'])): ?>
  <div class="alerta alerta-sucesso" style="max-width:none;">
    <i class="fa-solid fa-circle-check"></i> Informações salvas com sucesso.
  </div>
<?php endif; ?>
<?php if (!empty($erros)): ?>
  <div class="alerta alerta-erro" style="max-width:none;">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <?= implode('<br>', array_map('htmlspecialchars', $erros)) ?>
  </div>
<?php endif; ?>

<form class="painel" method="post" enctype="multipart/form-data">
  <h2 class="painel__titulo"><i class="fa-solid fa-image"></i> Identidade visual</h2>
  <div class="form-grid">
    <div class="campo">
      <label for="imagem_brasao">Brasão / logo da Câmara</label>
      <div class="upload-preview" id="preview-brasao" style="aspect-ratio:1/1; max-width:160px;">
        <?php if (!empty($dados['imagem_brasao'])): ?>
          <img src="assets/uploads/institucional/<?= htmlspecialchars($dados['imagem_brasao']) ?>" alt="">
        <?php else: ?>
          <span>Nenhuma logo enviada</span>
        <?php endif; ?>
      </div>
      <input type="file" id="imagem_brasao" name="imagem_brasao" accept=".jpg,.jpeg,.png,.webp,.svg">
      <small class="ajuda">Usada no cabeçalho do portal e do gerenciador. JPG, PNG, WEBP ou SVG, até 4MB.</small>
    </div>

    <div class="campo">
      <label for="imagem_cidade">Foto da cidade</label>
      <div class="upload-preview" id="preview-cidade">
        <?php if (!empty($dados['imagem_cidade'])): ?>
          <img src="assets/uploads/institucional/<?= htmlspecialchars($dados['imagem_cidade']) ?>" alt="">
        <?php else: ?>
          <span>Nenhuma imagem enviada</span>
        <?php endif; ?>
      </div>
      <input type="file" id="imagem_cidade" name="imagem_cidade" accept=".jpg,.jpeg,.png,.webp">
      <small class="ajuda">Exibida na página institucional. JPG, PNG ou WEBP, até 4MB.</small>
    </div>
  </div>

  <h2 class="painel__titulo" style="margin-top:1.75rem;"><i class="fa-solid fa-map-location-dot"></i> Dados do município</h2>
  <div class="form-grid">
    <div class="campo">
      <label for="ano_fundacao">Ano de fundação</label>
      <input type="text" id="ano_fundacao" name="ano_fundacao" maxlength="10" placeholder="ex: 1988" value="<?= htmlspecialchars($dados['ano_fundacao'] ?? '') ?>">
    </div>

    <div class="campo">
      <label for="dia_aniversario">Dia do aniversário da cidade</label>
      <input type="number" id="dia_aniversario" name="dia_aniversario" min="1" max="31" value="<?= htmlspecialchars($dados['dia_aniversario'] ?? '') ?>">
    </div>

    <div class="campo">
      <label for="mes_aniversario">Mês do aniversário</label>
      <select id="mes_aniversario" name="mes_aniversario">
        <option value="">Selecione...</option>
        <?php foreach ($meses as $m): ?>
          <option value="<?= $m ?>" <?= ($dados['mes_aniversario'] ?? '') === $m ? 'selected' : '' ?>><?= $m ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="campo">
      <label for="populacao">População estimada</label>
      <input type="text" id="populacao" name="populacao" placeholder="ex: 11500" value="<?= htmlspecialchars($dados['populacao'] ?? '') ?>">
    </div>

    <div class="campo">
      <label for="area_km2">Área (km²)</label>
      <input type="text" id="area_km2" name="area_km2" placeholder="ex: 735,4" value="<?= htmlspecialchars($dados['area_km2'] ?? '') ?>">
    </div>
  </div>

  <h2 class="painel__titulo" style="margin-top:1.75rem;"><i class="fa-solid fa-book-open"></i> História do município</h2>
  <div class="form-grid">
    <div class="campo campo-largo">
      <label for="conteudo">Texto da história</label>
      <textarea id="conteudo" name="conteudo" rows="12"><?= htmlspecialchars($dados['conteudo'] ?? '') ?></textarea>
      <small class="ajuda">Parágrafos são reconhecidos automaticamente ao publicar no portal.</small>
    </div>
  </div>

  <div class="form-acoes">
    <button type="submit" class="btn btn-primario" style="width:auto;">
      <i class="fa-solid fa-floppy-disk"></i> Salvar informações
    </button>
  </div>
</form>

<script>
  function configurarPreview(inputId, previewId) {
    document.getElementById(inputId).addEventListener('change', function (e) {
      var arquivo = e.target.files[0];
      if (!arquivo) return;
      var preview = document.getElementById(previewId);
      var leitor = new FileReader();
      leitor.onload = function (ev) {
        preview.innerHTML = '<img src="' + ev.target.result + '" alt="">';
      };
      leitor.readAsDataURL(arquivo);
    });
  }
  configurarPreview('imagem_brasao', 'preview-brasao');
  configurarPreview('imagem_cidade', 'preview-cidade');
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
