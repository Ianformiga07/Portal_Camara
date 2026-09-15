<?php
require_once __DIR__ . '/includes/auth.php';

$tituloPagina = 'Informações Institucionais';

$dados = $pdo->query('SELECT * FROM institucional_conteudo WHERE id = 1')->fetch();
if (!$dados) {
    $pdo->exec('INSERT INTO institucional_conteudo (id) VALUES (1)');
    $dados = $pdo->query('SELECT * FROM institucional_conteudo WHERE id = 1')->fetch();
}

$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $campos = ['competencia', 'carta_servicos', 'estrutura_organizacional', 'identidade_missao', 'identidade_visao', 'identidade_valores'];
    foreach ($campos as $c) {
        $dados[$c] = trim($_POST[$c] ?? '');
    }

    $stmt = $pdo->prepare(
        'UPDATE institucional_conteudo SET competencia=:competencia, carta_servicos=:carta_servicos,
            estrutura_organizacional=:estrutura_organizacional, identidade_missao=:identidade_missao,
            identidade_visao=:identidade_visao, identidade_valores=:identidade_valores
         WHERE id = 1'
    );
    $stmt->execute([
        'competencia'              => $dados['competencia'],
        'carta_servicos'           => $dados['carta_servicos'],
        'estrutura_organizacional' => $dados['estrutura_organizacional'],
        'identidade_missao'        => $dados['identidade_missao'],
        'identidade_visao'         => $dados['identidade_visao'],
        'identidade_valores'       => $dados['identidade_valores'],
    ]);

    header('Location: institucional.php?ok=1');
    exit;
}

include __DIR__ . '/includes/header.php';
?>

<h1 class="pagina__titulo">Informações Institucionais</h1>
<p class="pagina__subtitulo">Conteúdo exibido na página pública "Informações Institucionais" do portal.</p>

<?php if (!empty($_GET['ok'])): ?>
  <div class="alerta alerta-sucesso" style="max-width:none;">
    <i class="fa-solid fa-circle-check"></i> Conteúdo salvo com sucesso.
  </div>
<?php endif; ?>

<form class="painel" method="post">
  <div class="form-grid">
    <div class="campo campo-largo">
      <label for="competencia">Competência</label>
      <textarea id="competencia" name="competencia" rows="4"><?= htmlspecialchars($dados['competencia'] ?? '') ?></textarea>
    </div>

    <div class="campo campo-largo">
      <label for="carta_servicos">Carta de Serviços ao Usuário</label>
      <textarea id="carta_servicos" name="carta_servicos" rows="4"><?= htmlspecialchars($dados['carta_servicos'] ?? '') ?></textarea>
    </div>

    <div class="campo campo-largo">
      <label for="estrutura_organizacional">Estrutura Organizacional</label>
      <textarea id="estrutura_organizacional" name="estrutura_organizacional" rows="4"><?= htmlspecialchars($dados['estrutura_organizacional'] ?? '') ?></textarea>
    </div>

    <div class="campo">
      <label for="identidade_missao">Missão</label>
      <textarea id="identidade_missao" name="identidade_missao" rows="3"><?= htmlspecialchars($dados['identidade_missao'] ?? '') ?></textarea>
    </div>
    <div class="campo">
      <label for="identidade_visao">Visão</label>
      <textarea id="identidade_visao" name="identidade_visao" rows="3"><?= htmlspecialchars($dados['identidade_visao'] ?? '') ?></textarea>
    </div>
    <div class="campo">
      <label for="identidade_valores">Valores</label>
      <textarea id="identidade_valores" name="identidade_valores" rows="3"><?= htmlspecialchars($dados['identidade_valores'] ?? '') ?></textarea>
    </div>
  </div>

  <div class="form-acoes">
    <button type="submit" class="btn btn-primario" style="width:auto;">
      <i class="fa-solid fa-floppy-disk"></i> Salvar
    </button>
  </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
