<?php
require_once 'config/database.php';
require_once 'includes/funcoes.php';

$id = (int) ($_GET['id'] ?? 0);

if (!$pdo) {
    header('Location: contratos.php');
    exit;
}

$stmt = $pdo->prepare(
    "SELECT c.*, f.razao_social, f.cnpj_cpf, tc.descricao AS tipo_contratacao_descricao,
            fin.descricao AS finalidade_descricao, sit.descricao AS situacao_descricao,
            fis.nome_completo AS fiscal_nome, ges.nome_completo AS gestor_nome,
            l.numero_processo AS licitacao_numero
     FROM contratos c
     LEFT JOIN fornecedores f ON f.id_fornecedor = c.id_fornecedor
     LEFT JOIN tipos_contratacao tc ON tc.id_tipo_contratacao = c.id_tipo_contratacao
     LEFT JOIN licitacao_finalidades fin ON fin.id_finalidade = c.id_finalidade
     LEFT JOIN contrato_situacoes sit ON sit.id_situacao = c.id_situacao
     LEFT JOIN servidores fis ON fis.id_servidor = c.id_fiscal
     LEFT JOIN servidores ges ON ges.id_servidor = c.id_gestor
     LEFT JOIN licitacoes l ON l.id_licitacao = c.id_licitacao
     WHERE c.id_contrato = :id AND c.status = 1"
);
$stmt->execute(['id' => $id]);
$contrato = $stmt->fetch();

if (!$contrato) {
    header('Location: contratos.php');
    exit;
}

$aditivos = $pdo->prepare('SELECT * FROM contratos_aditivos WHERE id_contrato = :id ORDER BY data_aditivo DESC');
$aditivos->execute(['id' => $id]);
$aditivos = $aditivos->fetchAll();

$page_title = 'Contrato ' . $contrato['numero_contrato'] . '/' . $contrato['ano_exercicio'] . ' — Câmara Municipal de Ananás';
include 'includes/header.php';

$tituloPaginaInterna = 'Contrato Nº ' . $contrato['numero_contrato'] . '/' . $contrato['ano_exercicio'];
$subtituloPaginaInterna = $contrato['razao_social'] ?: '';
include 'includes/pagina-header.php';
?>

<section class="pagina-interna-conteudo">
  <div class="section-container">
    <p style="margin-bottom:1.5rem;"><a href="contratos.php"><i class="fas fa-arrow-left"></i> Voltar para todos os contratos</a></p>

    <div class="ficha-card">
      <h3><i class="fas fa-clipboard-list"></i> Dados do contrato</h3>
      <dl class="ficha-grid">
        <div><dt>Contratada</dt><dd><?= htmlspecialchars($contrato['razao_social'] ?: '—') ?></dd></div>
        <?php if ($contrato['cnpj_cpf']): ?><div><dt>CNPJ/CPF</dt><dd><?= htmlspecialchars($contrato['cnpj_cpf']) ?></dd></div><?php endif; ?>
        <div><dt>Tipo de contratação</dt><dd><?= htmlspecialchars($contrato['tipo_contratacao_descricao'] ?: '—') ?></dd></div>
        <div><dt>Finalidade</dt><dd><?= htmlspecialchars($contrato['finalidade_descricao'] ?: '—') ?></dd></div>
        <div><dt>Situação</dt><dd><?= htmlspecialchars($contrato['situacao_descricao'] ?: '—') ?></dd></div>
        <div><dt>Início da vigência</dt><dd><?= $contrato['inicio_vigencia'] ? date('d/m/Y', strtotime($contrato['inicio_vigencia'])) : '—' ?></dd></div>
        <div><dt>Fim da vigência</dt><dd><?= $contrato['fim_vigencia'] ? date('d/m/Y', strtotime($contrato['fim_vigencia'])) : '—' ?></dd></div>
        <div><dt>Valor do contrato</dt><dd><?= $contrato['valor_estimado'] !== null ? 'R$ ' . number_format($contrato['valor_estimado'], 2, ',', '.') : '—' ?></dd></div>
        <?php if ($contrato['fiscal_nome']): ?><div><dt>Fiscal do contrato</dt><dd><?= htmlspecialchars($contrato['fiscal_nome']) ?></dd></div><?php endif; ?>
        <?php if ($contrato['gestor_nome']): ?><div><dt>Gestor do contrato</dt><dd><?= htmlspecialchars($contrato['gestor_nome']) ?></dd></div><?php endif; ?>
      </dl>

      <?php if ($contrato['objeto']): ?>
        <div class="ficha-objeto"><strong>Objeto:</strong> <?= nl2br(htmlspecialchars($contrato['objeto'])) ?></div>
      <?php endif; ?>

      <?php if ($contrato['arquivo']): ?>
        <div style="margin-top:1.5rem;">
          <a href="gerenciador/assets/uploads/contratos/<?= htmlspecialchars($contrato['arquivo']) ?>" target="_blank" class="btn-ler"><i class="fas fa-file-pdf"></i> Ver contrato assinado</a>
        </div>
      <?php endif; ?>

      <?php if ($contrato['licitacao_numero']): ?>
        <div style="margin-top:1rem;">
          <a href="licitacao-detalhe.php?id=<?= $contrato['id_licitacao'] ?>" class="btn-leia-mais">Ver licitação de origem (Processo <?= htmlspecialchars($contrato['licitacao_numero']) ?>) <i class="fas fa-arrow-right"></i></a>
        </div>
      <?php endif; ?>
    </div>

    <?php if (!empty($aditivos)): ?>
      <div class="ficha-card">
        <h3><i class="fas fa-file-circle-plus"></i> Termos aditivos</h3>
        <?php foreach ($aditivos as $a): ?>
          <div class="arquivo-lista-item">
            <div class="icone-pdf"><i class="fas fa-file-pdf"></i></div>
            <div class="info">
              <strong><?= htmlspecialchars($a['numero_aditivo'] ?: 'Termo aditivo') ?></strong>
              <span><?= $a['data_aditivo'] ? date('d/m/Y', strtotime($a['data_aditivo'])) : '' ?><?= $a['descricao'] ? ' · ' . htmlspecialchars($a['descricao']) : '' ?></span>
            </div>
            <?php if ($a['arquivo']): ?>
              <div class="acoes">
                <a href="gerenciador/assets/uploads/contratos/<?= htmlspecialchars($a['arquivo']) ?>" target="_blank" title="Ver PDF"><i class="fas fa-eye"></i></a>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php include 'includes/footer.php'; ?>
