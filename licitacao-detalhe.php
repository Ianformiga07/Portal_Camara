<?php
require_once 'config/database.php';
require_once 'includes/funcoes.php';

$id = (int) ($_GET['id'] ?? 0);

if (!$pdo) {
    header('Location: licitacoes.php');
    exit;
}

$stmt = $pdo->prepare(
    "SELECT l.*, o.descricao AS orgao_descricao, proc.descricao AS procedimento_descricao,
            m.descricao AS modalidade_descricao, t.descricao AS tipo_descricao,
            fin.descricao AS finalidade_descricao, reg.descricao AS regime_descricao,
            sit.descricao AS situacao_descricao, sv.nome_completo AS pregoeiro_nome
     FROM licitacoes l
     LEFT JOIN orgaos o ON o.id_orgao = l.id_orgao
     LEFT JOIN licitacao_procedimentos proc ON proc.id_procedimento = l.id_procedimento
     LEFT JOIN licitacao_modalidades m ON m.id_modalidade = l.id_modalidade
     LEFT JOIN licitacao_tipos t ON t.id_tipo_licitacao = l.id_tipo_licitacao
     LEFT JOIN licitacao_finalidades fin ON fin.id_finalidade = l.id_finalidade
     LEFT JOIN licitacao_regimes_execucao reg ON reg.id_regime_execucao = l.id_regime_execucao
     LEFT JOIN licitacao_situacoes sit ON sit.id_situacao = l.id_situacao
     LEFT JOIN servidores sv ON sv.id_servidor = l.id_pregoeiro
     WHERE l.id_licitacao = :id AND l.status = 1"
);
$stmt->execute(['id' => $id]);
$licitacao = $stmt->fetch();

if (!$licitacao) {
    header('Location: licitacoes.php');
    exit;
}

$participantes = $pdo->prepare(
    "SELECT lp.vencedor, f.razao_social
     FROM licitacoes_participantes lp
     JOIN fornecedores f ON f.id_fornecedor = lp.id_fornecedor
     WHERE lp.id_licitacao = :id
     ORDER BY lp.vencedor DESC, f.razao_social"
);
$participantes->execute(['id' => $id]);
$participantes = $participantes->fetchAll();

$contratoRelacionado = $pdo->prepare(
    "SELECT id_contrato, numero_contrato, ano_exercicio FROM contratos WHERE id_licitacao = :id AND status = 1 LIMIT 1"
);
$contratoRelacionado->execute(['id' => $id]);
$contratoRelacionado = $contratoRelacionado->fetch();

$page_title = 'Licitação ' . ($licitacao['numero_processo'] ?: '#' . $id) . ' — Câmara Municipal de Ananás';
include 'includes/header.php';

$tituloPaginaInterna = 'Licitação ' . ($licitacao['numero_processo'] ?: '#' . $id);
$subtituloPaginaInterna = $licitacao['modalidade_descricao'] ?: '';
include 'includes/pagina-header.php';
?>

<section class="pagina-interna-conteudo">
  <div class="section-container">
    <p style="margin-bottom:1.5rem;"><a href="licitacoes.php"><i class="fas fa-arrow-left"></i> Voltar para todas as licitações</a></p>

    <div class="ficha-card">
      <h3><i class="fas fa-clipboard-list"></i> Dados do processo</h3>
      <dl class="ficha-grid">
        <div><dt>Número do processo</dt><dd><?= htmlspecialchars($licitacao['numero_processo'] ?: '—') ?></dd></div>
        <?php if ($licitacao['numero_licitacao']): ?>
          <div><dt>Número da licitação</dt><dd><?= htmlspecialchars($licitacao['numero_licitacao']) ?>/<?= htmlspecialchars($licitacao['ano_exercicio']) ?></dd></div>
        <?php endif; ?>
        <div><dt>Órgão</dt><dd><?= htmlspecialchars($licitacao['orgao_descricao'] ?: '—') ?></dd></div>
        <div><dt>Modalidade</dt><dd><?= htmlspecialchars($licitacao['modalidade_descricao'] ?: '—') ?></dd></div>
        <div><dt>Procedimento</dt><dd><?= htmlspecialchars($licitacao['procedimento_descricao'] ?: '—') ?></dd></div>
        <div><dt>Tipo</dt><dd><?= htmlspecialchars($licitacao['tipo_descricao'] ?: '—') ?></dd></div>
        <div><dt>Finalidade</dt><dd><?= htmlspecialchars($licitacao['finalidade_descricao'] ?: '—') ?></dd></div>
        <div><dt>Regime de execução</dt><dd><?= htmlspecialchars($licitacao['regime_descricao'] ?: '—') ?></dd></div>
        <div><dt>Situação</dt><dd><?= htmlspecialchars($licitacao['situacao_descricao'] ?: '—') ?></dd></div>
        <?php if ($licitacao['pregoeiro_nome']): ?>
          <div><dt>Pregoeiro(a)</dt><dd><?= htmlspecialchars($licitacao['pregoeiro_nome']) ?></dd></div>
        <?php endif; ?>
        <div><dt>Data de abertura</dt><dd><?= $licitacao['data_abertura'] ? date('d/m/Y', strtotime($licitacao['data_abertura'])) : '—' ?></dd></div>
        <div><dt>Data de homologação</dt><dd><?= $licitacao['data_homologacao'] ? date('d/m/Y', strtotime($licitacao['data_homologacao'])) : '—' ?></dd></div>
        <div><dt>Valor estimado</dt><dd><?= $licitacao['valor_estimado'] !== null ? 'R$ ' . number_format($licitacao['valor_estimado'], 2, ',', '.') : '—' ?></dd></div>
      </dl>

      <?php if ($licitacao['objeto']): ?>
        <div class="ficha-objeto">
          <strong>Objeto:</strong> <?= nl2br(htmlspecialchars($licitacao['objeto'])) ?>
        </div>
      <?php endif; ?>

      <?php if ($licitacao['arquivo']): ?>
        <div style="margin-top:1.5rem;">
          <a href="gerenciador/assets/uploads/licitacoes/<?= htmlspecialchars($licitacao['arquivo']) ?>" target="_blank" class="btn-ler"><i class="fas fa-file-pdf"></i> Ver edital / documento</a>
        </div>
      <?php endif; ?>
    </div>

    <?php if (!empty($participantes)): ?>
      <div class="ficha-card">
        <h3><i class="fas fa-people-group"></i> Empresas participantes</h3>
        <ul class="lista-participantes">
          <?php foreach ($participantes as $p): ?>
            <li>
              <span><?= htmlspecialchars($p['razao_social']) ?></span>
              <?php if ($p['vencedor']): ?>
                <span class="tag-info"><i class="fas fa-trophy"></i> Vencedora</span>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if ($contratoRelacionado): ?>
      <div class="ficha-card">
        <h3><i class="fas fa-file-signature"></i> Contrato relacionado</h3>
        <a href="contrato-detalhe.php?id=<?= $contratoRelacionado['id_contrato'] ?>" class="btn-leia-mais">
          Ver contrato Nº <?= htmlspecialchars($contratoRelacionado['numero_contrato']) ?>/<?= htmlspecialchars($contratoRelacionado['ano_exercicio']) ?> <i class="fas fa-arrow-right"></i>
        </a>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php include 'includes/footer.php'; ?>
