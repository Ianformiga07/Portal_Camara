<?php
$page_title = 'Consultar Manifestação — Câmara Municipal de Ananás';
include 'includes/header.php';

$tituloPaginaInterna = 'Consultar Manifestação';
$subtituloPaginaInterna = 'Acompanhe o andamento da sua manifestação enviada à Ouvidoria.';

$protocolo = trim($_GET['protocolo'] ?? '');
$manifestacao = null;
$naoEncontrado = false;

if ($protocolo !== '' && $pdo) {
    $stmt = $pdo->prepare(
        "SELECT m.*, t.descricao AS tipo_descricao
         FROM manifestacoes m
         LEFT JOIN tipos_manifestacao t ON t.id_tipo_manifestacao = m.id_tipo_manifestacao
         WHERE m.protocolo = :p"
    );
    $stmt->execute(['p' => $protocolo]);
    $manifestacao = $stmt->fetch();
    $naoEncontrado = !$manifestacao;
}

include 'includes/pagina-header.php';
?>

<section class="pagina-interna-conteudo">
  <div class="section-container">

    <form class="form-consulta" method="get">
      <input type="text" name="protocolo" placeholder="Digite seu número de protocolo (ex: OUV20260830-4821)" value="<?= htmlspecialchars($protocolo) ?>" required>
      <button type="submit" class="btn-enviar-form"><i class="fas fa-magnifying-glass"></i> Consultar</button>
    </form>

    <?php if ($naoEncontrado): ?>
      <div class="alerta-portal erro"><i class="fas fa-triangle-exclamation"></i> Protocolo não encontrado. Confira o número e tente novamente.</div>
    <?php endif; ?>

    <?php if ($manifestacao): ?>
      <div class="ficha-card">
        <h3><i class="fas fa-comments"></i> Protocolo <?= htmlspecialchars($manifestacao['protocolo']) ?></h3>
        <dl class="ficha-grid">
          <div><dt>Tipo</dt><dd><?= htmlspecialchars($manifestacao['tipo_descricao'] ?: '—') ?></dd></div>
          <div><dt>Recebida em</dt><dd><?= date('d/m/Y H:i', strtotime($manifestacao['criado_em'])) ?></dd></div>
          <div><dt>Situação</dt><dd><?= $manifestacao['respondida'] ? 'Respondida' : 'Em análise' ?></dd></div>
        </dl>

        <?php if ($manifestacao['respondida']): ?>
          <div class="ficha-objeto">
            <strong>Resposta da Câmara</strong> (<?= date('d/m/Y H:i', strtotime($manifestacao['respondido_em'])) ?>):<br>
            <?= nl2br(htmlspecialchars($manifestacao['resposta'])) ?>
          </div>
        <?php else: ?>
          <div class="alerta-portal aviso" style="margin-top:1.25rem;">
            <i class="fas fa-clock"></i> Sua manifestação ainda está em análise pela equipe da Câmara.
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php include 'includes/footer.php'; ?>
