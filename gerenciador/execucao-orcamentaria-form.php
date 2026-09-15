<?php
require_once __DIR__ . '/includes/auth.php';

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

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$editando = $id !== null;

$registro = [
    'categoria'       => 'empenhos',
    'numero'          => '',
    'descricao'       => '',
    'favorecido'      => '',
    'data_referencia' => date('Y-m-d'),
    'ano'             => (int) date('Y'),
    'valor'           => '',
    'status'          => 1,
];
$erros = [];

if ($editando) {
    $stmt = $pdo->prepare('SELECT * FROM execucao_orcamentaria WHERE id_execucao = :id');
    $stmt->execute(['id' => $id]);
    $reg = $stmt->fetch();
    if (!$reg) {
        header('Location: execucao-orcamentaria.php');
        exit;
    }
    $registro = $reg;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $registro['categoria']       = $_POST['categoria'] ?? 'empenhos';
    $registro['numero']          = trim($_POST['numero'] ?? '');
    $registro['descricao']       = trim($_POST['descricao'] ?? '');
    $registro['favorecido']      = trim($_POST['favorecido'] ?? '');
    $registro['data_referencia'] = $_POST['data_referencia'] ?? '';
    $registro['ano']             = (int) ($_POST['ano'] ?? date('Y'));
    $registro['valor']           = (float) str_replace(',', '.', $_POST['valor'] ?? '0');
    $registro['status']          = isset($_POST['status']) ? 1 : 0;

    if (!isset($categorias[$registro['categoria']])) {
        $erros[] = 'Categoria inválida.';
    }
    if ($registro['descricao'] === '') {
        $erros[] = 'Informe a descrição do lançamento.';
    }
    if ($registro['ano'] < 2000 || $registro['ano'] > 2100) {
        $erros[] = 'Ano inválido.';
    }

    if (empty($erros)) {
        if ($editando) {
            $stmt = $pdo->prepare(
                'UPDATE execucao_orcamentaria SET categoria=:categoria, numero=:numero, descricao=:descricao,
                    favorecido=:favorecido, data_referencia=:data_referencia, ano=:ano, valor=:valor, status=:status
                 WHERE id_execucao=:id'
            );
            $stmt->execute([
                'categoria'       => $registro['categoria'],
                'numero'          => $registro['numero'] ?: null,
                'descricao'       => $registro['descricao'],
                'favorecido'      => $registro['favorecido'] ?: null,
                'data_referencia' => $registro['data_referencia'] ?: null,
                'ano'             => $registro['ano'],
                'valor'           => $registro['valor'],
                'status'          => $registro['status'],
                'id'              => $id,
            ]);
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO execucao_orcamentaria (categoria, numero, descricao, favorecido, data_referencia, ano, valor, status)
                 VALUES (:categoria, :numero, :descricao, :favorecido, :data_referencia, :ano, :valor, :status)'
            );
            $stmt->execute([
                'categoria'       => $registro['categoria'],
                'numero'          => $registro['numero'] ?: null,
                'descricao'       => $registro['descricao'],
                'favorecido'      => $registro['favorecido'] ?: null,
                'data_referencia' => $registro['data_referencia'] ?: null,
                'ano'             => $registro['ano'],
                'valor'           => $registro['valor'],
                'status'          => $registro['status'],
            ]);
        }
        header('Location: execucao-orcamentaria.php?ok=1');
        exit;
    }
}

include __DIR__ . '/includes/header.php';
?>

<h1 class="pagina__titulo"><?= $editando ? 'Editar lançamento' : 'Novo lançamento' ?></h1>
<p class="pagina__subtitulo"><a href="execucao-orcamentaria.php">← Voltar para a lista</a></p>

<?php if (!empty($erros)): ?>
  <div class="alerta alerta-erro" style="max-width:none;">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <?= implode('<br>', array_map('htmlspecialchars', $erros)) ?>
  </div>
<?php endif; ?>

<form class="painel" method="post">
  <div class="form-grid">
    <div class="campo">
      <label for="categoria">Categoria *</label>
      <select id="categoria" name="categoria">
        <?php foreach ($categorias as $slug => $nome): ?>
          <option value="<?= $slug ?>" <?= $registro['categoria'] === $slug ? 'selected' : '' ?>><?= htmlspecialchars($nome) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="campo">
      <label for="numero">Número</label>
      <input type="text" id="numero" name="numero" maxlength="30" placeholder="ex: 0112/2026" value="<?= htmlspecialchars($registro['numero'] ?? '') ?>">
    </div>

    <div class="campo campo-largo">
      <label for="descricao">Descrição *</label>
      <input type="text" id="descricao" name="descricao" maxlength="255" required value="<?= htmlspecialchars($registro['descricao']) ?>">
    </div>

    <div class="campo">
      <label for="favorecido">Favorecido</label>
      <input type="text" id="favorecido" name="favorecido" maxlength="150" value="<?= htmlspecialchars($registro['favorecido'] ?? '') ?>">
    </div>

    <div class="campo">
      <label for="data_referencia">Data</label>
      <input type="date" id="data_referencia" name="data_referencia" value="<?= htmlspecialchars($registro['data_referencia'] ?? '') ?>">
    </div>

    <div class="campo">
      <label for="ano">Ano *</label>
      <input type="number" id="ano" name="ano" min="2000" max="2100" required value="<?= (int) $registro['ano'] ?>">
    </div>

    <div class="campo">
      <label for="valor">Valor (R$) *</label>
      <input type="text" id="valor" name="valor" placeholder="0,00" required value="<?= htmlspecialchars(number_format((float) $registro['valor'], 2, ',', '')) ?>">
      <div class="campo-checkbox">
        <input type="checkbox" id="status" name="status" <?= $registro['status'] ? 'checked' : '' ?>>
        <label for="status">Ativo (visível no portal)</label>
      </div>
    </div>
  </div>

  <div class="form-acoes">
    <button type="submit" class="btn btn-primario" style="width:auto;"><i class="fa-solid fa-floppy-disk"></i> Salvar</button>
    <a href="execucao-orcamentaria.php" class="btn btn-secundario">Cancelar</a>
  </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
