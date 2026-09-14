<?php
require_once __DIR__ . '/includes/auth.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$editando = $id !== null;
$tituloPagina = $editando ? 'Editar servidor' : 'Novo servidor';

$cargos = $pdo->query('SELECT id_cargo, descricao FROM cargos ORDER BY descricao')->fetchAll();
$departamentos = $pdo->query('SELECT id_departamento, descricao FROM departamentos ORDER BY descricao')->fetchAll();
$escolaridades = $pdo->query('SELECT id_escolaridade, descricao FROM escolaridades ORDER BY id_escolaridade')->fetchAll();

$servidor = [
    'nome_completo' => '', 'cpf' => '', 'data_nascimento' => '', 'sexo' => '', 'foto_perfil' => '',
    'id_cargo' => '', 'id_departamento' => '', 'id_escolaridade' => '', 'matricula' => '',
    'data_admissao' => '', 'celular' => '', 'email' => '', 'ativo' => 1,
];
$erros = [];

if ($editando) {
    $stmt = $pdo->prepare('SELECT * FROM servidores WHERE id_servidor = :id');
    $stmt->execute(['id' => $id]);
    $registro = $stmt->fetch();
    if (!$registro) {
        header('Location: servidores.php');
        exit;
    }
    $servidor = $registro;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $servidor['nome_completo']   = trim($_POST['nome_completo'] ?? '');
    $servidor['cpf']             = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
    $servidor['data_nascimento'] = $_POST['data_nascimento'] ?: null;
    $servidor['sexo']            = $_POST['sexo'] ?: null;
    $servidor['id_cargo']        = $_POST['id_cargo'] ?: null;
    $servidor['id_departamento'] = $_POST['id_departamento'] ?: null;
    $servidor['id_escolaridade'] = $_POST['id_escolaridade'] ?: null;
    $servidor['matricula']       = trim($_POST['matricula'] ?? '');
    $servidor['data_admissao']   = $_POST['data_admissao'] ?: null;
    $servidor['celular']         = trim($_POST['celular'] ?? '');
    $servidor['email']           = trim($_POST['email'] ?? '');
    $servidor['ativo']           = isset($_POST['ativo']) ? 1 : 0;

    if ($servidor['nome_completo'] === '') {
        $erros[] = 'O nome completo é obrigatório.';
    }
    if (strlen($servidor['cpf']) !== 11) {
        $erros[] = 'Informe um CPF válido (11 dígitos).';
    } else {
        $stmtCpf = $pdo->prepare('SELECT id_servidor FROM servidores WHERE cpf = :cpf AND id_servidor != :id_atual');
        $stmtCpf->execute(['cpf' => $servidor['cpf'], 'id_atual' => $id ?? 0]);
        if ($stmtCpf->fetch()) {
            $erros[] = 'Já existe um servidor cadastrado com esse CPF.';
        }
    }

    $novaFoto = null;
    if (!empty($_FILES['foto_perfil']['name'])) {
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
        $extensao = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
        $tamanhoMaximo = 4 * 1024 * 1024;

        if ($_FILES['foto_perfil']['error'] !== UPLOAD_ERR_OK) {
            $erros[] = 'Falha ao enviar a foto. Tente novamente.';
        } elseif (!in_array($extensao, $extensoesPermitidas, true)) {
            $erros[] = 'Formato de imagem inválido. Use JPG, PNG ou WEBP.';
        } elseif ($_FILES['foto_perfil']['size'] > $tamanhoMaximo) {
            $erros[] = 'A foto deve ter no máximo 4MB.';
        } else {
            $novaFoto = 'servidor_' . uniqid() . '.' . $extensao;
            $pastaDestino = __DIR__ . '/assets/uploads/servidores/';
            if (!is_dir($pastaDestino)) {
                mkdir($pastaDestino, 0755, true);
            }
            if (!move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $pastaDestino . $novaFoto)) {
                $erros[] = 'Não foi possível salvar a foto no servidor.';
                $novaFoto = null;
            }
        }
    }

    if (empty($erros)) {
        if ($novaFoto) {
            if ($editando && !empty($servidor['foto_perfil'])) {
                @unlink(__DIR__ . '/assets/uploads/servidores/' . $servidor['foto_perfil']);
            }
            $servidor['foto_perfil'] = $novaFoto;
        }

        $parametros = [
            'nome_completo'   => $servidor['nome_completo'], 'cpf' => $servidor['cpf'],
            'data_nascimento' => $servidor['data_nascimento'], 'sexo' => $servidor['sexo'],
            'foto_perfil'     => $servidor['foto_perfil'] ?: null, 'id_cargo' => $servidor['id_cargo'],
            'id_departamento' => $servidor['id_departamento'], 'id_escolaridade' => $servidor['id_escolaridade'],
            'matricula'       => $servidor['matricula'] ?: null, 'data_admissao' => $servidor['data_admissao'],
            'celular'         => $servidor['celular'] ?: null, 'email' => $servidor['email'] ?: null,
            'ativo'           => $servidor['ativo'],
        ];

        if ($editando) {
            $parametros['id'] = $id;
            $pdo->prepare(
                'UPDATE servidores SET nome_completo=:nome_completo, cpf=:cpf, data_nascimento=:data_nascimento,
                    sexo=:sexo, foto_perfil=:foto_perfil, id_cargo=:id_cargo, id_departamento=:id_departamento,
                    id_escolaridade=:id_escolaridade, matricula=:matricula, data_admissao=:data_admissao,
                    celular=:celular, email=:email, ativo=:ativo
                 WHERE id_servidor=:id'
            )->execute($parametros);
        } else {
            $pdo->prepare(
                'INSERT INTO servidores (nome_completo, cpf, data_nascimento, sexo, foto_perfil, id_cargo,
                    id_departamento, id_escolaridade, matricula, data_admissao, celular, email, ativo)
                 VALUES (:nome_completo, :cpf, :data_nascimento, :sexo, :foto_perfil, :id_cargo,
                    :id_departamento, :id_escolaridade, :matricula, :data_admissao, :celular, :email, :ativo)'
            )->execute($parametros);
        }
        header('Location: servidores.php?ok=1');
        exit;
    }
}

include __DIR__ . '/includes/header.php';
?>

<h1 class="pagina__titulo"><?= $editando ? 'Editar servidor' : 'Novo servidor' ?></h1>
<p class="pagina__subtitulo"><a href="servidores.php">← Voltar para a lista</a></p>

<?php if (!empty($erros)): ?>
  <div class="alerta alerta-erro" style="max-width:none;">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <?= implode('<br>', array_map('htmlspecialchars', $erros)) ?>
  </div>
<?php endif; ?>

<?php if ($editando && ehAdministrador()): ?>
  <p class="pagina__subtitulo" style="margin-bottom:1rem;">
    <a href="usuario-permissoes.php?id=<?= $id ?>"><i class="fa-solid fa-key"></i> Definir acesso ao sistema (senha e permissões) deste servidor</a>
  </p>
<?php endif; ?>

<form class="painel" method="post" enctype="multipart/form-data">
  <h2 class="painel__titulo"><i class="fa-solid fa-id-card"></i> Dados pessoais</h2>
  <div class="form-grid">
    <div class="campo campo-largo">
      <label for="nome_completo">Nome completo *</label>
      <input type="text" id="nome_completo" name="nome_completo" maxlength="150" required value="<?= htmlspecialchars($servidor['nome_completo']) ?>">
    </div>

    <div class="campo">
      <label for="cpf">CPF *</label>
      <input type="text" id="cpf" name="cpf" maxlength="14" required placeholder="000.000.000-00" value="<?= htmlspecialchars($servidor['cpf']) ?>">
    </div>

    <div class="campo">
      <label for="data_nascimento">Data de nascimento</label>
      <input type="date" id="data_nascimento" name="data_nascimento" value="<?= htmlspecialchars($servidor['data_nascimento'] ?? '') ?>">
    </div>

    <div class="campo">
      <label for="sexo">Sexo</label>
      <select id="sexo" name="sexo">
        <option value="">Não informado</option>
        <option value="M" <?= ($servidor['sexo'] ?? '') === 'M' ? 'selected' : '' ?>>Masculino</option>
        <option value="F" <?= ($servidor['sexo'] ?? '') === 'F' ? 'selected' : '' ?>>Feminino</option>
      </select>
    </div>

    <div class="campo">
      <label for="celular">Celular</label>
      <input type="text" id="celular" name="celular" maxlength="15" value="<?= htmlspecialchars($servidor['celular'] ?? '') ?>">
    </div>

    <div class="campo">
      <label for="email">E-mail</label>
      <input type="email" id="email" name="email" maxlength="100" value="<?= htmlspecialchars($servidor['email'] ?? '') ?>">
    </div>

    <div class="campo campo-largo">
      <label for="foto_perfil">Foto</label>
      <div class="upload-preview" id="preview">
        <?php if (!empty($servidor['foto_perfil'])): ?>
          <img src="assets/uploads/servidores/<?= htmlspecialchars($servidor['foto_perfil']) ?>" alt="">
        <?php else: ?>
          <span>Nenhuma foto selecionada</span>
        <?php endif; ?>
      </div>
      <input type="file" id="foto_perfil" name="foto_perfil" accept=".jpg,.jpeg,.png,.webp">
      <small class="ajuda">JPG, PNG ou WEBP, até 4MB.</small>
    </div>
  </div>

  <h2 class="painel__titulo" style="margin-top:1.75rem;"><i class="fa-solid fa-briefcase"></i> Dados funcionais</h2>
  <div class="form-grid">
    <div class="campo">
      <label for="id_cargo">Cargo</label>
      <select id="id_cargo" name="id_cargo">
        <option value="">Selecione...</option>
        <?php foreach ($cargos as $c): ?>
          <option value="<?= $c['id_cargo'] ?>" <?= (string) ($servidor['id_cargo'] ?? '') === (string) $c['id_cargo'] ? 'selected' : '' ?>><?= htmlspecialchars($c['descricao']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="campo">
      <label for="id_departamento">Departamento</label>
      <select id="id_departamento" name="id_departamento">
        <option value="">Selecione...</option>
        <?php foreach ($departamentos as $d): ?>
          <option value="<?= $d['id_departamento'] ?>" <?= (string) ($servidor['id_departamento'] ?? '') === (string) $d['id_departamento'] ? 'selected' : '' ?>><?= htmlspecialchars($d['descricao']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="campo">
      <label for="id_escolaridade">Escolaridade</label>
      <select id="id_escolaridade" name="id_escolaridade">
        <option value="">Selecione...</option>
        <?php foreach ($escolaridades as $e): ?>
          <option value="<?= $e['id_escolaridade'] ?>" <?= (string) ($servidor['id_escolaridade'] ?? '') === (string) $e['id_escolaridade'] ? 'selected' : '' ?>><?= htmlspecialchars($e['descricao']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="campo">
      <label for="matricula">Matrícula</label>
      <input type="text" id="matricula" name="matricula" maxlength="20" value="<?= htmlspecialchars($servidor['matricula'] ?? '') ?>">
    </div>

    <div class="campo">
      <label for="data_admissao">Data de admissão</label>
      <input type="date" id="data_admissao" name="data_admissao" value="<?= htmlspecialchars($servidor['data_admissao'] ?? '') ?>">
    </div>

    <div class="campo campo-largo">
      <div class="campo-checkbox">
        <input type="checkbox" id="ativo" name="ativo" <?= $servidor['ativo'] ? 'checked' : '' ?>>
        <label for="ativo">Servidor ativo</label>
      </div>
    </div>
  </div>

  <div class="form-acoes">
    <button type="submit" class="btn btn-primario" style="width:auto;">
      <i class="fa-solid fa-floppy-disk"></i> Salvar servidor
    </button>
    <a href="servidores.php" class="btn btn-secundario">Cancelar</a>
  </div>
</form>

<script>
  document.getElementById('cpf').addEventListener('input', function (e) {
    let v = e.target.value.replace(/\D/g, '').slice(0, 11);
    v = v.replace(/(\d{3})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    e.target.value = v;
  });

  document.getElementById('foto_perfil').addEventListener('change', function (e) {
    var arquivo = e.target.files[0];
    if (!arquivo) return;
    var preview = document.getElementById('preview');
    var leitor = new FileReader();
    leitor.onload = function (ev) {
      preview.innerHTML = '<img src="' + ev.target.result + '" alt="">';
    };
    leitor.readAsDataURL(arquivo);
  });
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
