<?php
require_once __DIR__ . '/includes/auth.php';

$tituloPagina = 'Dashboard';

$totalNoticias      = (int) $pdo->query("SELECT COUNT(*) FROM noticias WHERE status = 1")->fetchColumn();
$totalDocumentos    = (int) $pdo->query("SELECT COUNT(*) FROM documentos WHERE status = 1")->fetchColumn();
$totalLicitacoes    = (int) $pdo->query("SELECT COUNT(*) FROM licitacoes WHERE status = 1")->fetchColumn();
$totalManifestacoes = (int) $pdo->query("SELECT COUNT(*) FROM manifestacoes WHERE respondida = 0")->fetchColumn();

$ultimasManifestacoes = $pdo->query(
    "SELECT protocolo, nome, criado_em, respondida
     FROM manifestacoes
     ORDER BY criado_em DESC
     LIMIT 5"
)->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<h1 class="pagina__titulo">Olá, <?= htmlspecialchars(explode(' ', $usuario['nome_completo'])[0]) ?> 👋</h1>
<p class="pagina__subtitulo">Aqui está um resumo do que está acontecendo no portal.</p>

<div class="grid-cards">
  <div class="card-resumo">
    <div class="card-resumo__icone"><i class="fa-solid fa-newspaper"></i></div>
    <div>
      <div class="card-resumo__numero"><?= $totalNoticias ?></div>
      <div class="card-resumo__legenda">Notícias publicadas</div>
    </div>
  </div>

  <div class="card-resumo">
    <div class="card-resumo__icone"><i class="fa-solid fa-gavel"></i></div>
    <div>
      <div class="card-resumo__numero"><?= $totalDocumentos ?></div>
      <div class="card-resumo__legenda">Documentos publicados</div>
    </div>
  </div>

  <div class="card-resumo">
    <div class="card-resumo__icone"><i class="fa-solid fa-file-contract"></i></div>
    <div>
      <div class="card-resumo__numero"><?= $totalLicitacoes ?></div>
      <div class="card-resumo__legenda">Licitações ativas</div>
    </div>
  </div>

  <div class="card-resumo" style="border-left-color: var(--dourado);">
    <div class="card-resumo__icone" style="background:#fdf0d5; color:#96690a;"><i class="fa-solid fa-comments"></i></div>
    <div>
      <div class="card-resumo__numero"><?= $totalManifestacoes ?></div>
      <div class="card-resumo__legenda">Manifestações pendentes</div>
    </div>
  </div>
</div>

<div class="painel">
  <h2 class="painel__titulo"><i class="fa-solid fa-inbox"></i> Últimas manifestações recebidas</h2>

  <?php if (empty($ultimasManifestacoes)): ?>
    <p style="color:var(--texto-claro); font-size:.88rem;">Nenhuma manifestação recebida ainda.</p>
  <?php else: ?>
    <table class="tabela-simples">
      <thead>
        <tr>
          <th>Protocolo</th>
          <th>Nome</th>
          <th>Recebida em</th>
          <th>Situação</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($ultimasManifestacoes as $m): ?>
          <tr>
            <td><?= htmlspecialchars($m['protocolo']) ?></td>
            <td><?= htmlspecialchars($m['nome'] ?: 'Anônimo') ?></td>
            <td><?= date('d/m/Y H:i', strtotime($m['criado_em'])) ?></td>
            <td>
              <?php if ($m['respondida']): ?>
                <span class="badge badge-ok">Respondida</span>
              <?php else: ?>
                <span class="badge badge-pendente">Pendente</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
