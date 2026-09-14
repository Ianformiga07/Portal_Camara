<?php
$page_title = 'e-SIC — Câmara Municipal de Ananás';
require_once 'includes/funcoes.php';
include 'includes/header.php';

$tituloPaginaInterna = 'e-SIC — Serviço de Informação ao Cidadão';
$subtituloPaginaInterna = 'Solicite acesso a informações públicas da Câmara Municipal, conforme a Lei nº 12.527/2011.';

$tipos = $pdo ? $pdo->query('SELECT id_tipo_esic, descricao FROM tipos_esic ORDER BY descricao')->fetchAll() : [];
$escolaridades = $pdo ? $pdo->query('SELECT id_escolaridade, descricao FROM escolaridades ORDER BY id_escolaridade')->fetchAll() : [];
$erros = [];
$protocoloGerado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $anonimo = isset($_POST['anonimo']) ? 1 : 0;
    $idTipo  = $_POST['id_tipo_esic'] ?: null;
    $nome    = trim($_POST['nome'] ?? '');
    $cpf     = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
    $dataNascimento = $_POST['data_nascimento'] ?: null;
    $sexo    = $_POST['sexo'] ?: null;
    $idEscolaridade = $_POST['id_escolaridade'] ?: null;
    $telefone = trim($_POST['telefone'] ?? '');
    $formaRecebimento = $_POST['forma_recebimento'] ?? 'E-mail';
    $email   = trim($_POST['email'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');

    if (!$idTipo) {
        $erros[] = 'Selecione o tipo de solicitação.';
    }
    if ($descricao === '') {
        $erros[] = 'Descreva a informação solicitada.';
    }
    if (!$anonimo && $nome === '') {
        $erros[] = 'Informe seu nome, ou marque a opção de solicitação anônima.';
    }
    if (!$anonimo && ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL))) {
        $erros[] = 'Informe um e-mail válido para receber a resposta.';
    }

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
            $arquivo = 'esic_' . uniqid() . '.' . $extensao;
            $pastaDestino = __DIR__ . '/gerenciador/assets/uploads/esic/';
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
        $protocolo = gerarProtocoloUnico($pdo, 'esic_solicitacoes', 'SIC');
        $pdo->prepare(
            'INSERT INTO esic_solicitacoes (id_tipo_esic, anonimo, nome, cpf, data_nascimento, sexo, id_escolaridade,
                telefone, forma_recebimento, email, descricao, anexo, protocolo)
             VALUES (:tipo, :anonimo, :nome, :cpf, :nasc, :sexo, :escolaridade, :telefone, :forma, :email, :descricao, :anexo, :protocolo)'
        )->execute([
            'tipo'         => $idTipo,
            'anonimo'      => $anonimo,
            'nome'         => $anonimo ? null : ($nome ?: null),
            'cpf'          => $anonimo ? null : ($cpf ?: null),
            'nasc'         => $dataNascimento,
            'sexo'         => $sexo,
            'escolaridade' => $idEscolaridade,
            'telefone'     => $telefone ?: null,
            'forma'        => $formaRecebimento,
            'email'        => $email ?: null,
            'descricao'    => $descricao,
            'anexo'        => $arquivo,
            'protocolo'    => $protocolo,
        ]);
        $protocoloGerado = $protocolo;
    } elseif (empty($erros) && !$pdo) {
        $erros[] = 'Não foi possível registrar sua solicitação no momento. Tente novamente mais tarde.';
    }
}

include 'includes/pagina-header.php';
?>

<section class="pagina-interna-conteudo">
  <div class="section-container">

    <?php if ($protocoloGerado): ?>
      <div class="protocolo-box">
        <i class="fas fa-circle-check"></i>
        <h3>Solicitação registrada com sucesso!</h3>
        <p>Guarde o número abaixo para acompanhar a resposta. Pela Lei nº 12.527/2011, o prazo de resposta é de até <strong>20 dias corridos</strong>, prorrogável por mais 10 dias mediante justificativa.</p>
        <div class="numero"><?= htmlspecialchars($protocoloGerado) ?></div>
        <p><a href="esic-consultar.php" class="btn-leia-mais">Consultar andamento <i class="fas fa-arrow-right"></i></a></p>
      </div>
    <?php else: ?>

      <p style="margin-bottom:1.5rem;"><a href="esic-consultar.php"><i class="fas fa-magnifying-glass"></i> Já tenho um protocolo — consultar andamento</a></p>

      <div class="alerta-portal aviso">
        <i class="fas fa-circle-info"></i> O prazo legal de resposta é de até 20 dias corridos, prorrogável por mais 10 dias mediante justificativa (Lei nº 12.527/2011).
      </div>

      <?php if (!empty($erros)): ?>
        <div class="alerta-portal erro">
          <i class="fas fa-triangle-exclamation"></i> <?= implode('<br>', array_map('htmlspecialchars', $erros)) ?>
        </div>
      <?php endif; ?>

      <form class="form-atendimento" method="post" enctype="multipart/form-data">
        <div class="campo-form" style="margin-bottom:1.1rem;">
          <label for="id_tipo_esic">Tipo de solicitação *</label>
          <select id="id_tipo_esic" name="id_tipo_esic" required>
            <option value="">Selecione...</option>
            <?php foreach ($tipos as $t): ?>
              <option value="<?= $t['id_tipo_esic'] ?>" <?= (string) ($_POST['id_tipo_esic'] ?? '') === (string) $t['id_tipo_esic'] ? 'selected' : '' ?>><?= htmlspecialchars($t['descricao']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="campo-checkbox-form">
          <input type="checkbox" id="anonimo" name="anonimo" onchange="document.getElementById('bloco-identificacao-sic').style.display = this.checked ? 'none' : 'grid';" <?= isset($_POST['anonimo']) ? 'checked' : '' ?>>
          <label for="anonimo">Desejo fazer esta solicitação de forma anônima</label>
        </div>

        <div class="linha-campos" id="bloco-identificacao-sic" style="display:<?= isset($_POST['anonimo']) ? 'none' : 'grid' ?>;">
          <div class="campo-form">
            <label for="nome">Nome completo</label>
            <input type="text" id="nome" name="nome" maxlength="150" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
          </div>
          <div class="campo-form">
            <label for="cpf">CPF</label>
            <input type="text" id="cpf" name="cpf" maxlength="14" placeholder="000.000.000-00" value="<?= htmlspecialchars($_POST['cpf'] ?? '') ?>">
          </div>
          <div class="campo-form">
            <label for="email">E-mail *</label>
            <input type="email" id="email" name="email" maxlength="100" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
          </div>
          <div class="campo-form">
            <label for="telefone">Telefone</label>
            <input type="text" id="telefone" name="telefone" maxlength="20" value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>">
          </div>
          <div class="campo-form">
            <label for="data_nascimento">Data de nascimento</label>
            <input type="date" id="data_nascimento" name="data_nascimento" value="<?= htmlspecialchars($_POST['data_nascimento'] ?? '') ?>">
          </div>
          <div class="campo-form">
            <label for="sexo">Sexo</label>
            <select id="sexo" name="sexo">
              <option value="">Não informar</option>
              <option value="M" <?= ($_POST['sexo'] ?? '') === 'M' ? 'selected' : '' ?>>Masculino</option>
              <option value="F" <?= ($_POST['sexo'] ?? '') === 'F' ? 'selected' : '' ?>>Feminino</option>
            </select>
          </div>
          <div class="campo-form">
            <label for="id_escolaridade">Escolaridade</label>
            <select id="id_escolaridade" name="id_escolaridade">
              <option value="">Não informar</option>
              <?php foreach ($escolaridades as $e): ?>
                <option value="<?= $e['id_escolaridade'] ?>"><?= htmlspecialchars($e['descricao']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="campo-form">
            <label for="forma_recebimento">Como deseja receber a resposta?</label>
            <select id="forma_recebimento" name="forma_recebimento">
              <option value="E-mail">E-mail</option>
              <option value="Telefone">Telefone</option>
              <option value="Presencial">Retirar presencialmente</option>
            </select>
          </div>
        </div>

        <div class="campo-form" style="margin-bottom:1.1rem;">
          <label for="descricao">Descreva a informação solicitada *</label>
          <textarea id="descricao" name="descricao" rows="6" required><?= htmlspecialchars($_POST['descricao'] ?? '') ?></textarea>
        </div>

        <div class="campo-form" style="margin-bottom:1.1rem;">
          <label for="anexo">Anexo (opcional)</label>
          <input type="file" id="anexo" name="anexo" accept=".jpg,.jpeg,.png,.pdf">
          <small style="display:block; margin-top:.3rem; color:var(--texto-claro); font-size:.78rem;">JPG, PNG ou PDF, até 5MB.</small>
        </div>

        <button type="submit" class="btn-enviar-form"><i class="fas fa-paper-plane"></i> Enviar solicitação</button>
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
