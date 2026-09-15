<?php
require_once __DIR__ . '/includes/auth.php';

$tituloPagina = 'Receitas e Despesas';

$categorias = [
    'empenhos'                 => 'Empenhos',
    'liquidacoes'               => 'Liquidações',
    'pagamentos'                => 'Pagamentos',
    'diarias'                   => 'Diárias',
    'combustivel'                => 'Combustível',
    'receitas-arrecadadas'       => 'Receitas Arrecadadas',
    'informacoes-consolidadas'    => 'Informações Consolidadas',
    'restos-a-pagar'             => 'Restos a Pagar',
    'ordem-cronologica'          => 'Ordem Cronológica de Pagamentos',
    'despesas-fixadas'          => 'Despesas Fixadas',
];

$catFiltro = $_GET['cat'] ?? '';
$busca = trim($_GET['busca'] ?? '');

$condicoes = [];
$parametros = [];
if ($catFiltro !== '' && isset($categorias[$catFiltro])) {
    $condicoes[] = 'categoria = :cat';
    $parametros['cat'] = $catFiltro;
}
if ($busca !== '') {
    $condicoes[] = '(descricao LIKE :busca OR favorecido LIKE :busca OR numero LIKE :busca)';
    $parametros['busca'] = '%' . $busca . '%';
}
$whereSql = $condicoes ? 'WHERE ' . implode(' AND ', $condicoes) : '';

$stmt = $pdo->prepare("SELECT * FROM execucao_orcamentaria $whereSql ORDER BY data_referencia DESC, id_execucao DESC LIMIT 300");
$stmt->execute($parametros);
$registros = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<h1 class="pagina__titulo">Receitas e Despesas</h1>
<p class="pagina__subtitulo">Execução orçamentária exibida no Portal da Transparência (empenhos, liquidações, pagamentos, diárias, receitas etc.).</p>

<?php if (!empty($_GET['ok'])): ?>
  <div class="alerta alerta-sucesso" style="max-width:none;">
    <i class="fa-solid fa-circle-check"></i>
    <?= $_GET['ok'] === 'excluido' ? 'Lançamento excluído com sucesso.' : 'Lançamento salvo com sucesso.' ?>
  </div>
<?php endif; ?>

<div class="toolbar">
  <form class="toolbar__busca" method="get">
    <input type="text" name="busca" placeholder="Buscar por descrição, favorecido ou nº..." value="<?= htmlspecialchars($busca) ?>">
    <select name="cat" onchange="this.form.submit()">
      <option value="">Todas as categorias</option>
      <?php foreach ($categorias as $slug => $nome): ?>
        <option value="<?= $slug ?>" <?= $catFiltro === $slug ? 'selected' : '' ?>><?= htmlspecialchars($nome) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-secundario"><i class="fa-solid fa-magnifying-glass"></i></button>
  </form>

  <a href="execucao-orcamentaria-form.php" class="btn btn-primario" style="width:auto;">
    <i class="fa-solid fa-plus"></i> Novo lançamento
  </a>
</div>

<div class="painel">
  <?php if (empty($registros)): ?>
    <div class="vazio"><i class="fa-solid fa-file-invoice-dollar"></i><p>Nenhum lançamento encontrado.</p></div>
  <?php else: ?>
    <table class="tabela-simples">
      <thead>
        <tr>
          <th>Categoria</th>
          <th>Descrição</th>
          <th>Favorecido</th>
          <th>Data</th>
          <th>Valor</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($registros as $r): ?>
          <tr>
            <td><?= htmlspecialchars($categorias[$r['categoria']] ?? $r['categoria']) ?></td>
            <td>
              <div class="titulo-linha"><?= $r['numero'] ? 'Nº ' . htmlspecialchars($r['numero']) . ' — ' : '' ?><?= htmlspecialchars($r['descricao']) ?></div>
            </td>
            <td><?= $r['favorecido'] ? htmlspecialchars($r['favorecido']) : '—' ?></td>
            <td><?= $r['data_referencia'] ? date('d/m/Y', strtotime($r['data_referencia'])) : '—' ?></td>
            <td>R$ <?= number_format((float) $r['valor'], 2, ',', '.') ?></td>
            <td>
              <div class="coluna-acoes">
                <a class="btn-icone" href="execucao-orcamentaria-form.php?id=<?= $r['id_execucao'] ?>" title="Editar"><i class="fa-solid fa-pen"></i></a>
                <form method="post" action="execucao-orcamentaria-excluir.php" onsubmit="return confirm('Excluir este lançamento?');" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $r['id_execucao'] ?>">
                  <button type="submit" class="btn-icone perigo" title="Excluir"><i class="fa-solid fa-trash"></i></button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <p style="font-size:.8rem; color:var(--cinza-medio); margin-top:.8rem;">Mostrando até 300 lançamentos mais recentes. Use a busca/filtro para refinar.</p>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
