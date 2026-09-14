<?php
require_once __DIR__ . '/includes/auth.php';

$id = (int) ($_GET['id'] ?? 0);
$tituloPagina = 'Responder e-SIC';

$stmt = $pdo->prepare(
    'SELECT e.*, t.descricao AS tipo_descricao
     FROM esic_solicitacoes e
     LEFT JOIN tipos_esic t ON t.id_tipo_esic = e.id_tipo_esic
     WHERE e.id_esic = :id'
);
$stmt->execute(['id' => $id]);
$solicitacao = $stmt->fetch();

if (!$solicitacao) {
    header('Location: esic.php');
    exit;
}

$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resposta = trim($_POST['resposta'] ?? '');

    if ($resposta === '') {
        $erros[] = 'Escreva a resposta antes de salvar.';
    } else {
        $pdo->prepare(
            'UPDATE esic_solicitacoes SET resposta=:resposta, respondida=1, respondido_por=:respondido_por, respondido_em=NOW()
             WHERE id_esic=:id'
        )->execute([
            'resposta'       => $resposta,
            'respondido_por' => $usuario['id_servidor'],
            'id'             => $id,
        ]);
        header('Location: esic.php?ok=1');
        exit;
    }
}

$diasEmAberto = (int) floor((time() - strtotime($solicitacao['criado_em'])) / 86400);
$prazoLimite = date('d/m/Y', strtotime($solicitacao['criado_em'] . ' +20 days'));

include __DIR__ . '/includes/header.php';
?>

<h1 class="pagina__titulo">Solicitação <?= htmlspecialchars($solicitacao['protocolo']) ?></h1>
<p class="pagina__subtitulo"><a href="esic.php">← Voltar para a lista</a></p>

<?php if (!empty($erros)): ?>
  <div class="alerta alerta-erro" style="max-width:none;">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <?= implode('<br>', array_map('htmlspecialchars', $erros)) ?>
  </div>
<?php endif; ?>

<?php if (!$solicitacao['respondida']): ?>
  <div class="alerta <?= $diasEmAberto >= 18 ? 'alerta-erro' : 'alerta-sucesso' ?>" style="max-width:none;">
    <i class="fa-solid fa-clock"></i>
    Prazo legal de resposta (Lei nº 12.527/2011): até <strong><?= $prazoLimite ?></strong> (<?= $diasEmAberto ?> dia(s) em aberto).
  </div>
<?php endif; ?>

<div class="painel" style="margin-bottom:1.25rem;">
  <h2 class="painel__titulo"><i class="fa-solid fa-user"></i> Dados do solicitante</h2>
  <div class="form-grid">
    <div class="campo">
      <label>Nome</label>
      <p style="margin:0; font-size:.9rem;"><?= $solicitacao['anonimo'] ? '<em>Solicitação anônima</em>' : htmlspecialchars($solicitacao['nome'] ?: 'Não informado') ?></p>
    </div>
    <div class="campo">
      <label>Tipo de solicitação</label>
      <p style="margin:0; font-size:.9rem;"><?= htmlspecialchars($solicitacao['tipo_descricao'] ?: '—') ?></p>
    </div>
    <div class="campo">
      <label>E-mail</label>
      <p style="margin:0; font-size:.9rem;"><?= htmlspecialchars($solicitacao['email'] ?: 'Não informado') ?></p>
    </div>
    <div class="campo">
      <label>Telefone</label>
      <p style="margin:0; font-size:.9rem;"><?= htmlspecialchars($solicitacao['telefone'] ?: 'Não informado') ?></p>
    </div>
    <div class="campo">
      <label>Forma de recebimento da resposta</label>
      <p style="margin:0; font-size:.9rem;"><?= htmlspecialchars($solicitacao['forma_recebimento'] ?: 'Não informado') ?></p>
    </div>
    <div class="campo">
      <label>Recebida em</label>
      <p style="margin:0; font-size:.9rem;"><?= date('d/m/Y H:i', strtotime($solicitacao['criado_em'])) ?></p>
    </div>
    <?php if ($solicitacao['anexo']): ?>
      <div class="campo">
        <label>Anexo</label>
        <p style="margin:0; font-size:.9rem;">
          <a href="assets/uploads/esic/<?= htmlspecialchars($solicitacao['anexo']) ?>" target="_blank"><i class="fa-solid fa-paperclip"></i> Ver arquivo</a>
        </p>
      </div>
    <?php endif; ?>
    <div class="campo campo-largo">
      <label>Descrição do pedido</label>
      <p style="margin:0; font-size:.9rem; white-space:pre-line; background:var(--cinza-claro); padding:.75rem; border-radius:var(--radius-sm);"><?= htmlspecialchars($solicitacao['descricao']) ?></p>
    </div>
  </div>
</div>

<form class="painel" method="post">
  <h2 class="painel__titulo"><i class="fa-solid fa-reply"></i> Resposta</h2>

  <?php if ($solicitacao['respondida']): ?>
    <p style="color:var(--texto-claro); font-size:.8rem; margin-bottom:.75rem;">
      Já respondida em <?= date('d/m/Y H:i', strtotime($solicitacao['respondido_em'])) ?>. Salvar novamente substitui a resposta anterior.
    </p>
  <?php endif; ?>

  <div class="campo campo-largo">
    <label for="resposta">Resposta ao solicitante *</label>
    <textarea id="resposta" name="resposta" rows="8" required><?= htmlspecialchars($solicitacao['resposta'] ?? '') ?></textarea>
  </div>

  <div class="form-acoes">
    <button type="submit" class="btn btn-primario" style="width:auto;">
      <i class="fa-solid fa-paper-plane"></i> Salvar resposta
    </button>
    <a href="esic.php" class="btn btn-secundario">Cancelar</a>
  </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
