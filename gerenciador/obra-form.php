<?php
require_once __DIR__ . '/includes/auth.php';

$situacoes = ['planejada' => 'Planejada', 'andamento' => 'Em andamento', 'concluida' => 'Concluída', 'paralisada' => 'Paralisada'];

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$editando = $id !== null;

$o = ['nome' => '', 'descricao' => '', 'empresa_responsavel' => '', 'valor' => '', 'situacao' => 'planejada', 'percentual_execucao' => 0, 'data_inicio' => '', 'previsao_termino' => '', 'localizacao' => '', 'status' => 1];
$erros = [];

if ($editando) {
    $stmt = $pdo->prepare('SELECT * FROM obras WHERE id_obra = :id');
    $stmt->execute(['id' => $id]);
    $reg = $stmt->fetch();
    if (!$reg) { header('Location: obras.php'); exit; }
    $o = $reg;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['nome', 'descricao', 'empresa_responsavel', 'localizacao'] as $c) {
        $o[$c] = trim($_POST[$c] ?? '');
    }
    $o['valor']               = $_POST['valor'] !== '' ? (float) str_replace(',', '.', $_POST['valor']) : null;
    $o['situacao']            = $_POST['situacao'] ?? 'planejada';
    $o['percentual_execucao'] = max(0, min(100, (int) ($_POST['percentual_execucao'] ?? 0)));
    $o['data_inicio']         = $_POST['data_inicio'] ?? '';
    $o['previsao_termino']    = $_POST['previsao_termino'] ?? '';
    $o['status']              = isset($_POST['status']) ? 1 : 0;

    if ($o['nome'] === '') $erros[] = 'Informe o nome da obra.';
    if (!isset($situacoes[$o['situacao']])) $erros[] = 'Situação inválida.';

    if (empty($erros)) {
        $params = [
            'nome' => $o['nome'], 'descricao' => $o['descricao'] ?: null, 'empresa_responsavel' => $o['empresa_responsavel'] ?: null,
            'valor' => $o['valor'], 'situacao' => $o['situacao'], 'percentual_execucao' => $o['percentual_execucao'],
            'data_inicio' => $o['data_inicio'] ?: null, 'previsao_termino' => $o['previsao_termino'] ?: null,
            'localizacao' => $o['localizacao'] ?: null, 'status' => $o['status'],
        ];
        if ($editando) {
            $params['id'] = $id;
            $pdo->prepare('UPDATE obras SET nome=:nome, descricao=:descricao, empresa_responsavel=:empresa_responsavel, valor=:valor, situacao=:situacao, percentual_execucao=:percentual_execucao, data_inicio=:data_inicio, previsao_termino=:previsao_termino, localizacao=:localizacao, status=:status WHERE id_obra=:id')->execute($params);
        } else {
            $pdo->prepare('INSERT INTO obras (nome, descricao, empresa_responsavel, valor, situacao, percentual_execucao, data_inicio, previsao_termino, localizacao, status) VALUES (:nome, :descricao, :empresa_responsavel, :valor, :situacao, :percentual_execucao, :data_inicio, :previsao_termino, :localizacao, :status)')->execute($params);
        }
        header('Location: obras.php?ok=1');
        exit;
    }
}

include __DIR__ . '/includes/header.php';
?>
<h1 class="pagina__titulo"><?= $editando ? 'Editar obra' : 'Nova obra' ?></h1>
<p class="pagina__subtitulo"><a href="obras.php">← Voltar para a lista</a></p>

<?php if (!empty($erros)): ?>
  <div class="alerta alerta-erro" style="max-width:none;"><i class="fa-solid fa-triangle-exclamation"></i> <?= implode('<br>', array_map('htmlspecialchars', $erros)) ?></div>
<?php endif; ?>

<form class="painel" method="post">
  <div class="form-grid">
    <div class="campo campo-largo">
      <label for="nome">Nome da obra *</label>
      <input type="text" id="nome" name="nome" maxlength="200" required value="<?= htmlspecialchars($o['nome']) ?>">
    </div>
    <div class="campo campo-largo">
      <label for="descricao">Descrição</label>
      <textarea id="descricao" name="descricao" rows="3"><?= htmlspecialchars($o['descricao'] ?? '') ?></textarea>
    </div>
    <div class="campo">
      <label for="empresa_responsavel">Empresa responsável</label>
      <input type="text" id="empresa_responsavel" name="empresa_responsavel" maxlength="150" value="<?= htmlspecialchars($o['empresa_responsavel'] ?? '') ?>">
    </div>
    <div class="campo">
      <label for="valor">Valor (R$)</label>
      <input type="text" id="valor" name="valor" placeholder="0,00" value="<?= htmlspecialchars($o['valor'] !== null ? number_format((float) $o['valor'], 2, ',', '') : '') ?>">
    </div>
    <div class="campo">
      <label for="situacao">Situação *</label>
      <select id="situacao" name="situacao">
        <?php foreach ($situacoes as $slug => $nome): ?>
          <option value="<?= $slug ?>" <?= $o['situacao'] === $slug ? 'selected' : '' ?>><?= $nome ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="campo">
      <label for="percentual_execucao">% Executado</label>
      <input type="number" id="percentual_execucao" name="percentual_execucao" min="0" max="100" value="<?= (int) $o['percentual_execucao'] ?>">
    </div>
    <div class="campo">
      <label for="data_inicio">Data de início</label>
      <input type="date" id="data_inicio" name="data_inicio" value="<?= htmlspecialchars($o['data_inicio'] ?? '') ?>">
    </div>
    <div class="campo">
      <label for="previsao_termino">Previsão de término</label>
      <input type="date" id="previsao_termino" name="previsao_termino" value="<?= htmlspecialchars($o['previsao_termino'] ?? '') ?>">
    </div>
    <div class="campo">
      <label for="localizacao">Localização</label>
      <input type="text" id="localizacao" name="localizacao" maxlength="150" value="<?= htmlspecialchars($o['localizacao'] ?? '') ?>">
      <div class="campo-checkbox">
        <input type="checkbox" id="status" name="status" <?= $o['status'] ? 'checked' : '' ?>>
        <label for="status">Ativa (visível no portal)</label>
      </div>
    </div>
  </div>
  <div class="form-acoes">
    <button type="submit" class="btn btn-primario" style="width:auto;"><i class="fa-solid fa-floppy-disk"></i> Salvar</button>
    <a href="obras.php" class="btn btn-secundario">Cancelar</a>
  </div>
</form>
<?php include __DIR__ . '/includes/footer.php'; ?>
