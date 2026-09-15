<?php
$page_title = 'Patrimônio e Almoxarifado — Câmara Municipal de Ananás';
require_once 'includes/funcoes.php';
include 'includes/header.php';

$tituloPaginaInterna = 'Patrimônio e Almoxarifado';
$subtituloPaginaInterna = 'Bens patrimoniais da Câmara Municipal.';

$busca = trim($_GET['busca'] ?? '');
$itens = [];
if ($pdo) {
    $sql = 'SELECT * FROM patrimonio WHERE status = 1';
    $params = [];
    if ($busca !== '') {
        $sql .= ' AND (descricao LIKE :busca OR codigo LIKE :busca OR categoria LIKE :busca)';
        $params['busca'] = '%' . $busca . '%';
    }
    $sql .= ' ORDER BY codigo ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $itens = $stmt->fetchAll();
}

$situacoes = [
    'em_uso'      => 'Em uso',
    'manutencao'  => 'Em manutenção',
    'baixado'     => 'Baixado',
];

include 'includes/pagina-header.php';
?>

<section class="pagina-interna-conteudo">
  <div class="section-container">

    <form class="toolbar-filtro" method="get" style="margin-bottom:1.2rem;">
      <input type="text" name="busca" placeholder="Buscar por código, descrição ou categoria..." value="<?= htmlspecialchars($busca) ?>">
      <button type="submit"><i class="fas fa-magnifying-glass"></i> Buscar</button>
    </form>

    <?php if (empty($itens)): ?>
      <div class="vazio-lista"><i class="fas fa-boxes-stacked"></i><p>Nenhum bem patrimonial encontrado.</p></div>
    <?php else: ?>
      <div class="tabela-publica-wrap">
        <div class="tabela-publica-scroll">
          <table class="tabela-publica" style="width:100%;">
            <thead>
              <tr>
                <th>Código</th>
                <th>Descrição</th>
                <th>Categoria</th>
                <th>Aquisição</th>
                <th>Valor</th>
                <th>Situação</th>
                <th>Localização</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($itens as $p): ?>
                <tr class="linha-sem-clique">
                  <td><span class="badge-pill"><?= htmlspecialchars($p['codigo']) ?></span></td>
                  <td><?= htmlspecialchars($p['descricao']) ?></td>
                  <td><?= $p['categoria'] ? htmlspecialchars($p['categoria']) : '—' ?></td>
                  <td><?= $p['data_aquisicao'] ? date('d/m/Y', strtotime($p['data_aquisicao'])) : '—' ?></td>
                  <td><?= $p['valor_aquisicao'] !== null ? 'R$ ' . number_format((float) $p['valor_aquisicao'], 2, ',', '.') : '—' ?></td>
                  <td><?= htmlspecialchars($situacoes[$p['situacao']] ?? $p['situacao']) ?></td>
                  <td><?= $p['localizacao'] ? htmlspecialchars($p['localizacao']) : '—' ?></td>
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
