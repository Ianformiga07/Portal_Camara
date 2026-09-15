<?php
require_once __DIR__ . '/includes/auth.php';

$tituloPagina = 'Banner Principal';

$slides = $pdo->query(
    'SELECT id_slide, titulo, texto, imagem, ordem, status FROM hero_slides ORDER BY ordem ASC, id_slide ASC'
)->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<h1 class="pagina__titulo">Banner Principal</h1>
<p class="pagina__subtitulo">Slides exibidos no carrossel do topo da página inicial do portal.</p>

<?php if (!empty($_GET['ok'])): ?>
  <div class="alerta alerta-sucesso" style="max-width:none;">
    <i class="fa-solid fa-circle-check"></i>
    <?= $_GET['ok'] === 'excluido' ? 'Slide excluído com sucesso.' : 'Slide salvo com sucesso.' ?>
  </div>
<?php endif; ?>

<div class="toolbar">
  <div></div>
  <a href="hero-slide-form.php" class="btn btn-primario" style="width:auto;">
    <i class="fa-solid fa-plus"></i> Novo slide
  </a>
</div>

<div class="painel">
  <?php if (empty($slides)): ?>
    <div class="vazio">
      <i class="fa-solid fa-images"></i>
      <p>Nenhum slide cadastrado. Enquanto não houver slides aqui, o portal usa as imagens padrão da pasta <code>assets/img/hero-carousel/</code>.</p>
    </div>
  <?php else: ?>
    <table class="tabela-simples">
      <thead>
        <tr>
          <th style="width:70px;"></th>
          <th style="width:70px;">Ordem</th>
          <th>Título</th>
          <th>Situação</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($slides as $s): ?>
          <tr>
            <td>
              <?php if ($s['imagem']): ?>
                <img class="miniatura" src="assets/uploads/hero/<?= htmlspecialchars($s['imagem']) ?>" alt="">
              <?php else: ?>
                <img class="miniatura" src="assets/img/sem-imagem.svg" alt="">
              <?php endif; ?>
            </td>
            <td><?= (int) $s['ordem'] ?></td>
            <td>
              <div class="titulo-linha"><?= htmlspecialchars($s['titulo']) ?></div>
              <?php if ($s['texto']): ?>
                <div class="descricao-linha"><?= htmlspecialchars($s['texto']) ?></div>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($s['status']): ?>
                <span class="badge badge-ok">Ativo</span>
              <?php else: ?>
                <span class="badge badge-inativo">Inativo</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="coluna-acoes">
                <a class="btn-icone" href="hero-slide-form.php?id=<?= $s['id_slide'] ?>" title="Editar">
                  <i class="fa-solid fa-pen"></i>
                </a>
                <form method="post" action="hero-slide-excluir.php" onsubmit="return confirm('Excluir o slide \'<?= htmlspecialchars(addslashes($s['titulo'])) ?>\'? Essa ação não pode ser desfeita.');" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $s['id_slide'] ?>">
                  <button type="submit" class="btn-icone perigo" title="Excluir"><i class="fa-solid fa-trash"></i></button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <p style="font-size:.8rem; color:var(--texto-claro,#777); margin-top:1rem;">
      <i class="fa-solid fa-circle-info"></i> A ordem de exibição no carrossel segue a coluna "Ordem" (menor primeiro). Apenas slides "Ativos" aparecem no portal.
    </p>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
