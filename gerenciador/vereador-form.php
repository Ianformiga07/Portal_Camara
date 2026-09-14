<?php
require_once __DIR__ . '/includes/auth.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$editando = $id !== null;
$tituloPagina = $editando ? 'Editar vereador' : 'Novo vereador';

$mandatos = $pdo->query('SELECT id_mandato, descricao FROM mandatos_eletivos ORDER BY id_mandato DESC')->fetchAll();
$mandatosAnteriores = [];

$dados = [
    'nome_completo'   => '',
    'cpf'             => '',
    'data_nascimento' => '',
    'sexo'            => '',
    'foto_perfil'     => '',
    'apelido'         => '',
    'partido'         => '',
    'ocupacao'        => '',
    'id_mandato'      => '',
    'ativo'           => 1,
];
$idServidor = null;
$erros = [];

if ($editando) {
    $stmt = $pdo->prepare(
        'SELECT v.id_vereador, v.id_servidor, v.apelido, v.partido, v.ocupacao, v.id_mandato, v.ativo,
                s.nome_completo, s.cpf, s.data_nascimento, s.sexo, s.foto_perfil
         FROM vereadores v
         JOIN servidores s ON s.id_servidor = v.id_servidor
         WHERE v.id_vereador = :id'
    );
    $stmt->execute(['id' => $id]);
    $registro = $stmt->fetch();
    if (!$registro) {
        header('Location: vereadores.php');
        exit;
    }
    $dados = $registro;
    $idServidor = $registro['id_servidor'];

    $stmtAnt = $pdo->prepare(
        'SELECT ma.id, ma.ano_inicio, ma.ano_fim, m.descricao AS mandato_descricao
         FROM vereadores_mandatos_anteriores ma
         JOIN mandatos_eletivos m ON m.id_mandato = ma.id_mandato
         WHERE ma.id_vereador = :id
         ORDER BY ma.ano_inicio DESC'
    );
    $stmtAnt->execute(['id' => $id]);
    $mandatosAnteriores = $stmtAnt->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados['nome_completo']   = trim($_POST['nome_completo'] ?? '');
    $dados['cpf']             = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
    $dados['data_nascimento'] = $_POST['data_nascimento'] ?: null;
    $dados['sexo']            = $_POST['sexo'] ?: null;
    $dados['apelido']         = trim($_POST['apelido'] ?? '');
    $dados['partido']         = trim($_POST['partido'] ?? '');
    $dados['ocupacao']        = trim($_POST['ocupacao'] ?? '');
    $dados['id_mandato']      = $_POST['id_mandato'] ?: null;
    $dados['ativo']           = isset($_POST['ativo']) ? 1 : 0;

    if ($dados['nome_completo'] === '') {
        $erros[] = 'O nome completo é obrigatório.';
    }
    if (strlen($dados['cpf']) !== 11) {
        $erros[] = 'Informe um CPF válido (11 dígitos).';
    } else {
        $stmtCpf = $pdo->prepare('SELECT id_servidor FROM servidores WHERE cpf = :cpf AND id_servidor != :id_atual');
        $stmtCpf->execute(['cpf' => $dados['cpf'], 'id_atual' => $idServidor ?? 0]);
        if ($stmtCpf->fetch()) {
            $erros[] = 'Já existe um servidor cadastrado com esse CPF.';
        }
    }

    // --- Upload de foto (opcional) ---
    $novaFoto = null;
    if (!empty($_FILES['foto']['name'])) {
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
        $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $tamanhoMaximo = 4 * 1024 * 1024;

        if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            $erros[] = 'Falha ao enviar a foto. Tente novamente.';
        } elseif (!in_array($extensao, $extensoesPermitidas, true)) {
            $erros[] = 'Formato de imagem inválido. Use JPG, PNG ou WEBP.';
        } elseif ($_FILES['foto']['size'] > $tamanhoMaximo) {
            $erros[] = 'A foto deve ter no máximo 4MB.';
        } else {
            $novaFoto = 'vereador_' . uniqid() . '.' . $extensao;
            $pastaDestino = __DIR__ . '/assets/uploads/vereadores/';
            if (!is_dir($pastaDestino)) {
                mkdir($pastaDestino, 0755, true);
            }
            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $pastaDestino . $novaFoto)) {
                $erros[] = 'Não foi possível salvar a foto no servidor.';
                $novaFoto = null;
            }
        }
    }

    if (empty($erros)) {
        if ($novaFoto) {
            if ($editando && !empty($dados['foto_perfil'])) {
                @unlink(__DIR__ . '/assets/uploads/vereadores/' . $dados['foto_perfil']);
            }
            $dados['foto_perfil'] = $novaFoto;
        }

        try {
            $pdo->beginTransaction();

            if ($editando) {
                $pdo->prepare(
                    'UPDATE servidores SET nome_completo=:nome_completo, cpf=:cpf, data_nascimento=:data_nascimento,
                        sexo=:sexo, foto_perfil=:foto_perfil
                     WHERE id_servidor=:id_servidor'
                )->execute([
                    'nome_completo'   => $dados['nome_completo'],
                    'cpf'             => $dados['cpf'],
                    'data_nascimento' => $dados['data_nascimento'],
                    'sexo'            => $dados['sexo'],
                    'foto_perfil'     => $dados['foto_perfil'] ?: null,
                    'id_servidor'     => $idServidor,
                ]);

                $pdo->prepare(
                    'UPDATE vereadores SET apelido=:apelido, partido=:partido, ocupacao=:ocupacao,
                        id_mandato=:id_mandato, ativo=:ativo
                     WHERE id_vereador=:id'
                )->execute([
                    'apelido'    => $dados['apelido'] ?: null,
                    'partido'    => $dados['partido'] ?: null,
                    'ocupacao'   => $dados['ocupacao'] ?: null,
                    'id_mandato' => $dados['id_mandato'],
                    'ativo'      => $dados['ativo'],
                    'id'         => $id,
                ]);
            } else {
                $idCargoVereador = $pdo->query("SELECT id_cargo FROM cargos WHERE descricao = 'Vereador' LIMIT 1")->fetchColumn() ?: null;

                $pdo->prepare(
                    'INSERT INTO servidores (cpf, nome_completo, data_nascimento, sexo, foto_perfil, id_cargo, ativo)
                     VALUES (:cpf, :nome_completo, :data_nascimento, :sexo, :foto_perfil, :id_cargo, 1)'
                )->execute([
                    'cpf'             => $dados['cpf'],
                    'nome_completo'   => $dados['nome_completo'],
                    'data_nascimento' => $dados['data_nascimento'],
                    'sexo'            => $dados['sexo'],
                    'foto_perfil'     => $dados['foto_perfil'] ?: null,
                    'id_cargo'        => $idCargoVereador,
                ]);
                $novoIdServidor = $pdo->lastInsertId();

                $pdo->prepare(
                    'INSERT INTO vereadores (id_servidor, apelido, partido, ocupacao, id_mandato, ativo)
                     VALUES (:id_servidor, :apelido, :partido, :ocupacao, :id_mandato, :ativo)'
                )->execute([
                    'id_servidor' => $novoIdServidor,
                    'apelido'     => $dados['apelido'] ?: null,
                    'partido'     => $dados['partido'] ?: null,
                    'ocupacao'    => $dados['ocupacao'] ?: null,
                    'id_mandato'  => $dados['id_mandato'],
                    'ativo'       => $dados['ativo'],
                ]);
            }

            $pdo->commit();
            header('Location: vereadores.php?ok=1');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $erros[] = 'Não foi possível salvar. Tente novamente.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<h1 class="pagina__titulo"><?= $editando ? 'Editar vereador' : 'Novo vereador' ?></h1>
<p class="pagina__subtitulo"><a href="vereadores.php">← Voltar para a lista</a></p>

<?php if (!empty($erros)): ?>
  <div class="alerta alerta-erro" style="max-width:none;">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <?= implode('<br>', array_map('htmlspecialchars', $erros)) ?>
  </div>
<?php endif; ?>

<form class="painel" method="post" enctype="multipart/form-data">
  <h2 class="painel__titulo"><i class="fa-solid fa-id-card"></i> Dados pessoais</h2>
  <div class="form-grid">
    <div class="campo campo-largo">
      <label for="nome_completo">Nome completo *</label>
      <input type="text" id="nome_completo" name="nome_completo" maxlength="150" required value="<?= htmlspecialchars($dados['nome_completo']) ?>">
    </div>

    <div class="campo">
      <label for="cpf">CPF *</label>
      <input type="text" id="cpf" name="cpf" maxlength="14" required placeholder="000.000.000-00" value="<?= htmlspecialchars($dados['cpf']) ?>">
    </div>

    <div class="campo">
      <label for="data_nascimento">Data de nascimento</label>
      <input type="date" id="data_nascimento" name="data_nascimento" value="<?= htmlspecialchars($dados['data_nascimento'] ?? '') ?>">
    </div>

    <div class="campo">
      <label for="sexo">Sexo</label>
      <select id="sexo" name="sexo">
        <option value="">Não informado</option>
        <option value="M" <?= ($dados['sexo'] ?? '') === 'M' ? 'selected' : '' ?>>Masculino</option>
        <option value="F" <?= ($dados['sexo'] ?? '') === 'F' ? 'selected' : '' ?>>Feminino</option>
      </select>
    </div>

    <div class="campo">
      <label for="foto">Foto</label>
      <div class="upload-preview" id="preview">
        <?php if (!empty($dados['foto_perfil'])): ?>
          <img src="assets/uploads/vereadores/<?= htmlspecialchars($dados['foto_perfil']) ?>" alt="">
        <?php else: ?>
          <span>Nenhuma foto selecionada</span>
        <?php endif; ?>
      </div>
      <input type="file" id="foto" name="foto" accept=".jpg,.jpeg,.png,.webp">
      <small class="ajuda">JPG, PNG ou WEBP, até 4MB.</small>
    </div>
  </div>

  <h2 class="painel__titulo" style="margin-top:1.75rem;"><i class="fa-solid fa-landmark"></i> Dados do mandato</h2>
  <div class="form-grid">
    <div class="campo">
      <label for="apelido">Nome parlamentar / apelido</label>
      <input type="text" id="apelido" name="apelido" maxlength="100" value="<?= htmlspecialchars($dados['apelido'] ?? '') ?>">
    </div>

    <div class="campo">
      <label for="partido">Partido</label>
      <input type="text" id="partido" name="partido" maxlength="50" value="<?= htmlspecialchars($dados['partido'] ?? '') ?>">
    </div>

    <div class="campo">
      <label for="ocupacao">Ocupação / profissão</label>
      <input type="text" id="ocupacao" name="ocupacao" maxlength="100" value="<?= htmlspecialchars($dados['ocupacao'] ?? '') ?>">
    </div>

    <div class="campo">
      <label for="id_mandato">Mandato</label>
      <select id="id_mandato" name="id_mandato">
        <option value="">Selecione...</option>
        <?php foreach ($mandatos as $m): ?>
          <option value="<?= $m['id_mandato'] ?>" <?= (string) ($dados['id_mandato'] ?? '') === (string) $m['id_mandato'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($m['descricao']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="campo campo-largo">
      <div class="campo-checkbox">
        <input type="checkbox" id="ativo" name="ativo" <?= $dados['ativo'] ? 'checked' : '' ?>>
        <label for="ativo">Em exercício (visível no portal)</label>
      </div>
    </div>
  </div>

  <div class="form-acoes">
    <button type="submit" class="btn btn-primario" style="width:auto;">
      <i class="fa-solid fa-floppy-disk"></i> Salvar vereador
    </button>
    <a href="vereadores.php" class="btn btn-secundario">Cancelar</a>
  </div>
</form>

<?php if ($editando): ?>
  <div class="painel" style="margin-top:1.25rem;">
    <h2 class="painel__titulo"><i class="fa-solid fa-clock-rotate-left"></i> Mandatos anteriores</h2>
    <p class="pagina__subtitulo" style="margin-bottom:1rem;">Use esta seção para registrar mandatos anteriores deste vereador, além do mandato atual definido acima.</p>

    <?php if (empty($mandatosAnteriores)): ?>
      <p style="color:var(--texto-claro); font-size:.85rem; margin-bottom:1.25rem;">Nenhum mandato anterior cadastrado.</p>
    <?php else: ?>
      <table class="tabela-simples" style="margin-bottom:1.25rem;">
        <thead>
          <tr>
            <th>Mandato</th>
            <th>Início</th>
            <th>Fim</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($mandatosAnteriores as $ma): ?>
            <tr>
              <td><?= htmlspecialchars($ma['mandato_descricao']) ?></td>
              <td><?= htmlspecialchars($ma['ano_inicio']) ?></td>
              <td><?= htmlspecialchars($ma['ano_fim']) ?></td>
              <td>
                <form method="post" action="vereador-mandato-anterior-excluir.php" onsubmit="return confirm('Remover este mandato anterior?');" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $ma['id'] ?>">
                  <input type="hidden" name="id_vereador" value="<?= $id ?>">
                  <button type="submit" class="btn-icone perigo" title="Remover"><i class="fa-solid fa-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <form method="post" action="vereador-mandato-anterior-salvar.php">
      <input type="hidden" name="id_vereador" value="<?= $id ?>">
      <div class="form-grid">
        <div class="campo">
          <label for="ant_id_mandato">Mandato</label>
          <select id="ant_id_mandato" name="id_mandato" required>
            <option value="">Selecione...</option>
            <?php foreach ($mandatos as $m): ?>
              <option value="<?= $m['id_mandato'] ?>"><?= htmlspecialchars($m['descricao']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="campo">
          <label for="ant_ano_inicio">Ano de início</label>
          <input type="number" id="ant_ano_inicio" name="ano_inicio" min="1980" max="2100" required placeholder="ex: 2013">
        </div>
        <div class="campo">
          <label for="ant_ano_fim">Ano de fim</label>
          <input type="number" id="ant_ano_fim" name="ano_fim" min="1980" max="2100" required placeholder="ex: 2016">
        </div>
      </div>
      <div class="form-acoes">
        <button type="submit" class="btn btn-secundario btn-sm" style="width:auto;">
          <i class="fa-solid fa-plus"></i> Adicionar mandato anterior
        </button>
      </div>
    </form>
  </div>
<?php endif; ?>

<script>
  document.getElementById('cpf').addEventListener('input', function (e) {
    let v = e.target.value.replace(/\D/g, '').slice(0, 11);
    v = v.replace(/(\d{3})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    e.target.value = v;
  });

  document.getElementById('foto').addEventListener('change', function (e) {
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
