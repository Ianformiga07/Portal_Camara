<?php
require_once __DIR__ . '/includes/auth.php';

$categorias = [
    'cargos-comissionados' => 'Cargos Comissionados',
    'tabela-remuneratoria' => 'Tabela Remuneratória',
    'folha-servidores'     => 'Folha — Servidores',
    'folha-estagiarios'    => 'Folha — Estagiários',
    'terceirizados'        => 'Terceirizados',
];

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$editando = $id !== null;

$registro = [
    'categoria'   => 'cargos-comissionados',
    'referencia'  => '',
    'descricao'   => '',
    'quantidade'  => '',
    'valor'       => '',
    'competencia' => '',
    'status'      => 1,
];
$erros = [];

if ($editando) {
    $stmt = $pdo->prepare('SELECT * FROM rh_informacoes WHERE id_rh = :id');
    $stmt->execute(['id' => $id]);
    $reg = $stmt->fetch();
    if (!$reg) {
        header('Location: rh-informacoes.php');
        exit;
    }
    $registro = $reg;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $registro['categoria']   = $_POST['categoria'] ?? 'cargos-comissionados';
    $registro['referencia']  = trim($_POST['referencia'] ?? '');
    $registro['descricao']   = trim($_POST['descricao'] ?? '');
    $registro['quantidade']  = $_POST['quantidade'] !== '' ? (int) $_POST['quantidade'] : null;
    $registro['valor']       = $_POST['valor'] !== '' ? (float) str_replace(',', '.', $_POST['valor']) : null;
    $registro['competencia'] = trim($_POST['competencia'] ?? '');
    $registro['status']      = isset($_POST['status']) ? 1 : 0;

    if (!isset($categorias[$registro['categoria']])) {
        $erros[] = 'Categoria inválida.';
    }
    if ($registro['referencia'] === '') {
        $erros[] = 'Informe o cargo/referência.';
    }

    if (empty($erros)) {
        if ($editando) {
            $stmt = $pdo->prepare(
                'UPDATE rh_informacoes SET categoria=:categoria, referencia=:referencia, descricao=:descricao,
                    quantidade=:quantidade, valor=:valor, competencia=:competencia, status=:status
                 WHERE id_rh=:id'
            );
            $stmt->execute([
                'categoria'   => $registro['categoria'],
                'referencia'  => $registro['referencia'],
                'descricao'   => $registro['descricao'] ?: null,
                'quantidade'  => $registro['quantidade'],
                'valor'       => $registro['valor'],
                'competencia' => $registro['competencia'] ?: null,
                'status'      => $registro['status'],
                'id'          => $id,
            ]);
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO rh_informacoes (categoria, referencia, descricao, quantidade, valor, competencia, status)
                 VALUES (:categoria, :referencia, :descricao, :quantidade, :valor, :competencia, :status)'
            );
            $stmt->execute([
                'categoria'   => $registro['categoria'],
                'referencia'  => $registro['referencia'],
                'descricao'   => $registro['descricao'] ?: null,
                'quantidade'  => $registro['quantidade'],
                'valor'       => $registro['valor'],
                'competencia' => $registro['competencia'] ?: null,
                'status'      => $registro['status'],
            ]);
        }
        header('Location: rh-informacoes.php?ok=1');
        exit;
    }
}

include __DIR__ . '/includes/header.php';
?>

<h1 class="pagina__titulo"><?= $editando ? 'Editar registro' : 'Novo registro' ?></h1>
<p class="pagina__subtitulo"><a href="rh-informacoes.php">← Voltar para a lista</a></p>

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
      <label for="referencia">Cargo / Referência *</label>
      <input type="text" id="referencia" name="referencia" maxlength="150" required value="<?= htmlspecialchars($registro['referencia']) ?>" placeholder="ex: Chefe de Gabinete">
    </div>

    <div class="campo campo-largo">
      <label for="descricao">Detalhe</label>
      <input type="text" id="descricao" name="descricao" maxlength="255" value="<?= htmlspecialchars($registro['descricao'] ?? '') ?>" placeholder="ex: CC-1, empresa terceirizada, etc.">
    </div>

    <div class="campo">
      <label for="quantidade">Quantidade</label>
      <input type="number" id="quantidade" name="quantidade" min="0" value="<?= htmlspecialchars((string) ($registro['quantidade'] ?? '')) ?>">
    </div>

    <div class="campo">
      <label for="valor">Valor (R$)</label>
      <input type="text" id="valor" name="valor" placeholder="0,00" value="<?= htmlspecialchars($registro['valor'] !== null ? number_format((float) $registro['valor'], 2, ',', '') : '') ?>">
    </div>

    <div class="campo">
      <label for="competencia">Competência</label>
      <input type="text" id="competencia" name="competencia" maxlength="20" placeholder="ex: 08/2026" value="<?= htmlspecialchars($registro['competencia'] ?? '') ?>">
      <small class="ajuda">Só se aplica a folha de pagamento e terceirizados.</small>
      <div class="campo-checkbox">
        <input type="checkbox" id="status" name="status" <?= $registro['status'] ? 'checked' : '' ?>>
        <label for="status">Ativo (visível no portal)</label>
      </div>
    </div>
  </div>

  <div class="form-acoes">
    <button type="submit" class="btn btn-primario" style="width:auto;"><i class="fa-solid fa-floppy-disk"></i> Salvar</button>
    <a href="rh-informacoes.php" class="btn btn-secundario">Cancelar</a>
  </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
