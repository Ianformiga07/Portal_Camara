<?php
require_once __DIR__ . '/includes/auth.php';

$tituloPagina = 'Comissões';

$legislaturas = $pdo->query(
    'SELECT lb.id_legislatura, lb.descricao, m.descricao AS mandato_descricao
     FROM legislaturas_bienio lb
     JOIN mandatos_eletivos m ON m.id_mandato = lb.id_mandato
     ORDER BY lb.id_legislatura DESC'
)->fetchAll();

$idLegislatura = $_GET['legislatura'] ?? ($legislaturas[0]['id_legislatura'] ?? '');

$vereadoresAtivos = $pdo->query(
    "SELECT v.id_vereador, s.nome_completo
     FROM vereadores v
     JOIN servidores s ON s.id_servidor = v.id_servidor
     WHERE v.ativo = 1
     ORDER BY s.nome_completo"
)->fetchAll();

$funcoes = $pdo->query('SELECT id_funcao, descricao FROM funcoes_legislativas ORDER BY id_funcao')->fetchAll();
$tiposComissao = $pdo->query('SELECT id_tipo_comissao, descricao FROM tipos_comissao ORDER BY descricao')->fetchAll();

$membros = [];
if ($idLegislatura) {
    $stmt = $pdo->prepare(
        'SELECT cm.id_comissao, cm.observacao, s.nome_completo, v.apelido, f.descricao AS funcao_descricao,
                tc.descricao AS tipo_comissao_descricao
         FROM comissoes_membros cm
         JOIN vereadores v ON v.id_vereador = cm.id_vereador
         JOIN servidores s ON s.id_servidor = v.id_servidor
         LEFT JOIN funcoes_legislativas f ON f.id_funcao = cm.id_funcao
         LEFT JOIN tipos_comissao tc ON tc.id_tipo_comissao = cm.id_tipo_comissao
         WHERE cm.id_legislatura = :leg AND cm.ativo = 1
         ORDER BY tc.descricao, cm.id_funcao'
    );
    $stmt->execute(['leg' => $idLegislatura]);
    $membros = $stmt->fetchAll();
}

// agrupa os membros por comissão, para exibição organizada
$porComissao = [];
foreach ($membros as $m) {
    $chave = $m['tipo_comissao_descricao'] ?: 'Sem comissão definida';
    $porComissao[$chave][] = $m;
}

include __DIR__ . '/includes/header.php';
?>

<h1 class="pagina__titulo">Comissões</h1>
<p class="pagina__subtitulo">Composição das comissões permanentes por legislatura (biênio).</p>

<?php if (!empty($_GET['ok'])): ?>
  <div class="alerta alerta-sucesso" style="max-width:none;">
    <i class="fa-solid fa-circle-check"></i> Comissão atualizada com sucesso.
  </div>
<?php endif; ?>

<div class="toolbar">
  <form class="toolbar__busca" method="get" style="max-width:320px;">
    <select name="legislatura" onchange="this.form.submit()">
      <?php foreach ($legislaturas as $l): ?>
        <option value="<?= $l['id_legislatura'] ?>" <?= (string) $idLegislatura === (string) $l['id_legislatura'] ? 'selected' : '' ?>>
          Legislatura <?= htmlspecialchars($l['descricao']) ?> (mandato <?= htmlspecialchars($l['mandato_descricao']) ?>)
        </option>
      <?php endforeach; ?>
    </select>
  </form>
</div>

<?php if (empty($porComissao)): ?>
  <div class="painel">
    <p style="color:var(--texto-claro); font-size:.85rem;">Nenhum membro de comissão cadastrado nesta legislatura ainda.</p>
  </div>
<?php else: ?>
  <?php foreach ($porComissao as $nomeComissao => $membrosComissao): ?>
    <div class="painel" style="margin-bottom:1.25rem;">
      <h2 class="painel__titulo"><i class="fa-solid fa-sitemap"></i> <?= htmlspecialchars($nomeComissao) ?></h2>
      <table class="tabela-simples">
        <thead>
          <tr>
            <th>Vereador</th>
            <th>Função na comissão</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($membrosComissao as $m): ?>
            <tr>
              <td class="titulo-linha"><?= htmlspecialchars($m['nome_completo']) ?><?= $m['apelido'] ? ' ("' . htmlspecialchars($m['apelido']) . '")' : '' ?></td>
              <td><?= htmlspecialchars($m['funcao_descricao'] ?: '—') ?></td>
              <td>
                <form method="post" action="comissao-excluir.php" onsubmit="return confirm('Remover este membro da comissão?');" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $m['id_comissao'] ?>">
                  <input type="hidden" name="legislatura" value="<?= $idLegislatura ?>">
                  <button type="submit" class="btn-icone perigo" title="Remover"><i class="fa-solid fa-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<div class="painel">
  <h2 class="painel__titulo"><i class="fa-solid fa-plus"></i> Adicionar membro a uma comissão</h2>

  <?php if (empty($vereadoresAtivos)): ?>
    <p style="color:var(--texto-claro); font-size:.85rem;">Cadastre vereadores primeiro.</p>
  <?php elseif ($idLegislatura): ?>
    <form method="post" action="comissao-salvar.php">
      <input type="hidden" name="id_legislatura" value="<?= $idLegislatura ?>">
      <div class="form-grid">
        <div class="campo">
          <label for="id_tipo_comissao">Comissão</label>
          <select id="id_tipo_comissao" name="id_tipo_comissao" required>
            <option value="">Selecione...</option>
            <?php foreach ($tiposComissao as $tc): ?>
              <option value="<?= $tc['id_tipo_comissao'] ?>"><?= htmlspecialchars($tc['descricao']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="campo">
          <label for="id_vereador">Vereador</label>
          <select id="id_vereador" name="id_vereador" required>
            <option value="">Selecione...</option>
            <?php foreach ($vereadoresAtivos as $v): ?>
              <option value="<?= $v['id_vereador'] ?>"><?= htmlspecialchars($v['nome_completo']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="campo">
          <label for="id_funcao">Função na comissão</label>
          <select id="id_funcao" name="id_funcao">
            <option value="">Membro</option>
            <?php foreach ($funcoes as $f): ?>
              <option value="<?= $f['id_funcao'] ?>"><?= htmlspecialchars($f['descricao']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="form-acoes">
        <button type="submit" class="btn btn-secundario btn-sm" style="width:auto;">
          <i class="fa-solid fa-plus"></i> Adicionar à comissão
        </button>
      </div>
    </form>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
