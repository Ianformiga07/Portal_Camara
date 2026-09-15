<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/assinatura-digital.php';

$id = (int) ($_GET['id'] ?? 0);
$tituloPagina = 'Montar edição do Diário Oficial';

$stmt = $pdo->prepare('SELECT * FROM diario_oficial_edicoes WHERE id_edicao = :id');
$stmt->execute(['id' => $id]);
$edicao = $stmt->fetch();

if (!$edicao) {
    header('Location: diario-oficial-edicoes.php');
    exit;
}

$editavel = $edicao['status'] === 'rascunho';

$stmtAtos = $pdo->prepare('SELECT * FROM diario_oficial_atos WHERE id_edicao = :id ORDER BY ordem, id_ato');
$stmtAtos->execute(['id' => $id]);
$atos = $stmtAtos->fetchAll();

$certificadoOk = certificadoDisponivel();

include __DIR__ . '/includes/header.php';
?>

<h1 class="pagina__titulo">Edição nº <?= htmlspecialchars($edicao['numero_edicao']) ?>/<?= htmlspecialchars($edicao['ano_exercicio']) ?></h1>
<p class="pagina__subtitulo">
  <a href="diario-oficial-edicoes.php">← Voltar para a lista</a> ·
  Publicação em <?= date('d/m/Y', strtotime($edicao['data_edicao'])) ?> ·
  <?= $editavel ? '<span class="badge badge-pendente">Rascunho</span>' : '<span class="badge badge-ok">Publicada</span>' ?>
</p>

<?php if (!empty($_GET['ok'])): ?>
  <div class="alerta alerta-sucesso" style="max-width:none;">
    <i class="fa-solid fa-circle-check"></i>
    <?php if ($_GET['ok'] === 'reunido'): ?>Atos do período reunidos com sucesso.
    <?php elseif ($_GET['ok'] === 'gerado'): ?>PDF da edição gerado com sucesso.
    <?php else: ?>Operação concluída com sucesso.<?php endif; ?>
  </div>
<?php endif; ?>
<?php if (!empty($_GET['erro'])): ?>
  <div class="alerta alerta-erro" style="max-width:none;">
    <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($_GET['erro']) ?>
  </div>
<?php endif; ?>

<?php if (!$certificadoOk): ?>
  <div class="alerta alerta-erro" style="max-width:none; background:#fdf0d5; color:#96690a; border-color:#f0dba8;">
    <i class="fa-solid fa-circle-info"></i>
    Nenhum certificado digital ICP-Brasil configurado — os PDFs serão publicados apenas com hash SHA-256 de integridade, sem assinatura digital.
    Para assinar de verdade, configure <code>gerenciador/config/assinatura.php</code> (veja <code>assinatura.example.php</code>).
  </div>
<?php endif; ?>

<?php if ($editavel): ?>
<div class="painel" style="margin-bottom:1.25rem;">
  <h2 class="painel__titulo"><i class="fa-solid fa-arrows-rotate"></i> Reunir automaticamente</h2>
  <p class="pagina__subtitulo" style="margin-bottom:1rem;">
    Busca decretos, portarias, resoluções e demais documentos publicados no período, além de licitações abertas e extratos de contrato, e adiciona todos como atos desta edição.
  </p>
  <form class="form-grid" method="post" action="diario-oficial-reunir.php">
    <input type="hidden" name="id_edicao" value="<?= $id ?>">
    <div class="campo">
      <label for="data_inicio">De</label>
      <input type="date" id="data_inicio" name="data_inicio" required value="<?= date('Y-m-01') ?>">
    </div>
    <div class="campo">
      <label for="data_fim">Até</label>
      <input type="date" id="data_fim" name="data_fim" required value="<?= date('Y-m-d') ?>">
    </div>
    <div class="campo" style="align-self:end;">
      <button type="submit" class="btn btn-secundario" style="width:auto;">
        <i class="fa-solid fa-magnifying-glass"></i> Buscar e adicionar
      </button>
    </div>
  </form>
</div>

<div class="painel" style="margin-bottom:1.25rem;">
  <h2 class="painel__titulo"><i class="fa-solid fa-pen-to-square"></i> Adicionar ato avulso</h2>
  <p class="pagina__subtitulo" style="margin-bottom:1rem;">Para atos que ainda não têm cadastro em outro módulo — digite o conteúdo direto aqui.</p>
  <form class="form-grid" method="post" action="diario-oficial-ato-salvar.php">
    <input type="hidden" name="id_edicao" value="<?= $id ?>">
    <div class="campo">
      <label for="tipo">Tipo *</label>
      <input type="text" id="tipo" name="tipo" maxlength="60" required placeholder="ex: Portaria, Edital, Aviso">
    </div>
    <div class="campo">
      <label for="numero">Número</label>
      <input type="text" id="numero" name="numero" maxlength="20" placeholder="ex: 012/2026">
    </div>
    <div class="campo campo-largo">
      <label for="titulo_ato">Título / ementa</label>
      <input type="text" id="titulo_ato" name="titulo" maxlength="255">
    </div>
    <div class="campo campo-largo">
      <label for="texto">Texto do ato *</label>
      <textarea id="texto" name="texto" rows="6" required></textarea>
    </div>
    <div class="campo" style="align-self:end;">
      <button type="submit" class="btn btn-secundario" style="width:auto;">
        <i class="fa-solid fa-plus"></i> Adicionar à edição
      </button>
    </div>
  </form>
</div>
<?php endif; ?>

<div class="painel" style="margin-bottom:1.25rem;">
  <h2 class="painel__titulo"><i class="fa-solid fa-list-ol"></i> Atos desta edição (<?= count($atos) ?>)</h2>
  <?php if (empty($atos)): ?>
    <div class="vazio">
      <i class="fa-solid fa-inbox"></i>
      <p>Nenhum ato adicionado ainda. Use as ferramentas acima para reunir ou adicionar atos.</p>
    </div>
  <?php else: ?>
    <table class="tabela-simples">
      <thead>
        <tr>
          <th>Tipo</th>
          <th>Número</th>
          <th>Título</th>
          <th>Origem</th>
          <?php if ($editavel): ?><th></th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($atos as $a): ?>
          <tr>
            <td><?= htmlspecialchars($a['tipo']) ?></td>
            <td><?= htmlspecialchars($a['numero'] ?: '—') ?></td>
            <td><?= htmlspecialchars($a['titulo'] ?: '—') ?></td>
            <td><?= $a['origem'] === 'auto' ? '<span class="badge badge-ok">Reunido</span>' : '<span class="badge badge-pendente">Avulso</span>' ?></td>
            <?php if ($editavel): ?>
              <td>
                <form method="post" action="diario-oficial-ato-excluir.php" onsubmit="return confirm('Remover este ato da edição?');">
                  <input type="hidden" name="id_ato" value="<?= $a['id_ato'] ?>">
                  <input type="hidden" name="id_edicao" value="<?= $id ?>">
                  <button type="submit" class="btn-icone perigo" title="Remover"><i class="fa-solid fa-trash"></i></button>
                </form>
              </td>
            <?php endif; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<div class="painel">
  <h2 class="painel__titulo"><i class="fa-solid fa-file-pdf"></i> Gerar, assinar e publicar</h2>

  <?php if ($edicao['arquivo_pdf']): ?>
    <p style="font-size:.9rem; margin-bottom:.75rem;">
      <a href="assets/uploads/diario-oficial/<?= htmlspecialchars($edicao['arquivo_pdf']) ?>" target="_blank">
        <i class="fa-solid fa-file-pdf"></i> Ver PDF gerado
      </a>
      <br>
      <span style="color:var(--texto-claro); font-size:.8rem;">
        Hash SHA-256: <code><?= htmlspecialchars($edicao['hash_sha256']) ?></code><br>
        <?= $edicao['assinado_icp'] ? '<i class="fa-solid fa-signature"></i> Assinado digitalmente (ICP-Brasil) em ' . date('d/m/Y H:i', strtotime($edicao['assinado_em'])) : '<i class="fa-solid fa-fingerprint"></i> Publicado apenas com hash de integridade (sem certificado ICP-Brasil configurado)' ?>
      </span>
    </p>
  <?php endif; ?>

  <?php if ($editavel): ?>
    <div class="form-acoes">
      <form method="post" action="diario-oficial-gerar.php" style="display:inline;">
        <input type="hidden" name="id_edicao" value="<?= $id ?>">
        <button type="submit" class="btn btn-secundario" style="width:auto;" <?= empty($atos) ? 'disabled title="Adicione ao menos um ato antes"' : '' ?>>
          <i class="fa-solid fa-gears"></i> <?= $edicao['arquivo_pdf'] ? 'Gerar PDF novamente' : 'Gerar PDF da edição' ?>
        </button>
      </form>

      <?php if ($edicao['arquivo_pdf']): ?>
        <form method="post" action="diario-oficial-publicar.php" style="display:inline;" onsubmit="return confirm('Publicar esta edição no Diário Oficial do site? Depois de publicada ela não poderá mais ser editada.');">
          <input type="hidden" name="id_edicao" value="<?= $id ?>">
          <button type="submit" class="btn btn-primario" style="width:auto;">
            <i class="fa-solid fa-upload"></i> Publicar no site
          </button>
        </form>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <p style="color:var(--texto-claro); font-size:.85rem;">Esta edição já foi publicada e não pode mais ser alterada.</p>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
