<?php
$page_title = 'Consultar e-SIC — Câmara Municipal de Ananás';
include 'includes/header.php';

$tituloPaginaInterna = 'Consultar Solicitação e-SIC';
$subtituloPaginaInterna = 'Acompanhe o andamento da sua solicitação de acesso à informação.';

$protocolo = trim($_GET['protocolo'] ?? '');
$solicitacao = null;
$naoEncontrado = false;

if ($protocolo !== '' && $pdo) {
    $stmt = $pdo->prepare(
        "SELECT e.*, t.descricao AS tipo_descricao
         FROM esic_solicitacoes e
         LEFT JOIN tipos_esic t ON t.id_tipo_esic = e.id_tipo_esic
         WHERE e.protocolo = :p"
    );
    $stmt->execute(['p' => $protocolo]);
    $solicitacao = $stmt->fetch();
    $naoEncontrado = !$solicitacao;
}

include 'includes/pagina-header.php';
?>

<section class="pagina-interna-conteudo">
  <div class="section-container">

    <form class="form-consulta" method="get">
      <input type="text" name="protocolo" placeholder="Digite seu número de protocolo (ex: SIC20260830-4821)" value="<?= htmlspecialchars($protocolo) ?>" required>
      <button type="submit" class="btn-enviar-form"><i class="fas fa-magnifying-glass"></i> Consultar</button>
    </form>

    <?php if ($naoEncontrado): ?>
      <div class="alerta-portal erro"><i class="fas fa-triangle-exclamation"></i> Protocolo não encontrado. Confira o número e tente novamente.</div>
    <?php endif; ?>

    <?php if ($solicitacao): ?>
      <?php
        $diasEmAberto = (int) floor((time() - strtotime($solicitacao['criado_em'])) / 86400);
        $prazoLimite = date('d/m/Y', strtotime($solicitacao['criado_em'] . ' +20 days'));
      ?>
      <div class="ficha-card">
        <h3><i class="fas fa-scale-balanced"></i> Protocolo <?= htmlspecialchars($solicitacao['protocolo']) ?></h3>
        <dl class="ficha-grid">
          <div><dt>Tipo</dt><dd><?= htmlspecialchars($solicitacao['tipo_descricao'] ?: '—') ?></dd></div>
          <div><dt>Recebida em</dt><dd><?= date('d/m/Y H:i', strtotime($solicitacao['criado_em'])) ?></dd></div>
          <div><dt>Situação</dt><dd><?= $solicitacao['respondida'] ? 'Respondida' : 'Em análise' ?></dd></div>
        </dl>

        <?php if ($solicitacao['respondida']): ?>
          <div class="ficha-objeto">
            <strong>Resposta da Câmara</strong> (<?= date('d/m/Y H:i', strtotime($solicitacao['respondido_em'])) ?>):<br>
            <?= nl2br(htmlspecialchars($solicitacao['resposta'])) ?>
          </div>
        <?php else: ?>
          <div class="alerta-portal <?= $diasEmAberto >= 18 ? 'erro' : 'aviso' ?>" style="margin-top:1.25rem;">
            <i class="fas fa-clock"></i> Solicitação em análise. Prazo legal de resposta: até <strong><?= $prazoLimite ?></strong> (<?= $diasEmAberto ?> dia(s) em aberto).
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php include 'includes/footer.php'; ?>
