<?php
require_once __DIR__ . '/includes/auth.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$editando = $id !== null;
$tituloPagina = $editando ? 'Editar compra' : 'Nova compra';

$modalidades = $pdo->query('SELECT id_modalidade, descricao FROM licitacao_modalidades ORDER BY descricao')->fetchAll();
$licitacoesDisponiveis = $pdo->query('SELECT id_licitacao, numero_processo, objeto FROM licitacoes ORDER BY id_licitacao DESC')->fetchAll();
$fornecedores = $pdo->query('SELECT id_fornecedor, razao_social FROM fornecedores WHERE ativo = 1 ORDER BY razao_social')->fetchAll();

$compra = [
    'id_modalidade' => '', 'id_licitacao' => '', 'id_fornecedor' => '',
    'data_compra' => date('Y-m-d'), 'tipo_compra' => '', 'descricao' => '',
    'valor_total' => '', 'valor_desconto' => '', 'valor_final' => '', 'status' => 1,
];
$erros = [];

if ($editando) {
    $stmt = $pdo->prepare('SELECT * FROM compras WHERE id_compra = :id');
    $stmt->execute(['id' => $id]);
    $registro = $stmt->fetch();
    if (!$registro) {
        header('Location: compras.php');
        exit;
    }
    $compra = $registro;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $compra['id_modalidade'] = $_POST['id_modalidade'] ?: null;
    $compra['id_licitacao']  = $_POST['id_licitacao'] ?: null;
    $compra['id_fornecedor'] = $_POST['id_fornecedor'] ?: null;
    $compra['data_compra']   = $_POST['data_compra'] ?: null;
    $compra['tipo_compra']   = trim($_POST['tipo_compra'] ?? '');
    $compra['descricao']     = trim($_POST['descricao'] ?? '');
    $compra['valor_total']   = $_POST['valor_total'] !== '' ? str_replace(',', '.', $_POST['valor_total']) : null;
    $compra['valor_desconto'] = $_POST['valor_desconto'] !== '' ? str_replace(',', '.', $_POST['valor_desconto']) : 0;
    $compra['status']        = isset($_POST['status']) ? 1 : 0;

    if ($compra['tipo_compra'] === '') {
        $erros[] = 'Informe o tipo/objeto da compra.';
    }
    if ($compra['valor_total'] === null) {
        $erros[] = 'Informe o valor total da compra.';
    }

    // valor final é sempre calculado no servidor, nunca confiado ao que veio do formulário
    $compra['valor_final'] = $compra['valor_total'] !== null
        ? max(0, (float) $compra['valor_total'] - (float) ($compra['valor_desconto'] ?: 0))
        : null;

    if (empty($erros)) {
        $parametros = [
            'id_modalidade'  => $compra['id_modalidade'], 'id_licitacao' => $compra['id_licitacao'],
            'id_fornecedor'  => $compra['id_fornecedor'], 'data_compra' => $compra['data_compra'],
            'tipo_compra'    => $compra['tipo_compra'], 'descricao' => $compra['descricao'] ?: null,
            'valor_total'    => $compra['valor_total'], 'valor_desconto' => $compra['valor_desconto'],
            'valor_final'    => $compra['valor_final'], 'status' => $compra['status'],
        ];

        if ($editando) {
            $parametros['id'] = $id;
            $pdo->prepare(
                'UPDATE compras SET id_modalidade=:id_modalidade, id_licitacao=:id_licitacao, id_fornecedor=:id_fornecedor,
                    data_compra=:data_compra, tipo_compra=:tipo_compra, descricao=:descricao, valor_total=:valor_total,
                    valor_desconto=:valor_desconto, valor_final=:valor_final, status=:status
                 WHERE id_compra=:id'
            )->execute($parametros);
        } else {
            $pdo->prepare(
                'INSERT INTO compras (id_modalidade, id_licitacao, id_fornecedor, data_compra, tipo_compra, descricao,
                    valor_total, valor_desconto, valor_final, status)
                 VALUES (:id_modalidade, :id_licitacao, :id_fornecedor, :data_compra, :tipo_compra, :descricao,
                    :valor_total, :valor_desconto, :valor_final, :status)'
            )->execute($parametros);
        }
        header('Location: compras.php?ok=1');
        exit;
    }
}

include __DIR__ . '/includes/header.php';

function selecionadoCompra($atual, $valor): string
{
    return (string) $atual === (string) $valor ? 'selected' : '';
}
?>

<h1 class="pagina__titulo"><?= $editando ? 'Editar compra' : 'Nova compra' ?></h1>
<p class="pagina__subtitulo"><a href="compras.php">← Voltar para a lista</a></p>

<?php if (!empty($erros)): ?>
  <div class="alerta alerta-erro" style="max-width:none;">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <?= implode('<br>', array_map('htmlspecialchars', $erros)) ?>
  </div>
<?php endif; ?>

<form class="painel" method="post">
  <div class="form-grid">
    <div class="campo campo-largo">
      <label for="tipo_compra">Tipo / objeto da compra *</label>
      <input type="text" id="tipo_compra" name="tipo_compra" maxlength="200" required placeholder="ex: Material de expediente" value="<?= htmlspecialchars($compra['tipo_compra']) ?>">
    </div>

    <div class="campo campo-largo">
      <label for="descricao">Descrição</label>
      <textarea id="descricao" name="descricao" rows="3"><?= htmlspecialchars($compra['descricao'] ?? '') ?></textarea>
    </div>

    <div class="campo">
      <label for="id_fornecedor">Fornecedor</label>
      <select id="id_fornecedor" name="id_fornecedor">
        <option value="">Selecione...</option>
        <?php foreach ($fornecedores as $f): ?>
          <option value="<?= $f['id_fornecedor'] ?>" <?= selecionadoCompra($compra['id_fornecedor'], $f['id_fornecedor']) ?>><?= htmlspecialchars($f['razao_social']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="campo">
      <label for="id_modalidade">Modalidade</label>
      <select id="id_modalidade" name="id_modalidade">
        <option value="">Selecione...</option>
        <?php foreach ($modalidades as $m): ?>
          <option value="<?= $m['id_modalidade'] ?>" <?= selecionadoCompra($compra['id_modalidade'], $m['id_modalidade']) ?>><?= htmlspecialchars($m['descricao']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="campo">
      <label for="id_licitacao">Licitação relacionada</label>
      <select id="id_licitacao" name="id_licitacao">
        <option value="">Não se aplica</option>
        <?php foreach ($licitacoesDisponiveis as $l): ?>
          <option value="<?= $l['id_licitacao'] ?>" <?= selecionadoCompra($compra['id_licitacao'], $l['id_licitacao']) ?>>
            <?= htmlspecialchars($l['numero_processo'] ?: ('#' . $l['id_licitacao'])) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="campo">
      <label for="data_compra">Data da compra</label>
      <input type="date" id="data_compra" name="data_compra" value="<?= htmlspecialchars($compra['data_compra'] ?? '') ?>">
    </div>

    <div class="campo">
      <label for="valor_total">Valor total (R$) *</label>
      <input type="text" id="valor_total" name="valor_total" placeholder="0,00" required value="<?= htmlspecialchars($compra['valor_total'] ?? '') ?>">
    </div>

    <div class="campo">
      <label for="valor_desconto">Desconto (R$)</label>
      <input type="text" id="valor_desconto" name="valor_desconto" placeholder="0,00" value="<?= htmlspecialchars($compra['valor_desconto'] ?? '') ?>">
    </div>

    <div class="campo">
      <label>Valor final (calculado)</label>
      <input type="text" id="valor_final_preview" disabled value="R$ <?= number_format((float) ($compra['valor_final'] ?: 0), 2, ',', '.') ?>">
    </div>

    <div class="campo campo-largo">
      <div class="campo-checkbox">
        <input type="checkbox" id="status" name="status" <?= $compra['status'] ? 'checked' : '' ?>>
        <label for="status">Publicada (visível no portal)</label>
      </div>
    </div>
  </div>

  <div class="form-acoes">
    <button type="submit" class="btn btn-primario" style="width:auto;">
      <i class="fa-solid fa-floppy-disk"></i> Salvar compra
    </button>
    <a href="compras.php" class="btn btn-secundario">Cancelar</a>
  </div>
</form>

<script>
  // apenas uma prévia visual - o valor final "de verdade" é sempre recalculado no servidor
  function atualizarPreviaValorFinal() {
    var total = parseFloat((document.getElementById('valor_total').value || '0').replace(',', '.')) || 0;
    var desconto = parseFloat((document.getElementById('valor_desconto').value || '0').replace(',', '.')) || 0;
    var final = Math.max(0, total - desconto);
    document.getElementById('valor_final_preview').value = 'R$ ' + final.toFixed(2).replace('.', ',');
  }
  document.getElementById('valor_total').addEventListener('input', atualizarPreviaValorFinal);
  document.getElementById('valor_desconto').addEventListener('input', atualizarPreviaValorFinal);
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
