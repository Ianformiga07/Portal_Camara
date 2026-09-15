<?php
$page_title = 'Recursos Humanos — Cargos e Remuneração — Câmara Municipal de Ananás';
require_once 'includes/funcoes.php';
include 'includes/header.php';

$tituloPaginaInterna = 'Cargos, Remuneração e Folha de Pagamento';
$subtituloPaginaInterna = 'Informações complementares de Recursos Humanos. Para o quadro de servidores, acesse a seção Servidores.';

$categorias = [
    'cargos-comissionados' => 'Cargos Comissionados',
    'tabela-remuneratoria' => 'Tabela Remuneratória',
    'folha-servidores'     => 'Folha — Servidores',
    'folha-estagiarios'    => 'Folha — Estagiários',
    'terceirizados'        => 'Terceirizados',
    'editais-concursos'    => 'Editais de Concursos',
];

$catAtual = $_GET['cat'] ?? 'cargos-comissionados';
if (!isset($categorias[$catAtual])) {
    $catAtual = 'cargos-comissionados';
}

$registros = [];
$documentos = [];
if ($pdo) {
    if ($catAtual === 'editais-concursos') {
        $stmt = $pdo->prepare(
            "SELECT d.* FROM documentos d
             JOIN categorias_documentos c ON c.id_categoria = d.id_categoria
             WHERE c.slug = 'editais-concursos' AND d.status = 1
             ORDER BY d.data_publicacao DESC"
        );
        $stmt->execute();
        $documentos = $stmt->fetchAll();
    } else {
        $stmt = $pdo->prepare('SELECT * FROM rh_informacoes WHERE categoria = :cat AND status = 1 ORDER BY competencia DESC, id_rh DESC');
        $stmt->execute(['cat' => $catAtual]);
        $registros = $stmt->fetchAll();
    }
}

include 'includes/pagina-header.php';
?>

<style>
  .subtabs-orc { display:flex; flex-wrap:wrap; gap:.5rem; margin-bottom:1.2rem; }
  .subtabs-orc a {
    padding:.5rem .9rem; border-radius:var(--radius-sm); font-size:.82rem; font-weight:600;
    background:var(--cinza-claro); color:var(--texto-medio); text-decoration:none; border:1px solid var(--cinza-borda);
  }
  .subtabs-orc a.ativo { background:var(--verde-medio); color:#fff; border-color:var(--verde-medio); }
</style>

<section class="pagina-interna-conteudo">
  <div class="section-container">

    <div class="subtabs-orc">
      <?php foreach ($categorias as $slug => $nome): ?>
        <a href="?cat=<?= $slug ?>" class="<?= $catAtual === $slug ? 'ativo' : '' ?>"><?= htmlspecialchars($nome) ?></a>
      <?php endforeach; ?>
    </div>

    <?php if ($catAtual === 'editais-concursos'): ?>

      <?php if (empty($documentos)): ?>
        <div class="vazio-lista"><i class="fas fa-bullhorn"></i><p>Nenhum edital publicado no momento.</p></div>
      <?php else: ?>
        <?php foreach ($documentos as $d): ?>
          <div class="arquivo-lista-item">
            <div class="icone-pdf"><i class="fas fa-bullhorn"></i></div>
            <div class="info">
              <strong><?= $d['numero_documento'] ? 'Edital ' . htmlspecialchars($d['numero_documento']) . ' — ' : '' ?><?= htmlspecialchars($d['titulo']) ?></strong>
              <span><?= $d['data_publicacao'] ? date('d/m/Y', strtotime($d['data_publicacao'])) : '—' ?></span>
              <?php if ($d['descricao']): ?><p style="margin-top:.3rem; font-size:.85rem; color:var(--texto-medio);"><?= htmlspecialchars(resumirTexto($d['descricao'], 160)) ?></p><?php endif; ?>
            </div>
            <div class="acoes">
              <?php if ($d['arquivo']): ?>
                <a href="gerenciador/assets/uploads/documentos/<?= htmlspecialchars($d['arquivo']) ?>" target="_blank" title="Visualizar"><i class="fas fa-eye"></i></a>
                <a href="gerenciador/assets/uploads/documentos/<?= htmlspecialchars($d['arquivo']) ?>" download title="Download"><i class="fas fa-download"></i></a>
              <?php else: ?>
                <span style="font-size:.78rem; color:var(--texto-claro);">Sem arquivo anexado</span>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

    <?php else: ?>

      <?php if (empty($registros)): ?>
        <div class="vazio-lista"><i class="fas fa-id-card"></i><p>Nenhuma informação cadastrada nesta categoria ainda.</p></div>
      <?php else: ?>
        <div class="tabela-publica-wrap">
          <div class="tabela-publica-scroll">
            <table class="tabela-publica" style="width:100%;">
              <thead>
                <tr>
                  <th><?= in_array($catAtual, ['folha-servidores', 'folha-estagiarios', 'terceirizados']) ? 'Descrição' : 'Cargo / Referência' ?></th>
                  <th>Detalhe</th>
                  <th>Quantidade</th>
                  <th>Competência</th>
                  <th>Valor</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($registros as $r): ?>
                  <tr class="linha-sem-clique">
                    <td><?= htmlspecialchars($r['referencia']) ?></td>
                    <td><?= $r['descricao'] ? htmlspecialchars($r['descricao']) : '—' ?></td>
                    <td><?= $r['quantidade'] !== null ? (int) $r['quantidade'] : '—' ?></td>
                    <td><?= $r['competencia'] ? htmlspecialchars($r['competencia']) : '—' ?></td>
                    <td><?= $r['valor'] !== null ? 'R$ ' . number_format((float) $r['valor'], 2, ',', '.') : '—' ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>

    <?php endif; ?>

  </div>
</section>

<?php include 'includes/footer.php'; ?>
