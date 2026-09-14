<?php
$page_title = 'Servidores — Câmara Municipal de Ananás';
require_once 'includes/funcoes.php';
include 'includes/header.php';

$tituloPaginaInterna = 'Servidores';
$subtituloPaginaInterna = 'Quadro de servidores da Câmara Municipal de Ananás — Portal da Transparência.';

$departamento = $_GET['departamento'] ?? '';
$cargoFiltro  = $_GET['cargo'] ?? '';

$servidores = [];
$departamentos = [];
$cargos = [];

if ($pdo) {
    $departamentos = $pdo->query('SELECT id_departamento, descricao FROM departamentos ORDER BY descricao')->fetchAll();
    $cargos        = $pdo->query('SELECT id_cargo, descricao FROM cargos ORDER BY descricao')->fetchAll();

    $condicoes = ['s.ativo = 1'];
    $parametros = [];
    if ($departamento !== '') {
        $condicoes[] = 's.id_departamento = :departamento';
        $parametros['departamento'] = $departamento;
    }
    if ($cargoFiltro !== '') {
        $condicoes[] = 's.id_cargo = :cargo';
        $parametros['cargo'] = $cargoFiltro;
    }
    $whereSql = 'WHERE ' . implode(' AND ', $condicoes);

    $sql = "SELECT s.id_servidor, s.nome_completo, s.foto_perfil, s.cpf, s.matricula, s.data_admissao,
                   c.descricao AS cargo_descricao, d.descricao AS departamento_descricao
            FROM servidores s
            LEFT JOIN cargos c ON c.id_cargo = s.id_cargo
            LEFT JOIN departamentos d ON d.id_departamento = s.id_departamento
            $whereSql
            ORDER BY s.nome_completo";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);
    $servidores = $stmt->fetchAll();
}

include 'includes/pagina-header.php';
?>

<!-- DataTables (ordenação, busca e paginação da tabela) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.13.8/css/jquery.dataTables.min.css">

<section class="pagina-interna-conteudo">
  <div class="section-container">

    <form class="tabela-toolbar" method="get" id="formFiltrosServidores">
      <div class="tabela-busca-dt">
        <i class="fas fa-magnifying-glass"></i>
        <input type="text" id="buscaServidores" placeholder="Buscar por nome, cargo ou departamento...">
      </div>
      <div class="filtros-selects">
        <select name="cargo" onchange="this.form.submit()">
          <option value="">Todos os cargos</option>
          <?php foreach ($cargos as $c): ?>
            <option value="<?= $c['id_cargo'] ?>" <?= (string) $cargoFiltro === (string) $c['id_cargo'] ? 'selected' : '' ?>><?= htmlspecialchars($c['descricao']) ?></option>
          <?php endforeach; ?>
        </select>
        <select name="departamento" onchange="this.form.submit()">
          <option value="">Todos os departamentos</option>
          <?php foreach ($departamentos as $d): ?>
            <option value="<?= $d['id_departamento'] ?>" <?= (string) $departamento === (string) $d['id_departamento'] ? 'selected' : '' ?>><?= htmlspecialchars($d['descricao']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </form>

    <?php if (empty($servidores)): ?>
      <div class="tabela-publica-wrap">
        <div class="tabela-publica-vazio">
          <i class="fas fa-id-card"></i>
          <p>Nenhum servidor encontrado para os filtros selecionados.</p>
        </div>
      </div>
    <?php else: ?>
      <div class="tabela-publica-wrap">
        <div class="tabela-publica-scroll">
          <table class="tabela-publica" id="tabelaServidores" style="width:100%;">
            <thead>
              <tr>
                <th class="col-foto"></th>
                <th>Nome</th>
                <th>CPF</th>
                <th>Cargo</th>
                <th>Departamento</th>
                <th>Matrícula</th>
                <th>Admissão</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($servidores as $s): ?>
                <tr onclick="window.location='servidor-detalhe.php?id=<?= $s['id_servidor'] ?>'">
                  <td class="col-foto">
                    <?php if (!empty($s['foto_perfil'])): ?>
                      <img class="foto-mini" src="gerenciador/assets/uploads/servidores/<?= htmlspecialchars($s['foto_perfil']) ?>" alt="">
                    <?php else: ?>
                      <div class="foto-mini-icone"><i class="fas fa-user"></i></div>
                    <?php endif; ?>
                  </td>
                  <td><a class="nome-link" href="servidor-detalhe.php?id=<?= $s['id_servidor'] ?>"><?= htmlspecialchars($s['nome_completo']) ?></a></td>
                  <td class="col-cpf"><?= mascararCpf($s['cpf']) ?></td>
                  <td><?= htmlspecialchars($s['cargo_descricao'] ?: '—') ?></td>
                  <td><?= htmlspecialchars($s['departamento_descricao'] ?: '—') ?></td>
                  <td><?= htmlspecialchars($s['matricula'] ?: '—') ?></td>
                  <td data-order="<?= $s['data_admissao'] ? date('Ymd', strtotime($s['data_admissao'])) : '0' ?>"><?= $s['data_admissao'] ? date('d/m/Y', strtotime($s['data_admissao'])) : '—' ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>

    <p class="tabela-nota-rodape">
      <i class="fas fa-shield-halved"></i>
      <span>Em conformidade com a Lei Geral de Proteção de Dados (LGPD), o CPF é exibido parcialmente oculto. Dados de remuneração dependem da integração com a folha de pagamento e não constam nesta listagem.</span>
    </p>

  </div>
</section>

<!-- jQuery + DataTables -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.13.8/js/jquery.dataTables.min.js"></script>
<script>
  $(function () {
    var tabela = $('#tabelaServidores').DataTable({
      language: {
        url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/pt-BR.json',
        lengthMenu: '_MENU_ por página'
      },
      columnDefs: [
        { orderable: false, targets: 0 }
      ],
      order: [[1, 'asc']],
      pageLength: 15,
      lengthMenu: [10, 15, 25, 50]
    });

    $('#buscaServidores').on('keyup', function () {
      tabela.search(this.value).draw();
    });
  });
</script>

<?php include 'includes/footer.php'; ?>
