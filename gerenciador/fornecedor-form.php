<?php
require_once __DIR__ . '/includes/auth.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$editando = $id !== null;
$tituloPagina = $editando ? 'Editar fornecedor' : 'Novo fornecedor';

$fornecedor = [
    'razao_social'      => '',
    'nome_fantasia'     => '',
    'cnpj_cpf'          => '',
    'ativo'             => 1,
    'motivo_inativacao' => '',
];
$erros = [];

if ($editando) {
    $stmt = $pdo->prepare('SELECT * FROM fornecedores WHERE id_fornecedor = :id');
    $stmt->execute(['id' => $id]);
    $registro = $stmt->fetch();
    if (!$registro) {
        header('Location: fornecedores.php');
        exit;
    }
    $fornecedor = $registro;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fornecedor['razao_social']      = trim($_POST['razao_social'] ?? '');
    $fornecedor['nome_fantasia']     = trim($_POST['nome_fantasia'] ?? '');
    $fornecedor['cnpj_cpf']          = trim($_POST['cnpj_cpf'] ?? '');
    $fornecedor['ativo']             = isset($_POST['ativo']) ? 1 : 0;
    $fornecedor['motivo_inativacao'] = trim($_POST['motivo_inativacao'] ?? '');

    if ($fornecedor['razao_social'] === '') {
        $erros[] = 'A razão social é obrigatória.';
    }
    if (!$fornecedor['ativo'] && $fornecedor['motivo_inativacao'] === '') {
        $erros[] = 'Informe o motivo da inativação.';
    }

    if (empty($erros)) {
        $parametros = [
            'razao_social'      => $fornecedor['razao_social'],
            'nome_fantasia'     => $fornecedor['nome_fantasia'] ?: null,
            'cnpj_cpf'          => $fornecedor['cnpj_cpf'] ?: null,
            'ativo'             => $fornecedor['ativo'],
            'motivo_inativacao' => $fornecedor['ativo'] ? null : $fornecedor['motivo_inativacao'],
        ];

        if ($editando) {
            $parametros['id'] = $id;
            $pdo->prepare(
                'UPDATE fornecedores SET razao_social=:razao_social, nome_fantasia=:nome_fantasia,
                    cnpj_cpf=:cnpj_cpf, ativo=:ativo, motivo_inativacao=:motivo_inativacao
                 WHERE id_fornecedor=:id'
            )->execute($parametros);
        } else {
            $pdo->prepare(
                'INSERT INTO fornecedores (razao_social, nome_fantasia, cnpj_cpf, ativo, motivo_inativacao)
                 VALUES (:razao_social, :nome_fantasia, :cnpj_cpf, :ativo, :motivo_inativacao)'
            )->execute($parametros);
        }
        header('Location: fornecedores.php?ok=1');
        exit;
    }
}

include __DIR__ . '/includes/header.php';
?>

<h1 class="pagina__titulo"><?= $editando ? 'Editar fornecedor' : 'Novo fornecedor' ?></h1>
<p class="pagina__subtitulo"><a href="fornecedores.php">← Voltar para a lista</a></p>

<?php if (!empty($erros)): ?>
  <div class="alerta alerta-erro" style="max-width:none;">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <?= implode('<br>', array_map('htmlspecialchars', $erros)) ?>
  </div>
<?php endif; ?>

<form class="painel" method="post">
  <div class="form-grid">
    <div class="campo campo-largo">
      <label for="razao_social">Razão social *</label>
      <input type="text" id="razao_social" name="razao_social" maxlength="150" required value="<?= htmlspecialchars($fornecedor['razao_social']) ?>">
    </div>

    <div class="campo">
      <label for="nome_fantasia">Nome fantasia</label>
      <input type="text" id="nome_fantasia" name="nome_fantasia" maxlength="150" value="<?= htmlspecialchars($fornecedor['nome_fantasia'] ?? '') ?>">
    </div>

    <div class="campo">
      <label for="cnpj_cpf">CNPJ / CPF</label>
      <input type="text" id="cnpj_cpf" name="cnpj_cpf" maxlength="20" value="<?= htmlspecialchars($fornecedor['cnpj_cpf'] ?? '') ?>">
    </div>

    <div class="campo campo-largo">
      <div class="campo-checkbox">
        <input type="checkbox" id="ativo" name="ativo" <?= $fornecedor['ativo'] ? 'checked' : '' ?> onchange="document.getElementById('bloco-motivo').style.display = this.checked ? 'none' : 'block';">
        <label for="ativo">Fornecedor ativo</label>
      </div>
    </div>

    <div class="campo campo-largo" id="bloco-motivo" style="display:<?= $fornecedor['ativo'] ? 'none' : 'block' ?>;">
      <label for="motivo_inativacao">Motivo da inativação</label>
      <input type="text" id="motivo_inativacao" name="motivo_inativacao" maxlength="255" value="<?= htmlspecialchars($fornecedor['motivo_inativacao'] ?? '') ?>">
    </div>
  </div>

  <div class="form-acoes">
    <button type="submit" class="btn btn-primario" style="width:auto;">
      <i class="fa-solid fa-floppy-disk"></i> Salvar fornecedor
    </button>
    <a href="fornecedores.php" class="btn btn-secundario">Cancelar</a>
  </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
