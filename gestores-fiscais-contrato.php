<?php
$page_title = 'Gestores e Fiscais de Contrato — Câmara Municipal de Ananás';
include 'includes/header.php';

$tituloPaginaInterna = 'Relação de Gestores e Fiscais de Contrato';
$subtituloPaginaInterna = 'Servidores responsáveis pela gestão e fiscalização de cada contrato vigente.';

$registros = [];
if ($pdo) {
    $registros = $pdo->query(
        "SELECT c.id_contrato, c.numero_contrato, c.ano_exercicio, f.razao_social,
                fis.nome_completo AS fiscal_nome, ges.nome_completo AS gestor_nome
         FROM contratos c
         LEFT JOIN fornecedores f ON f.id_fornecedor = c.id_fornecedor
         LEFT JOIN servidores fis ON fis.id_servidor = c.id_fiscal
         LEFT JOIN servidores ges ON ges.id_servidor = c.id_gestor
         WHERE c.status = 1 AND (c.id_fiscal IS NOT NULL OR c.id_gestor IS NOT NULL)
         ORDER BY c.numero_contrato DESC"
    )->fetchAll();
}

include 'includes/pagina-header.php';
?>
<section class="pagina-interna-conteudo">
  <div class="section-container">
    <?php if (empty($registros)): ?>
      <div class="vazio-lista"><i class="fas fa-user-shield"></i><p>Nenhum contrato com fiscal ou gestor definido ainda.</p></div>
    <?php else: ?>
      <?php foreach ($registros as $r): ?>
        <a href="contrato-detalhe.php?id=<?= $r['id_contrato'] ?>" class="arquivo-lista-item" style="text-decoration:none; color:inherit;">
          <div class="icone-pdf"><i class="fas fa-user-shield"></i></div>
          <div class="info">
            <strong>Contrato Nº <?= htmlspecialchars($r['numero_contrato']) ?>/<?= htmlspecialchars($r['ano_exercicio']) ?> — <?= htmlspecialchars($r['razao_social'] ?: '—') ?></strong>
            <span>
              <?= $r['fiscal_nome'] ? 'Fiscal: ' . htmlspecialchars($r['fiscal_nome']) : 'Fiscal não definido' ?>
              <?= $r['gestor_nome'] ? ' · Gestor: ' . htmlspecialchars($r['gestor_nome']) : ' · Gestor não definido' ?>
            </span>
          </div>
        </a>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
