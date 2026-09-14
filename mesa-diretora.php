<?php
$page_title = 'Mesa Diretora — Câmara Municipal de Ananás';
include 'includes/header.php';

$tituloPaginaInterna = 'Mesa Diretora';
$subtituloPaginaInterna = 'Composição atual da Mesa Diretora da Câmara Municipal de Ananás.';

$membros = [];
if ($pdo) {
    $legislaturaAtual = $pdo->query('SELECT id_legislatura FROM legislaturas_bienio ORDER BY id_legislatura DESC LIMIT 1')->fetchColumn();
    if ($legislaturaAtual) {
        $stmt = $pdo->prepare(
            "SELECT md.id_vereador, s.nome_completo, s.foto_perfil, v.apelido, v.partido, f.descricao AS funcao_descricao
             FROM mesa_diretora md
             JOIN vereadores v ON v.id_vereador = md.id_vereador
             JOIN servidores s ON s.id_servidor = v.id_servidor
             LEFT JOIN funcoes_legislativas f ON f.id_funcao = md.id_funcao
             WHERE md.id_legislatura = :leg AND md.ativo = 1
             ORDER BY md.id_funcao"
        );
        $stmt->execute(['leg' => $legislaturaAtual]);
        $membros = $stmt->fetchAll();
    }
}

include 'includes/pagina-header.php';
?>

<section class="pagina-interna-conteudo">
  <div class="section-container">
    <?php if (empty($membros)): ?>
      <div class="vazio-lista">
        <i class="fas fa-people-roof"></i>
        <p>Composição da Mesa Diretora ainda não cadastrada.</p>
      </div>
    <?php else: ?>
      <div class="vereadores-grid-completa">
        <?php foreach ($membros as $m): ?>
          <a href="vereador-detalhe.php?id=<?= $m['id_vereador'] ?>" class="vereador-card">
            <?php if (!empty($m['foto_perfil'])): ?>
              <div class="vereador-foto-placeholder" style="padding:0; overflow:hidden;">
                <img src="gerenciador/assets/uploads/vereadores/<?= htmlspecialchars($m['foto_perfil']) ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
              </div>
            <?php else: ?>
              <div class="vereador-foto-placeholder"><i class="fas fa-user"></i></div>
            <?php endif; ?>
            <span class="nome"><?= htmlspecialchars($m['apelido'] ?: $m['nome_completo']) ?></span>
            <span class="partido"><?= htmlspecialchars($m['funcao_descricao'] ?: '') ?><?= $m['partido'] ? ' — ' . htmlspecialchars($m['partido']) : '' ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
