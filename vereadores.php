<?php
$page_title = 'Vereadores — Câmara Municipal de Ananás';
include 'includes/header.php';

$tituloPaginaInterna = 'Vereadores';
$subtituloPaginaInterna = 'Conheça os vereadores em exercício na atual legislatura da Câmara Municipal de Ananás.';

$vereadores = [];
if ($pdo) {
    $vereadores = $pdo->query(
        "SELECT v.id_vereador, v.apelido, v.partido, v.ocupacao, s.nome_completo, s.foto_perfil,
                f.descricao AS funcao_descricao, m.descricao AS mandato_descricao
         FROM vereadores v
         JOIN servidores s ON s.id_servidor = v.id_servidor
         LEFT JOIN mesa_diretora md ON md.id_vereador = v.id_vereador AND md.ativo = 1
         LEFT JOIN funcoes_legislativas f ON f.id_funcao = md.id_funcao
         LEFT JOIN mandatos_eletivos m ON m.id_mandato = v.id_mandato
         WHERE v.ativo = 1
         ORDER BY s.nome_completo"
    )->fetchAll();
}

include 'includes/pagina-header.php';
?>

<section class="pagina-interna-conteudo">
  <div class="section-container">

    <?php if (empty($vereadores)): ?>
      <div class="vazio-lista">
        <i class="fas fa-users"></i>
        <p>Nenhum vereador cadastrado no momento.</p>
      </div>
    <?php else: ?>
      <div class="vereadores-grid-completa">
        <?php foreach ($vereadores as $v): ?>
          <a href="vereador-detalhe.php?id=<?= $v['id_vereador'] ?>" class="vereador-card">
            <?php if (!empty($v['foto_perfil'])): ?>
              <div class="vereador-foto-placeholder" style="padding:0; overflow:hidden;">
                <img src="gerenciador/assets/uploads/vereadores/<?= htmlspecialchars($v['foto_perfil']) ?>" alt="<?= htmlspecialchars($v['nome_completo']) ?>" style="width:100%; height:100%; object-fit:cover;">
              </div>
            <?php else: ?>
              <div class="vereador-foto-placeholder">
                <i class="fas fa-user"></i>
              </div>
            <?php endif; ?>
            <span class="nome"><?= htmlspecialchars($v['apelido'] ?: $v['nome_completo']) ?></span>
            <span class="partido">
              <?= htmlspecialchars($v['partido'] ?: '') ?>
              <?= $v['funcao_descricao'] ? ' — ' . htmlspecialchars($v['funcao_descricao']) : '' ?>
            </span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php include 'includes/footer.php'; ?>
