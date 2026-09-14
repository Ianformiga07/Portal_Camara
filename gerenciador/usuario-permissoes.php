<?php
require_once __DIR__ . '/includes/auth.php';

if (!ehAdministrador()) {
    header('Location: index.php');
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
$tituloPagina = 'Definir acesso';

$stmt = $pdo->prepare('SELECT id_servidor, nome_completo, nivel_acesso, senha_hash FROM servidores WHERE id_servidor = :id');
$stmt->execute(['id' => $id]);
$servidor = $stmt->fetch();

if (!$servidor) {
    header('Location: usuarios.php');
    exit;
}

$modulos = $pdo->query('SELECT id_modulo, descricao FROM modulos_sistema ORDER BY id_modulo')->fetchAll();

$stmtPerm = $pdo->prepare('SELECT id_modulo FROM permissoes_acesso WHERE id_servidor = :id');
$stmtPerm->execute(['id' => $id]);
$permissoesAtuais = array_column($stmtPerm->fetchAll(), 'id_modulo');

$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nivelAcesso = (int) ($_POST['nivel_acesso'] ?? 3);
    $novaSenha   = $_POST['nova_senha'] ?? '';
    $confirmar   = $_POST['confirmar_senha'] ?? '';
    $modulosMarcados = $_POST['modulos'] ?? [];

    if ($novaSenha !== '' || $confirmar !== '') {
        if (strlen($novaSenha) < 6) {
            $erros[] = 'A nova senha deve ter pelo menos 6 caracteres.';
        } elseif ($novaSenha !== $confirmar) {
            $erros[] = 'A confirmação de senha não confere.';
        }
    } elseif (!$servidor['senha_hash']) {
        $erros[] = 'Defina uma senha inicial para que este servidor consiga acessar o sistema.';
    }

    if (empty($erros)) {
        if ($novaSenha !== '') {
            $pdo->prepare('UPDATE servidores SET nivel_acesso=:nivel, senha_hash=:senha WHERE id_servidor=:id')
                ->execute([
                    'nivel' => $nivelAcesso,
                    'senha' => password_hash($novaSenha, PASSWORD_BCRYPT),
                    'id'    => $id,
                ]);
        } else {
            $pdo->prepare('UPDATE servidores SET nivel_acesso=:nivel WHERE id_servidor=:id')
                ->execute(['nivel' => $nivelAcesso, 'id' => $id]);
        }

        // sincroniza as permissões por módulo (apaga e reinsere as marcadas)
        $pdo->prepare('DELETE FROM permissoes_acesso WHERE id_servidor = :id')->execute(['id' => $id]);
        $stmtInsere = $pdo->prepare('INSERT INTO permissoes_acesso (id_servidor, id_modulo) VALUES (:id_servidor, :id_modulo)');
        foreach ($modulosMarcados as $idModulo) {
            $stmtInsere->execute(['id_servidor' => $id, 'id_modulo' => (int) $idModulo]);
        }

        header('Location: usuarios.php?ok=1');
        exit;
    }
}

include __DIR__ . '/includes/header.php';
?>

<h1 class="pagina__titulo">Acesso de <?= htmlspecialchars($servidor['nome_completo']) ?></h1>
<p class="pagina__subtitulo"><a href="usuarios.php">← Voltar para a lista</a></p>

<?php if (!empty($erros)): ?>
  <div class="alerta alerta-erro" style="max-width:none;">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <?= implode('<br>', array_map('htmlspecialchars', $erros)) ?>
  </div>
<?php endif; ?>

<form class="painel" method="post">
  <h2 class="painel__titulo"><i class="fa-solid fa-user-lock"></i> Nível de acesso e senha</h2>
  <div class="form-grid">
    <div class="campo">
      <label for="nivel_acesso">Nível de acesso</label>
      <select id="nivel_acesso" name="nivel_acesso">
        <option value="1" <?= (int) $servidor['nivel_acesso'] === 1 ? 'selected' : '' ?>>Admin Master (acesso total)</option>
        <option value="2" <?= (int) $servidor['nivel_acesso'] === 2 ? 'selected' : '' ?>>Admin (gerencia usuários e conteúdo)</option>
        <option value="3" <?= (int) $servidor['nivel_acesso'] === 3 ? 'selected' : '' ?>>Operador (só os módulos liberados abaixo)</option>
      </select>
    </div>
    <div class="campo">
      <label for="nova_senha"><?= $servidor['senha_hash'] ? 'Nova senha' : 'Senha inicial *' ?></label>
      <input type="password" id="nova_senha" name="nova_senha" placeholder="Mínimo 6 caracteres">
    </div>
    <div class="campo">
      <label for="confirmar_senha">Confirmar senha</label>
      <input type="password" id="confirmar_senha" name="confirmar_senha">
    </div>
  </div>
  <?php if ($servidor['senha_hash']): ?>
    <small class="ajuda">Deixe os campos de senha em branco para manter a senha atual.</small>
  <?php endif; ?>

  <h2 class="painel__titulo" style="margin-top:1.75rem;"><i class="fa-solid fa-list-check"></i> Módulos liberados</h2>
  <p class="pagina__subtitulo" style="margin-bottom:1rem;">Relevante apenas para o nível "Operador". Admin Master e Admin já têm acesso a tudo.</p>
  <div class="form-grid">
    <?php foreach ($modulos as $m): ?>
      <div class="campo-checkbox">
        <input type="checkbox" id="modulo_<?= $m['id_modulo'] ?>" name="modulos[]" value="<?= $m['id_modulo'] ?>" <?= in_array($m['id_modulo'], $permissoesAtuais) ? 'checked' : '' ?>>
        <label for="modulo_<?= $m['id_modulo'] ?>"><?= htmlspecialchars($m['descricao']) ?></label>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="form-acoes">
    <button type="submit" class="btn btn-primario" style="width:auto;">
      <i class="fa-solid fa-floppy-disk"></i> Salvar acesso
    </button>
    <a href="usuarios.php" class="btn btn-secundario">Cancelar</a>
  </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
