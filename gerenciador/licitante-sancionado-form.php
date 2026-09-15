<?php
require_once __DIR__ . '/includes/auth.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$editando = $id !== null;

$s = ['nome' => '', 'cnpj_cpf' => '', 'tipo_sancao' => '', 'numero_processo' => '', 'fundamento_legal' => '', 'data_inicio' => '', 'data_fim' => '', 'status' => 1];
$erros = [];

if ($editando) {
    $stmt = $pdo->prepare('SELECT * FROM licitantes_sancionados WHERE id_sancao = :id');
    $stmt->execute(['id' => $id]);
    $reg = $stmt->fetch();
    if (!$reg) { header('Location: licitantes-sancionados.php'); exit; }
    $s = $reg;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['nome', 'cnpj_cpf', 'tipo_sancao', 'numero_processo', 'fundamento_legal'] as $c) {
        $s[$c] = trim($_POST[$c] ?? '');
    }
    $s['data_inicio'] = $_POST['data_inicio'] ?? '';
    $s['data_fim']    = $_POST['data_fim'] ?? '';
    $s['status']      = isset($_POST['status']) ? 1 : 0;

    if ($s['nome'] === '') $erros[] = 'Informe o nome/razão social.';
    if ($s['tipo_sancao'] === '') $erros[] = 'Informe o tipo de sanção.';

    if (empty($erros)) {
        $params = [
            'nome' => $s['nome'], 'cnpj_cpf' => $s['cnpj_cpf'] ?: null, 'tipo_sancao' => $s['tipo_sancao'],
            'numero_processo' => $s['numero_processo'] ?: null, 'fundamento_legal' => $s['fundamento_legal'] ?: null,
            'data_inicio' => $s['data_inicio'] ?: null, 'data_fim' => $s['data_fim'] ?: null, 'status' => $s['status'],
        ];
        if ($editando) {
            $params['id'] = $id;
            $pdo->prepare('UPDATE licitantes_sancionados SET nome=:nome, cnpj_cpf=:cnpj_cpf, tipo_sancao=:tipo_sancao, numero_processo=:numero_processo, fundamento_legal=:fundamento_legal, data_inicio=:data_inicio, data_fim=:data_fim, status=:status WHERE id_sancao=:id')->execute($params);
        } else {
            $pdo->prepare('INSERT INTO licitantes_sancionados (nome, cnpj_cpf, tipo_sancao, numero_processo, fundamento_legal, data_inicio, data_fim, status) VALUES (:nome, :cnpj_cpf, :tipo_sancao, :numero_processo, :fundamento_legal, :data_inicio, :data_fim, :status)')->execute($params);
        }
        header('Location: licitantes-sancionados.php?ok=1');
        exit;
    }
}

include __DIR__ . '/includes/header.php';
?>
<h1 class="pagina__titulo"><?= $editando ? 'Editar registro' : 'Novo registro' ?></h1>
<p class="pagina__subtitulo"><a href="licitantes-sancionados.php">← Voltar para a lista</a></p>

<?php if (!empty($erros)): ?>
  <div class="alerta alerta-erro" style="max-width:none;"><i class="fa-solid fa-triangle-exclamation"></i> <?= implode('<br>', array_map('htmlspecialchars', $erros)) ?></div>
<?php endif; ?>

<form class="painel" method="post">
  <div class="form-grid">
    <div class="campo campo-largo">
      <label for="nome">Nome / Razão Social *</label>
      <input type="text" id="nome" name="nome" maxlength="200" required value="<?= htmlspecialchars($s['nome']) ?>">
    </div>
    <div class="campo">
      <label for="cnpj_cpf">CNPJ/CPF</label>
      <input type="text" id="cnpj_cpf" name="cnpj_cpf" maxlength="20" value="<?= htmlspecialchars($s['cnpj_cpf'] ?? '') ?>">
    </div>
    <div class="campo">
      <label for="tipo_sancao">Tipo de sanção *</label>
      <input type="text" id="tipo_sancao" name="tipo_sancao" maxlength="100" required placeholder="ex: Impedimento de licitar e contratar" value="<?= htmlspecialchars($s['tipo_sancao']) ?>">
    </div>
    <div class="campo">
      <label for="numero_processo">Nº do processo</label>
      <input type="text" id="numero_processo" name="numero_processo" maxlength="50" value="<?= htmlspecialchars($s['numero_processo'] ?? '') ?>">
    </div>
    <div class="campo campo-largo">
      <label for="fundamento_legal">Fundamento legal</label>
      <input type="text" id="fundamento_legal" name="fundamento_legal" maxlength="150" placeholder="ex: Art. 155, Lei nº 14.133/2021" value="<?= htmlspecialchars($s['fundamento_legal'] ?? '') ?>">
    </div>
    <div class="campo">
      <label for="data_inicio">Início da sanção</label>
      <input type="date" id="data_inicio" name="data_inicio" value="<?= htmlspecialchars($s['data_inicio'] ?? '') ?>">
    </div>
    <div class="campo">
      <label for="data_fim">Fim da sanção</label>
      <input type="date" id="data_fim" name="data_fim" value="<?= htmlspecialchars($s['data_fim'] ?? '') ?>">
      <div class="campo-checkbox">
        <input type="checkbox" id="status" name="status" <?= $s['status'] ? 'checked' : '' ?>>
        <label for="status">Ativo (visível no portal)</label>
      </div>
    </div>
  </div>
  <div class="form-acoes">
    <button type="submit" class="btn btn-primario" style="width:auto;"><i class="fa-solid fa-floppy-disk"></i> Salvar</button>
    <a href="licitantes-sancionados.php" class="btn btn-secundario">Cancelar</a>
  </div>
</form>
<?php include __DIR__ . '/includes/footer.php'; ?>
