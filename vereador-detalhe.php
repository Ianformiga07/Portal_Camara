<?php
require_once 'config/database.php';
require_once 'includes/funcoes.php';

$id = (int) ($_GET['id'] ?? 0);
$vereador = null;

if (!$pdo) {
    header('Location: vereadores.php');
    exit;
}

$stmt = $pdo->prepare(
    "SELECT v.*, s.nome_completo, s.foto_perfil, s.data_nascimento, m.descricao AS mandato_descricao
     FROM vereadores v
     JOIN servidores s ON s.id_servidor = v.id_servidor
     LEFT JOIN mandatos_eletivos m ON m.id_mandato = v.id_mandato
     WHERE v.id_vereador = :id"
);
$stmt->execute(['id' => $id]);
$vereador = $stmt->fetch();

if (!$vereador) {
    header('Location: vereadores.php');
    exit;
}

// funções na Mesa Diretora (histórico completo, não só a atual)
$funcoesMesa = $pdo->prepare(
    "SELECT f.descricao AS funcao, l.descricao AS legislatura
     FROM mesa_diretora md
     JOIN funcoes_legislativas f ON f.id_funcao = md.id_funcao
     JOIN legislaturas_bienio l ON l.id_legislatura = md.id_legislatura
     WHERE md.id_vereador = :id AND md.ativo = 1
     ORDER BY l.id_legislatura DESC"
);
$funcoesMesa->execute(['id' => $id]);
$funcoesMesa = $funcoesMesa->fetchAll();

// comissões atuais
$comissoes = $pdo->prepare(
    "SELECT tc.descricao AS comissao, f.descricao AS funcao
     FROM comissoes_membros cm
     JOIN tipos_comissao tc ON tc.id_tipo_comissao = cm.id_tipo_comissao
     LEFT JOIN funcoes_legislativas f ON f.id_funcao = cm.id_funcao
     WHERE cm.id_vereador = :id AND cm.ativo = 1
     ORDER BY tc.descricao"
);
$comissoes->execute(['id' => $id]);
$comissoes = $comissoes->fetchAll();

// mandatos anteriores
$mandatosAnteriores = $pdo->prepare(
    "SELECT m.descricao AS mandato, ma.ano_inicio, ma.ano_fim
     FROM vereadores_mandatos_anteriores ma
     JOIN mandatos_eletivos m ON m.id_mandato = ma.id_mandato
     WHERE ma.id_vereador = :id
     ORDER BY ma.ano_inicio DESC"
);
$mandatosAnteriores->execute(['id' => $id]);
$mandatosAnteriores = $mandatosAnteriores->fetchAll();

// legislação de autoria
$documentosAutoria = $pdo->prepare(
    "SELECT d.id_documento, d.numero_documento, d.titulo, d.data_publicacao, d.arquivo, c.descricao AS categoria
     FROM documentos d
     JOIN categorias_documentos c ON c.id_categoria = d.id_categoria
     WHERE d.id_autor_vereador = :id AND d.status = 1
     ORDER BY d.data_publicacao DESC
     LIMIT 15"
);
$documentosAutoria->execute(['id' => $id]);
$documentosAutoria = $documentosAutoria->fetchAll();

$page_title = htmlspecialchars($vereador['nome_completo']) . ' — Câmara Municipal de Ananás';
include 'includes/header.php';

$tituloPaginaInterna = $vereador['apelido'] ?: $vereador['nome_completo'];
$subtituloPaginaInterna = 'Perfil do vereador na Câmara Municipal de Ananás.';
include 'includes/pagina-header.php';
?>

<section class="pagina-interna-conteudo">
  <div class="section-container">
    <p style="margin-bottom:1.5rem;"><a href="vereadores.php"><i class="fas fa-arrow-left"></i> Voltar para todos os vereadores</a></p>

    <div class="vereador-perfil">
      <div class="vereador-perfil-foto">
        <?php if (!empty($vereador['foto_perfil'])): ?>
          <img src="gerenciador/assets/uploads/vereadores/<?= htmlspecialchars($vereador['foto_perfil']) ?>" alt="<?= htmlspecialchars($vereador['nome_completo']) ?>">
        <?php else: ?>
          <i class="fas fa-user"></i>
        <?php endif; ?>
      </div>

      <div>
        <h2 class="vereador-perfil-nome"><?= htmlspecialchars($vereador['nome_completo']) ?></h2>
        <?php if ($vereador['apelido']): ?>
          <div class="vereador-perfil-apelido">"<?= htmlspecialchars($vereador['apelido']) ?>"</div>
        <?php endif; ?>

        <div class="vereador-perfil-tags">
          <?php if ($vereador['partido']): ?><span class="tag-info"><i class="fas fa-flag"></i> <?= htmlspecialchars($vereador['partido']) ?></span><?php endif; ?>
          <?php if ($vereador['mandato_descricao']): ?><span class="tag-info"><i class="fas fa-calendar"></i> Mandato <?= htmlspecialchars($vereador['mandato_descricao']) ?></span><?php endif; ?>
          <?php if ($vereador['ocupacao']): ?><span class="tag-info"><i class="fas fa-briefcase"></i> <?= htmlspecialchars($vereador['ocupacao']) ?></span><?php endif; ?>
          <?php foreach ($funcoesMesa as $fm): ?>
            <span class="tag-info dourado"><i class="fas fa-star"></i> <?= htmlspecialchars($fm['funcao']) ?> — <?= htmlspecialchars($fm['legislatura']) ?></span>
          <?php endforeach; ?>
        </div>

        <?php if (!empty($comissoes)): ?>
          <div class="vereador-perfil-secao">
            <h3><i class="fas fa-sitemap"></i> Comissões</h3>
            <ul class="lista-comissoes">
              <?php foreach ($comissoes as $c): ?>
                <li><?= htmlspecialchars($c['comissao']) ?><?= $c['funcao'] ? ' (' . htmlspecialchars($c['funcao']) . ')' : '' ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <?php if (!empty($mandatosAnteriores)): ?>
          <div class="vereador-perfil-secao">
            <h3><i class="fas fa-clock-rotate-left"></i> Mandatos anteriores</h3>
            <table class="tabela-mandatos">
              <thead><tr><th>Mandato</th><th>Início</th><th>Fim</th></tr></thead>
              <tbody>
                <?php foreach ($mandatosAnteriores as $ma): ?>
                  <tr>
                    <td><?= htmlspecialchars($ma['mandato']) ?></td>
                    <td><?= htmlspecialchars($ma['ano_inicio']) ?></td>
                    <td><?= htmlspecialchars($ma['ano_fim']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>

        <div class="vereador-perfil-secao">
          <h3><i class="fas fa-file-signature"></i> Legislação de autoria</h3>
          <?php if (empty($documentosAutoria)): ?>
            <p style="color:var(--texto-claro); font-size:.88rem;">Nenhum documento de autoria publicado ainda.</p>
          <?php else: ?>
            <ul class="lista-autoria">
              <?php foreach ($documentosAutoria as $d): ?>
                <li>
                  <span>
                    <?php if ($d['arquivo']): ?>
                      <a href="gerenciador/assets/uploads/documentos/<?= htmlspecialchars($d['arquivo']) ?>" target="_blank"><?= htmlspecialchars($d['categoria']) ?><?= $d['numero_documento'] ? ' Nº ' . htmlspecialchars($d['numero_documento']) : '' ?></a>
                    <?php else: ?>
                      <?= htmlspecialchars($d['categoria']) ?><?= $d['numero_documento'] ? ' Nº ' . htmlspecialchars($d['numero_documento']) : '' ?>
                    <?php endif; ?>
                    <?= $d['titulo'] ? ' — ' . htmlspecialchars($d['titulo']) : '' ?>
                  </span>
                  <time><?= $d['data_publicacao'] ? date('d/m/Y', strtotime($d['data_publicacao'])) : '' ?></time>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
