<?php
require_once __DIR__ . '/includes/auth.php';

$id = (int) ($_GET['id'] ?? 0);
$tituloPagina = 'Responder manifestação';

$stmt = $pdo->prepare(
    'SELECT m.*, t.descricao AS tipo_descricao
     FROM manifestacoes m
     LEFT JOIN tipos_manifestacao t ON t.id_tipo_manifestacao = m.id_tipo_manifestacao
     WHERE m.id_manifestacao = :id'
);
$stmt->execute(['id' => $id]);
$manifestacao = $stmt->fetch();

if (!$manifestacao) {
    header('Location: manifestacoes.php');
    exit;
}

$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resposta = trim($_POST['resposta'] ?? '');

    if ($resposta === '') {
        $erros[] = 'Escreva a resposta antes de salvar.';
    } else {
        $pdo->prepare(
            'UPDATE manifestacoes SET resposta=:resposta, respondida=1, respondido_por=:respondido_por, respondido_em=NOW()
             WHERE id_manifestacao=:id'
        )->execute([
            'resposta'       => $resposta,
            'respondido_por' => $usuario['id_servidor'],
            'id'             => $id,
        ]);
        header('Location: manifestacoes.php?ok=1');
        exit;
    }
}

include __DIR__ . '/includes/header.php';
?>

<h1 class="pagina__titulo">Manifestação <?= htmlspecialchars($manifestacao['protocolo']) ?></h1>
<p class="pagina__subtitulo"><a href="manifestacoes.php">← Voltar para a lista</a></p>

<?php if (!empty($erros)): ?>
  <div class="alerta alerta-erro" style="max-width:none;">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <?= implode('<br>', array_map('htmlspecialchars', $erros)) ?>
  </div>
<?php endif; ?>

<div class="painel" style="margin-bottom:1.25rem;">
  <h2 class="painel__titulo"><i class="fa-solid fa-user"></i> Dados do cidadão</h2>
  <div class="form-grid">
    <div class="campo">
      <label>Nome</label>
      <p style="margin:0; font-size:.9rem;"><?= $manifestacao['anonimo'] ? '<em>Manifestação anônima</em>' : htmlspecialchars($manifestacao['nome'] ?: 'Não informado') ?></p>
    </div>
    <div class="campo">
      <label>Tipo</label>
      <p style="margin:0; font-size:.9rem;"><?= htmlspecialchars($manifestacao['tipo_descricao'] ?: '—') ?></p>
    </div>
    <div class="campo">
      <label>E-mail</label>
      <p style="margin:0; font-size:.9rem;"><?= htmlspecialchars($manifestacao['email'] ?: 'Não informado') ?></p>
    </div>
    <div class="campo">
      <label>Telefone</label>
      <p style="margin:0; font-size:.9rem;"><?= htmlspecialchars($manifestacao['telefone'] ?: 'Não informado') ?></p>
    </div>
    <div class="campo">
      <label>Recebida em</label>
      <p style="margin:0; font-size:.9rem;"><?= date('d/m/Y H:i', strtotime($manifestacao['criado_em'])) ?> via <?= htmlspecialchars($manifestacao['forma_recebimento'] ?: 'portal') ?></p>
    </div>
    <?php if ($manifestacao['anexo']): ?>
      <div class="campo">
        <label>Anexo</label>
        <p style="margin:0; font-size:.9rem;">
          <a href="assets/uploads/manifestacoes/<?= htmlspecialchars($manifestacao['anexo']) ?>" target="_blank"><i class="fa-solid fa-paperclip"></i> Ver arquivo</a>
        </p>
      </div>
    <?php endif; ?>
    <div class="campo campo-largo">
      <label>Mensagem</label>
      <p style="margin:0; font-size:.9rem; white-space:pre-line; background:var(--cinza-claro); padding:.75rem; border-radius:var(--radius-sm);"><?= htmlspecialchars($manifestacao['texto']) ?></p>
    </div>
  </div>
</div>

<form class="painel" method="post">
  <h2 class="painel__titulo"><i class="fa-solid fa-reply"></i> Resposta</h2>

  <?php if ($manifestacao['respondida']): ?>
    <p style="color:var(--texto-claro); font-size:.8rem; margin-bottom:.75rem;">
      Já respondida em <?= date('d/m/Y H:i', strtotime($manifestacao['respondido_em'])) ?>. Salvar novamente substitui a resposta anterior.
    </p>
  <?php endif; ?>

  <div class="campo campo-largo">
    <label for="resposta">Resposta ao cidadão *</label>
    <textarea id="resposta" name="resposta" rows="8" required><?= htmlspecialchars($manifestacao['resposta'] ?? '') ?></textarea>
  </div>

  <div class="form-acoes">
    <button type="submit" class="btn btn-primario" style="width:auto;">
      <i class="fa-solid fa-paper-plane"></i> Salvar resposta
    </button>
    <a href="manifestacoes.php" class="btn btn-secundario">Cancelar</a>
  </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
