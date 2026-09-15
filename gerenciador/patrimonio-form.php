<?php
require_once __DIR__ . '/includes/auth.php';

$situacoes = ['em_uso' => 'Em uso', 'manutencao' => 'Em manutenção', 'baixado' => 'Baixado'];

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$editando = $id !== null;

$p = ['codigo' => '', 'descricao' => '', 'categoria' => '', 'data_aquisicao' => '', 'valor_aquisicao' => '', 'situacao' => 'em_uso', 'localizacao' => '', 'status' => 1];
$erros = [];

if ($editando) {
    $stmt = $pdo->prepare('SELECT * FROM patrimonio WHERE id_patrimonio = :id');
    $stmt->execute(['id' => $id]);
    $reg = $stmt->fetch();
    if (!$reg) { header('Location: patrimonio.php'); exit; }
    $p = $reg;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['codigo', 'descricao', 'categoria', 'localizacao'] as $c) {
        $p[$c] = trim($_POST[$c] ?? '');
    }
    $p['data_aquisicao']  = $_POST['data_aquisicao'] ?? '';
    $p['valor_aquisicao'] = $_POST['valor_aquisicao'] !== '' ? (float) str_replace(',', '.', $_POST['valor_aquisicao']) : null;
    $p['situacao']        = $_POST['situacao'] ?? 'em_uso';
    $p['status']          = isset($_POST['status']) ? 1 : 0;

    if ($p['codigo'] === '') $erros[] = 'Informe o código do bem.';
    if ($p['descricao'] === '') $erros[] = 'Informe a descrição do bem.';
    if (!isset($situacoes[$p['situacao']])) $erros[] = 'Situação inválida.';

    if (empty($erros)) {
        $params = [
            'codigo' => $p['codigo'], 'descricao' => $p['descricao'], 'categoria' => $p['categoria'] ?: null,
            'data_aquisicao' => $p['data_aquisicao'] ?: null, 'valor_aquisicao' => $p['valor_aquisicao'],
            'situacao' => $p['situacao'], 'localizacao' => $p['localizacao'] ?: null, 'status' => $p['status'],
        ];
        if ($editando) {
            $params['id'] = $id;
            $pdo->prepare('UPDATE patrimonio SET codigo=:codigo, descricao=:descricao, categoria=:categoria, data_aquisicao=:data_aquisicao, valor_aquisicao=:valor_aquisicao, situacao=:situacao, localizacao=:localizacao, status=:status WHERE id_patrimonio=:id')->execute($params);
        } else {
            $pdo->prepare('INSERT INTO patrimonio (codigo, descricao, categoria, data_aquisicao, valor_aquisicao, situacao, localizacao, status) VALUES (:codigo, :descricao, :categoria, :data_aquisicao, :valor_aquisicao, :situacao, :localizacao, :status)')->execute($params);
        }
        header('Location: patrimonio.php?ok=1');
        exit;
    }
}

include __DIR__ . '/includes/header.php';
?>
<h1 class="pagina__titulo"><?= $editando ? 'Editar item' : 'Novo item' ?></h1>
<p class="pagina__subtitulo"><a href="patrimonio.php">← Voltar para a lista</a></p>

<?php if (!empty($erros)): ?>
  <div class="alerta alerta-erro" style="max-width:none;"><i class="fa-solid fa-triangle-exclamation"></i> <?= implode('<br>', array_map('htmlspecialchars', $erros)) ?></div>
<?php endif; ?>

<form class="painel" method="post">
  <div class="form-grid">
    <div class="campo">
      <label for="codigo">Código *</label>
      <input type="text" id="codigo" name="codigo" maxlength="30" required placeholder="ex: PAT-0006" value="<?= htmlspecialchars($p['codigo']) ?>">
    </div>
    <div class="campo campo-largo">
      <label for="descricao">Descrição *</label>
      <input type="text" id="descricao" name="descricao" maxlength="200" required value="<?= htmlspecialchars($p['descricao']) ?>">
    </div>
    <div class="campo">
      <label for="categoria">Categoria</label>
      <input type="text" id="categoria" name="categoria" maxlength="80" placeholder="ex: Equipamento de Informática" value="<?= htmlspecialchars($p['categoria'] ?? '') ?>">
    </div>
    <div class="campo">
      <label for="data_aquisicao">Data de aquisição</label>
      <input type="date" id="data_aquisicao" name="data_aquisicao" value="<?= htmlspecialchars($p['data_aquisicao'] ?? '') ?>">
    </div>
    <div class="campo">
      <label for="valor_aquisicao">Valor de aquisição (R$)</label>
      <input type="text" id="valor_aquisicao" name="valor_aquisicao" placeholder="0,00" value="<?= htmlspecialchars($p['valor_aquisicao'] !== null ? number_format((float) $p['valor_aquisicao'], 2, ',', '') : '') ?>">
    </div>
    <div class="campo">
      <label for="situacao">Situação *</label>
      <select id="situacao" name="situacao">
        <?php foreach ($situacoes as $slug => $nome): ?>
          <option value="<?= $slug ?>" <?= $p['situacao'] === $slug ? 'selected' : '' ?>><?= $nome ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="campo">
      <label for="localizacao">Localização</label>
      <input type="text" id="localizacao" name="localizacao" maxlength="150" value="<?= htmlspecialchars($p['localizacao'] ?? '') ?>">
      <div class="campo-checkbox">
        <input type="checkbox" id="status" name="status" <?= $p['status'] ? 'checked' : '' ?>>
        <label for="status">Ativo (visível no portal)</label>
      </div>
    </div>
  </div>
  <div class="form-acoes">
    <button type="submit" class="btn btn-primario" style="width:auto;"><i class="fa-solid fa-floppy-disk"></i> Salvar</button>
    <a href="patrimonio.php" class="btn btn-secundario">Cancelar</a>
  </div>
</form>
<?php include __DIR__ . '/includes/footer.php'; ?>
