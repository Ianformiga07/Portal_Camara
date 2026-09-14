<?php
$page_title = 'Diário Oficial — Câmara Municipal de Ananás';
require_once 'includes/funcoes.php';
include 'includes/header.php';

$tituloPaginaInterna = 'Diário Oficial';
$subtituloPaginaInterna = 'Publicações oficiais dos atos da Câmara Municipal de Ananás.';

$ano = $_GET['ano'] ?? '';

$diarioAtual = null;
$edicoes = [];
$anosDisponiveis = [];

if ($pdo) {
    $diarioAtual = $pdo->query(
        "SELECT d.* FROM documentos d
         JOIN categorias_documentos c ON c.id_categoria = d.id_categoria
         WHERE c.slug = 'diario-oficial' AND d.status = 1
         ORDER BY d.data_publicacao DESC LIMIT 1"
    )->fetch();

    $anosDisponiveis = $pdo->query(
        "SELECT DISTINCT YEAR(d.data_publicacao) AS ano FROM documentos d
         JOIN categorias_documentos c ON c.id_categoria = d.id_categoria
         WHERE c.slug = 'diario-oficial' AND d.data_publicacao IS NOT NULL
         ORDER BY ano DESC"
    )->fetchAll(PDO::FETCH_COLUMN);

    $condicoes = ["c.slug = 'diario-oficial'", 'd.status = 1'];
    $parametros = [];
    if ($ano !== '') {
        $condicoes[] = 'YEAR(d.data_publicacao) = :ano';
        $parametros['ano'] = $ano;
    }
    $whereSql = 'WHERE ' . implode(' AND ', $condicoes);

    $sql = "SELECT d.id_documento, d.numero_documento, d.titulo, d.data_publicacao, d.arquivo
            FROM documentos d
            JOIN categorias_documentos c ON c.id_categoria = d.id_categoria
            $whereSql
            ORDER BY d.data_publicacao DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);
    $edicoes = $stmt->fetchAll();
}

include 'includes/pagina-header.php';
?>

<!-- DataTables (ordenacao, busca e paginacao da tabela) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.13.8/css/jquery.dataTables.min.css">

<section class="pagina-interna-conteudo">
  <div class="section-container">

    <?php if ($diarioAtual): ?>
      <div class="edicao-card" style="margin-bottom:2.5rem;">
        <div class="label-edicao">Última edição publicada</div>
        <div class="num-edicao">
          <?= $diarioAtual['numero_documento'] ? 'Edição Nº ' . htmlspecialchars($diarioAtual['numero_documento']) : 'Diário Oficial' ?>
          <span>– <?= $diarioAtual['data_publicacao'] ? date('d/m/Y', strtotime($diarioAtual['data_publicacao'])) : '' ?></span>
        </div>
        <?php if ($diarioAtual['arquivo']): ?>
          <div class="diario-capa">
            <iframe src="gerenciador/assets/uploads/documentos/<?= htmlspecialchars($diarioAtual['arquivo']) ?>#toolbar=0&navpanes=0&scrollbar=0&view=FitH" class="diario-iframe" title="Diário Oficial" loading="lazy"></iframe>
          </div>
          <div class="edicao-actions">
            <a href="gerenciador/assets/uploads/documentos/<?= htmlspecialchars($diarioAtual['arquivo']) ?>" target="_blank" class="btn-ler"><i class="fas fa-book-open"></i> Ler</a>
            <a href="gerenciador/assets/uploads/documentos/<?= htmlspecialchars($diarioAtual['arquivo']) ?>" download class="btn-baixar"><i class="fas fa-download"></i> Baixar PDF</a>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <h2 style="font-size:1.2rem; margin-bottom:1rem; color:var(--verde-escuro);"><i class="fas fa-folder-open"></i> Arquivo completo</h2>

    <form class="tabela-toolbar" method="get" id="formFiltrosDiario">
      <div class="tabela-busca-dt">
        <i class="fas fa-magnifying-glass"></i>
        <input type="text" id="buscaDiario" placeholder="Buscar por número ou título...">
      </div>
      <div class="filtros-selects">
        <select name="ano" onchange="this.form.submit()">
          <option value="">Todos os anos</option>
          <?php foreach ($anosDisponiveis as $a): ?>
            <option value="<?= $a ?>" <?= (string) $ano === (string) $a ? 'selected' : '' ?>><?= $a ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </form>

    <?php if (empty($edicoes)): ?>
      <div class="tabela-publica-wrap">
        <div class="tabela-publica-vazio">
          <i class="fas fa-file-circle-xmark"></i>
          <p>Nenhuma edição encontrada<?= $ano !== '' ? ' para ' . htmlspecialchars($ano) : '' ?>.</p>
        </div>
      </div>
    <?php else: ?>
      <div class="tabela-publica-wrap">
        <div class="tabela-publica-scroll">
          <table class="tabela-publica" id="tabelaDiario" style="width:100%;">
            <thead>
              <tr>
                <th>Edição</th>
                <th>Título</th>
                <th>Data de publicação</th>
                <th class="col-acoes">Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($edicoes as $e): ?>
                <tr<?= $e['arquivo'] ? " onclick=\"abrirDocumento(event, 'gerenciador/assets/uploads/documentos/" . htmlspecialchars($e['arquivo'], ENT_QUOTES) . "')\"" : ' class="linha-sem-clique"' ?>>
                  <td class="col-num"><span class="badge-pill"><?= $e['numero_documento'] ? 'Nº ' . htmlspecialchars($e['numero_documento']) : 'Diário Oficial' ?></span></td>
                  <td><?php if ($e['titulo']): ?><div class="doc-titulo-cel"><?= htmlspecialchars($e['titulo']) ?></div><?php else: ?>—<?php endif; ?></td>
                  <td data-order="<?= $e['data_publicacao'] ? date('Ymd', strtotime($e['data_publicacao'])) : '0' ?>"><?= $e['data_publicacao'] ? date('d/m/Y', strtotime($e['data_publicacao'])) : '—' ?></td>
                  <td class="col-acoes">
                    <?php if ($e['arquivo']): ?>
                      <a href="gerenciador/assets/uploads/documentos/<?= htmlspecialchars($e['arquivo']) ?>" target="_blank" title="Visualizar" onclick="event.stopPropagation()"><i class="fas fa-eye"></i></a>
                      <a href="gerenciador/assets/uploads/documentos/<?= htmlspecialchars($e['arquivo']) ?>" download title="Download" onclick="event.stopPropagation()"><i class="fas fa-download"></i></a>
                    <?php else: ?>
                      <span style="font-size:.78rem; color:var(--texto-claro);">Sem arquivo</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>

  </div>
</section>

<!-- jQuery + DataTables -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.13.8/js/jquery.dataTables.min.js"></script>
<script>
  function abrirDocumento(ev, url) {
    if (ev.target.closest('a')) return;
    window.open(url, '_blank');
  }

  $(function () {
    var tabela = $('#tabelaDiario').DataTable({
      language: {
        url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/pt-BR.json',
        lengthMenu: '_MENU_ por página'
      },
      columnDefs: [
        { orderable: false, targets: 3 }
      ],
      order: [[2, 'desc']],
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100]
    });

    $('#buscaDiario').on('keyup', function () {
      tabela.search(this.value).draw();
    });
  });
</script>

<?php include 'includes/footer.php'; ?>
