<?php
$page_title = 'Ouvidoria — Câmara Municipal de Ananás';
require_once 'includes/funcoes.php';
include 'includes/header.php';

$tituloPaginaInterna = 'Ouvidoria';
$subtituloPaginaInterna = 'Registre denúncias, reclamações, elogios ou sugestões para a Câmara Municipal de Ananás.';

$tipos = $pdo ? $pdo->query('SELECT id_tipo_manifestacao, descricao FROM tipos_manifestacao ORDER BY descricao')->fetchAll() : [];
$erros = [];
$protocoloGerado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $anonimo = isset($_POST['anonimo']) ? 1 : 0;
    $idTipo  = $_POST['id_tipo_manifestacao'] ?: null;
    $nome    = trim($_POST['nome'] ?? '');
    $cpf     = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $texto   = trim($_POST['texto'] ?? '');

    if (!$idTipo) {
        $erros[] = 'Selecione o tipo de manifestação.';
    }
    if ($texto === '') {
        $erros[] = 'Descreva sua manifestação.';
    }
    if (!$anonimo && $nome === '') {
        $erros[] = 'Informe seu nome, ou marque a opção de manifestação anônima.';
    }

    // --- Anexo opcional ---
    $arquivo = null;
    if (!empty($_FILES['anexo']['name'])) {
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'pdf'];
        $extensao = strtolower(pathinfo($_FILES['anexo']['name'], PATHINFO_EXTENSION));
        $tamanhoMaximo = 5 * 1024 * 1024;

        if ($_FILES['anexo']['error'] !== UPLOAD_ERR_OK) {
            $erros[] = 'Falha ao enviar o anexo. Tente novamente.';
        } elseif (!in_array($extensao, $extensoesPermitidas, true)) {
            $erros[] = 'Formato de anexo inválido. Use JPG, PNG ou PDF.';
        } elseif ($_FILES['anexo']['size'] > $tamanhoMaximo) {
            $erros[] = 'O anexo deve ter no máximo 5MB.';
        } else {
            $arquivo = 'manifestacao_' . uniqid() . '.' . $extensao;
            $pastaDestino = __DIR__ . '/gerenciador/assets/uploads/manifestacoes/';
            if (!is_dir($pastaDestino)) {
                mkdir($pastaDestino, 0755, true);
            }
            if (!move_uploaded_file($_FILES['anexo']['tmp_name'], $pastaDestino . $arquivo)) {
                $erros[] = 'Não foi possível salvar o anexo no servidor.';
                $arquivo = null;
            }
        }
    }

    if (empty($erros) && $pdo) {
        $protocolo = gerarProtocoloUnico($pdo, 'manifestacoes', 'OUV');
        $pdo->prepare(
            'INSERT INTO manifestacoes (id_tipo_manifestacao, anonimo, forma_recebimento, nome, cpf, email, telefone, anexo, texto, protocolo)
             VALUES (:tipo, :anonimo, :forma, :nome, :cpf, :email, :telefone, :anexo, :texto, :protocolo)'
        )->execute([
            'tipo'     => $idTipo,
            'anonimo'  => $anonimo,
            'forma'    => 'Portal',
            'nome'     => $anonimo ? null : ($nome ?: null),
            'cpf'      => $anonimo ? null : ($cpf ?: null),
            'email'    => $email ?: null,
            'telefone' => $telefone ?: null,
            'anexo'    => $arquivo,
            'texto'    => $texto,
            'protocolo' => $protocolo,
        ]);
        $protocoloGerado = $protocolo;
    } elseif (empty($erros) && !$pdo) {
        $erros[] = 'Não foi possível registrar sua manifestação no momento. Tente novamente mais tarde.';
    }
}

include 'includes/pagina-header.php';
?>

<section class="pagina-interna-conteudo">
  <div class="section-container">

    <?php if ($protocoloGerado): ?>
      <div class="protocolo-box">
        <i class="fas fa-circle-check"></i>
        <h3>Manifestação registrada com sucesso!</h3>
        <p>Guarde o número abaixo para acompanhar a resposta:</p>
        <div class="numero"><?= htmlspecialchars($protocoloGerado) ?></div>
        <p><a href="ouvidoria-consultar.php" class="btn-leia-mais">Consultar andamento <i class="fas fa-arrow-right"></i></a></p>
      </div>
    <?php else: ?>

      <p style="margin-bottom:1.5rem;"><a href="ouvidoria-consultar.php"><i class="fas fa-magnifying-glass"></i> Já tenho um protocolo — consultar andamento</a></p>

      <?php if (!empty($erros)): ?>
        <div class="alerta-portal erro">
          <i class="fas fa-triangle-exclamation"></i> <?= implode('<br>', array_map('htmlspecialchars', $erros)) ?>
        </div>
      <?php endif; ?>

      <form class="form-atendimento" method="post" enctype="multipart/form-data">
        <div class="campo-form" style="margin-bottom:1.1rem;">
          <label for="id_tipo_manifestacao">Tipo de manifestação *</label>
          <select id="id_tipo_manifestacao" name="id_tipo_manifestacao" required>
            <option value="">Selecione...</option>
            <?php foreach ($tipos as $t): ?>
              <option value="<?= $t['id_tipo_manifestacao'] ?>" <?= (string) ($_POST['id_tipo_manifestacao'] ?? '') === (string) $t['id_tipo_manifestacao'] ? 'selected' : '' ?>><?= htmlspecialchars($t['descricao']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="campo-checkbox-form">
          <input type="checkbox" id="anonimo" name="anonimo" onchange="document.getElementById('bloco-identificacao').style.display = this.checked ? 'none' : 'grid';" <?= isset($_POST['anonimo']) ? 'checked' : '' ?>>
          <label for="anonimo">Desejo fazer esta manifestação de forma anônima</label>
        </div>

        <div class="linha-campos" id="bloco-identificacao" style="display:<?= isset($_POST['anonimo']) ? 'none' : 'grid' ?>;">
          <div class="campo-form">
            <label for="nome">Nome completo</label>
            <input type="text" id="nome" name="nome" maxlength="150" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
          </div>
          <div class="campo-form">
            <label for="cpf">CPF</label>
            <input type="text" id="cpf" name="cpf" maxlength="14" placeholder="000.000.000-00" value="<?= htmlspecialchars($_POST['cpf'] ?? '') ?>">
          </div>
          <div class="campo-form">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" maxlength="100" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
          </div>
          <div class="campo-form">
            <label for="telefone">Telefone</label>
            <input type="text" id="telefone" name="telefone" maxlength="20" value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>">
          </div>
        </div>

        <div class="campo-form" style="margin-bottom:1.1rem;">
          <label for="texto">Descreva sua manifestação *</label>
          <textarea id="texto" name="texto" rows="6" required><?= htmlspecialchars($_POST['texto'] ?? '') ?></textarea>
        </div>

        <div class="campo-form" style="margin-bottom:1.1rem;">
          <label for="anexo">Anexo (opcional)</label>
          <input type="file" id="anexo" name="anexo" accept=".jpg,.jpeg,.png,.pdf">
          <small style="display:block; margin-top:.3rem; color:var(--texto-claro); font-size:.78rem;">JPG, PNG ou PDF, até 5MB.</small>
        </div>

        <button type="submit" class="btn-enviar-form"><i class="fas fa-paper-plane"></i> Enviar manifestação</button>
      </form>

    <?php endif; ?>

  </div>
</section>

<script>
  document.getElementById('cpf')?.addEventListener('input', function (e) {
    let v = e.target.value.replace(/\D/g, '').slice(0, 11);
    v = v.replace(/(\d{3})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    e.target.value = v;
  });
</script>

<?php include 'includes/footer.php'; ?>
