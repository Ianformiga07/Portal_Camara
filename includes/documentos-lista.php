<?php
/**
 * Template compartilhado de listagem por categoria de documento.
 * O arquivo que inclui este precisa definir antes:
 *   $categoriaSlug          (ex: 'leis-municipais')
 *   $tituloPaginaInterna    (ex: 'Leis Municipais')
 *   $subtituloPaginaInterna (opcional)
 */
require_once 'includes/funcoes.php';
include 'includes/header.php';

$ano = $_GET['ano'] ?? '';

$documentos = [];
$anosDisponiveis = [];

if ($pdo) {
    $anosDisponiveis = $pdo->prepare(
        "SELECT DISTINCT YEAR(d.data_publicacao) AS ano FROM documentos d
         JOIN categorias_documentos c ON c.id_categoria = d.id_categoria
         WHERE c.slug = :slug AND d.data_publicacao IS NOT NULL
         ORDER BY ano DESC"
    );
    $anosDisponiveis->execute(['slug' => $categoriaSlug]);
    $anosDisponiveis = $anosDisponiveis->fetchAll(PDO::FETCH_COLUMN);

    $condicoes = ['c.slug = :slug', 'd.status = 1'];
    $parametros = ['slug' => $categoriaSlug];
    if ($ano !== '') {
        $condicoes[] = 'YEAR(d.data_publicacao) = :ano';
        $parametros['ano'] = $ano;
    }
    $whereSql = 'WHERE ' . implode(' AND ', $condicoes);

    $sql = "SELECT d.id_documento, d.numero_documento, d.titulo, d.descricao, d.data_publicacao, d.arquivo,
                   s.nome_completo AS autor_nome
            FROM documentos d
            JOIN categorias_documentos c ON c.id_categoria = d.id_categoria
            LEFT JOIN vereadores v ON v.id_vereador = d.id_autor_vereador
            LEFT JOIN servidores s ON s.id_servidor = v.id_servidor
            $whereSql
            ORDER BY d.data_publicacao DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);
    $documentos = $stmt->fetchAll();
}

include 'includes/pagina-header.php';
?>

<!-- DataTables (ordenacao, busca e paginacao da tabela) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.13.8/css/jquery.dataTables.min.css">

<section class="pagina-interna-conteudo">
  <div class="section-container">

    <form class="tabela-toolbar" method="get" id="formFiltrosDocumentos">
      <div class="tabela-busca-dt">
        <i class="fas fa-magnifying-glass"></i>
        <input type="text" id="buscaDocumentos" placeholder="Buscar por número, título ou ementa...">
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

    <?php if (empty($documentos)): ?>
      <div class="tabela-publica-wrap">
        <div class="tabela-publica-vazio">
          <i class="fas fa-file-circle-xmark"></i>
          <p>Nenhum documento encontrado<?= $ano !== '' ? ' para ' . htmlspecialchars($ano) : '' ?>.</p>
        </div>
      </div>
    <?php else: ?>
      <div class="tabela-publica-wrap">
        <div class="tabela-publica-scroll">
          <table class="tabela-publica" id="tabelaDocumentos" style="width:100%;">
            <thead>
              <tr>
                <th>Número</th>
                <th>Título / Ementa</th>
                <th>Data</th>
                <th>Autoria</th>
                <th class="col-acoes">Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($documentos as $d): ?>
                <tr<?= $d['arquivo'] ? " onclick=\"abrirDocumento(event, 'gerenciador/assets/uploads/documentos/" . htmlspecialchars($d['arquivo'], ENT_QUOTES) . "')\"" : ' class="linha-sem-clique"' ?>>
                  <td class="col-num"><?php if ($d['numero_documento']): ?><span class="badge-pill">Nº <?= htmlspecialchars($d['numero_documento']) ?></span><?php else: ?>—<?php endif; ?></td>
                  <td>
                    <?php if ($d['titulo']): ?><div class="doc-titulo-cel"><?= htmlspecialchars($d['titulo']) ?></div><?php endif; ?>
                    <?php if ($d['descricao']): ?><div style="font-size:.82rem; color:var(--texto-claro); margin-top:.2rem;"><?= htmlspecialchars(resumirTexto($d['descricao'], 140)) ?></div><?php endif; ?>
                  </td>
                  <td data-order="<?= $d['data_publicacao'] ? date('Ymd', strtotime($d['data_publicacao'])) : '0' ?>"><?= $d['data_publicacao'] ? date('d/m/Y', strtotime($d['data_publicacao'])) : '—' ?></td>
                  <td><?= $d['autor_nome'] ? htmlspecialchars($d['autor_nome']) : '—' ?></td>
                  <td class="col-acoes">
                    <?php if ($d['arquivo']): ?>
                      <a href="gerenciador/assets/uploads/documentos/<?= htmlspecialchars($d['arquivo']) ?>" target="_blank" title="Visualizar" onclick="event.stopPropagation()"><i class="fas fa-eye"></i></a>
                      <a href="gerenciador/assets/uploads/documentos/<?= htmlspecialchars($d['arquivo']) ?>" download title="Download" onclick="event.stopPropagation()"><i class="fas fa-download"></i></a>
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
    var tabela = $('#tabelaDocumentos').DataTable({
      language: {
        url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/pt-BR.json',
        lengthMenu: '_MENU_ por página'
      },
      columnDefs: [
        { orderable: false, targets: 4 }
      ],
      order: [[2, 'desc']],
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100]
    });

    $('#buscaDocumentos').on('keyup', function () {
      tabela.search(this.value).draw();
    });
  });
</script>

<?php include 'includes/footer.php'; ?>
