<?php
$page_title = 'Licitantes e Contratados Sancionados — Câmara Municipal de Ananás';
require_once 'includes/funcoes.php';
include 'includes/header.php';

$tituloPaginaInterna = 'Licitantes e/ou Contratados Sancionados';
$subtituloPaginaInterna = 'Empresas suspensas ou impedidas de licitar/contratar com a Câmara.';

$sancionados = $pdo ? $pdo->query('SELECT * FROM licitantes_sancionados WHERE status = 1 ORDER BY data_inicio DESC')->fetchAll() : [];

include 'includes/pagina-header.php';
?>

<section class="pagina-interna-conteudo">
  <div class="section-container">
    <?php if (empty($sancionados)): ?>
      <div class="alerta-portal" style="max-width:none;">
        <i class="fas fa-circle-check"></i>
        Não há, até o momento, licitantes ou contratados sancionados registrados pela Câmara Municipal de Ananás.
      </div>
    <?php else: ?>
      <div class="tabela-publica-wrap">
        <div class="tabela-publica-scroll">
          <table class="tabela-publica" style="width:100%;">
            <thead>
              <tr>
                <th>Empresa / CPF-CNPJ</th>
                <th>Tipo de sanção</th>
                <th>Processo</th>
                <th>Fundamento legal</th>
                <th>Início</th>
                <th>Fim</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($sancionados as $s): ?>
                <tr class="linha-sem-clique">
                  <td><strong><?= htmlspecialchars($s['nome']) ?></strong><br><small style="color:var(--texto-claro);"><?= htmlspecialchars($s['cnpj_cpf'] ?: '—') ?></small></td>
                  <td><?= htmlspecialchars($s['tipo_sancao']) ?></td>
                  <td><?= $s['numero_processo'] ? htmlspecialchars($s['numero_processo']) : '—' ?></td>
                  <td><?= $s['fundamento_legal'] ? htmlspecialchars($s['fundamento_legal']) : '—' ?></td>
                  <td><?= $s['data_inicio'] ? date('d/m/Y', strtotime($s['data_inicio'])) : '—' ?></td>
                  <td><?= $s['data_fim'] ? date('d/m/Y', strtotime($s['data_fim'])) : '—' ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
