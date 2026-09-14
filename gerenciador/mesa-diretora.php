<?php
require_once __DIR__ . '/includes/auth.php';

$tituloPagina = 'Mesa Diretora';

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

$membros = [];
if ($idLegislatura) {
    $stmt = $pdo->prepare(
        'SELECT md.id, md.observacao, s.nome_completo, v.apelido, v.partido, f.descricao AS funcao_descricao
         FROM mesa_diretora md
         JOIN vereadores v ON v.id_vereador = md.id_vereador
         JOIN servidores s ON s.id_servidor = v.id_servidor
         LEFT JOIN funcoes_legislativas f ON f.id_funcao = md.id_funcao
         WHERE md.id_legislatura = :leg AND md.ativo = 1
         ORDER BY md.id_funcao'
    );
    $stmt->execute(['leg' => $idLegislatura]);
    $membros = $stmt->fetchAll();
}

include __DIR__ . '/includes/header.php';
?>

<h1 class="pagina__titulo">Mesa Diretora</h1>
<p class="pagina__subtitulo">Composição da Mesa Diretora por legislatura (biênio).</p>

<?php if (!empty($_GET['ok'])): ?>
  <div class="alerta alerta-sucesso" style="max-width:none;">
    <i class="fa-solid fa-circle-check"></i> Mesa Diretora atualizada com sucesso.
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

<div class="painel">
  <h2 class="painel__titulo"><i class="fa-solid fa-people-roof"></i> Composição atual</h2>

  <?php if (empty($membros)): ?>
    <p style="color:var(--texto-claro); font-size:.85rem; margin-bottom:1.25rem;">Nenhum membro cadastrado nesta legislatura ainda.</p>
  <?php else: ?>
    <table class="tabela-simples" style="margin-bottom:1.25rem;">
      <thead>
        <tr>
          <th>Função</th>
          <th>Vereador</th>
          <th>Partido</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($membros as $m): ?>
          <tr>
            <td class="titulo-linha"><?= htmlspecialchars($m['funcao_descricao'] ?: '—') ?></td>
            <td><?= htmlspecialchars($m['nome_completo']) ?><?= $m['apelido'] ? ' ("' . htmlspecialchars($m['apelido']) . '")' : '' ?></td>
            <td><?= htmlspecialchars($m['partido'] ?: '—') ?></td>
            <td>
              <form method="post" action="mesa-diretora-excluir.php" onsubmit="return confirm('Remover este membro da Mesa Diretora?');" style="display:inline;">
                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                <input type="hidden" name="legislatura" value="<?= $idLegislatura ?>">
                <button type="submit" class="btn-icone perigo" title="Remover"><i class="fa-solid fa-trash"></i></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <?php if (empty($vereadoresAtivos)): ?>
    <p style="color:var(--texto-claro); font-size:.85rem;">Cadastre vereadores primeiro para poder compor a Mesa Diretora.</p>
  <?php elseif ($idLegislatura): ?>
    <form method="post" action="mesa-diretora-salvar.php">
      <input type="hidden" name="id_legislatura" value="<?= $idLegislatura ?>">
      <div class="form-grid">
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
          <label for="id_funcao">Função</label>
          <select id="id_funcao" name="id_funcao" required>
            <option value="">Selecione...</option>
            <?php foreach ($funcoes as $f): ?>
              <option value="<?= $f['id_funcao'] ?>"><?= htmlspecialchars($f['descricao']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="campo campo-largo">
          <label for="observacao">Observação</label>
          <input type="text" id="observacao" name="observacao" maxlength="200">
        </div>
      </div>
      <div class="form-acoes">
        <button type="submit" class="btn btn-secundario btn-sm" style="width:auto;">
          <i class="fa-solid fa-plus"></i> Adicionar à Mesa Diretora
        </button>
      </div>
    </form>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
