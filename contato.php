<?php
$page_title = 'Fale Conosco — Câmara Municipal de Ananás';
require_once 'includes/funcoes.php';
include 'includes/header.php';

$tituloPaginaInterna = 'Fale Conosco';
$subtituloPaginaInterna = 'Envie sua dúvida, sugestão ou mensagem diretamente para a Câmara Municipal de Ananás.';

$erros = [];
$enviado = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome_completo'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $assunto  = trim($_POST['assunto'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');

    if ($nome === '') {
        $erros[] = 'Informe seu nome.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'Informe um e-mail válido.';
    }
    if ($mensagem === '') {
        $erros[] = 'Escreva sua mensagem.';
    }

    if (empty($erros) && $pdo) {
        $pdo->prepare(
            'INSERT INTO contato_site (nome_completo, email, assunto, mensagem) VALUES (:nome, :email, :assunto, :mensagem)'
        )->execute([
            'nome'     => $nome,
            'email'    => $email,
            'assunto'  => $assunto ?: null,
            'mensagem' => $mensagem,
        ]);
        $enviado = true;
    } elseif (empty($erros) && !$pdo) {
        $erros[] = 'Não foi possível enviar sua mensagem no momento. Tente novamente mais tarde.';
    }
}

include 'includes/pagina-header.php';
?>

<section class="pagina-interna-conteudo">
  <div class="section-container">

    <?php if ($enviado): ?>
      <div class="protocolo-box">
        <i class="fas fa-circle-check"></i>
        <h3>Mensagem enviada com sucesso!</h3>
        <p>Recebemos sua mensagem e a equipe da Câmara irá analisá-la. Caso tenha informado um e-mail válido, você poderá receber um retorno por lá.</p>
        <p style="margin-top:1rem;"><a href="index.php" class="btn-leia-mais">Voltar para o início <i class="fas fa-arrow-right"></i></a></p>
      </div>
    <?php else: ?>

      <?php if (!empty($erros)): ?>
        <div class="alerta-portal erro">
          <i class="fas fa-triangle-exclamation"></i> <?= implode('<br>', array_map('htmlspecialchars', $erros)) ?>
        </div>
      <?php endif; ?>

      <form class="form-atendimento" method="post">
        <div class="linha-campos">
          <div class="campo-form">
            <label for="nome_completo">Nome completo *</label>
            <input type="text" id="nome_completo" name="nome_completo" maxlength="150" required value="<?= htmlspecialchars($_POST['nome_completo'] ?? '') ?>">
          </div>
          <div class="campo-form">
            <label for="email">E-mail *</label>
            <input type="email" id="email" name="email" maxlength="100" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
          </div>
        </div>

        <div class="campo-form" style="margin-bottom:1.1rem;">
          <label for="assunto">Assunto</label>
          <input type="text" id="assunto" name="assunto" maxlength="150" value="<?= htmlspecialchars($_POST['assunto'] ?? '') ?>">
        </div>

        <div class="campo-form" style="margin-bottom:1.1rem;">
          <label for="mensagem">Mensagem *</label>
          <textarea id="mensagem" name="mensagem" rows="6" required><?= htmlspecialchars($_POST['mensagem'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn-enviar-form"><i class="fas fa-paper-plane"></i> Enviar mensagem</button>
      </form>

    <?php endif; ?>

  </div>
</section>

<?php include 'includes/footer.php'; ?>
