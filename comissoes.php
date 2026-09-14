<?php
$page_title = 'Comissões — Câmara Municipal de Ananás';
include 'includes/header.php';

$tituloPaginaInterna = 'Comissões Permanentes';
$subtituloPaginaInterna = 'Composição das comissões permanentes da Câmara Municipal de Ananás.';

$porComissao = [];
if ($pdo) {
    $legislaturaAtual = $pdo->query('SELECT id_legislatura FROM legislaturas_bienio ORDER BY id_legislatura DESC LIMIT 1')->fetchColumn();
    if ($legislaturaAtual) {
        $stmt = $pdo->prepare(
            "SELECT s.nome_completo, v.apelido, f.descricao AS funcao_descricao, tc.descricao AS comissao_descricao
             FROM comissoes_membros cm
             JOIN vereadores v ON v.id_vereador = cm.id_vereador
             JOIN servidores s ON s.id_servidor = v.id_servidor
             LEFT JOIN funcoes_legislativas f ON f.id_funcao = cm.id_funcao
             LEFT JOIN tipos_comissao tc ON tc.id_tipo_comissao = cm.id_tipo_comissao
             WHERE cm.id_legislatura = :leg AND cm.ativo = 1
             ORDER BY tc.descricao"
        );
        $stmt->execute(['leg' => $legislaturaAtual]);
        foreach ($stmt->fetchAll() as $m) {
            $chave = $m['comissao_descricao'] ?: 'Sem comissão definida';
            $porComissao[$chave][] = $m;
        }
    }
}

include 'includes/pagina-header.php';
?>

<section class="pagina-interna-conteudo">
  <div class="section-container">
    <?php if (empty($porComissao)): ?>
      <div class="vazio-lista">
        <i class="fas fa-sitemap"></i>
        <p>Composição das comissões ainda não cadastrada.</p>
      </div>
    <?php else: ?>
      <?php foreach ($porComissao as $nomeComissao => $membros): ?>
        <div class="ficha-card">
          <h3><i class="fas fa-sitemap"></i> <?= htmlspecialchars($nomeComissao) ?></h3>
          <ul class="lista-participantes">
            <?php foreach ($membros as $m): ?>
              <li>
                <span><?= htmlspecialchars($m['apelido'] ?: $m['nome_completo']) ?></span>
                <?php if ($m['funcao_descricao']): ?><span class="tag-info"><?= htmlspecialchars($m['funcao_descricao']) ?></span><?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
