<?php
require_once __DIR__ . '/includes/auth.php';

$tituloPagina = 'Diário Oficial';

$edicoes = $pdo->query(
    "SELECT e.id_edicao, e.numero_edicao, e.ano_exercicio, e.data_edicao, e.status,
            e.hash_sha256, e.assinado_icp, e.arquivo_pdf,
            (SELECT COUNT(*) FROM diario_oficial_atos a WHERE a.id_edicao = e.id_edicao) AS total_atos
     FROM diario_oficial_edicoes e
     ORDER BY e.ano_exercicio DESC, e.data_edicao DESC, e.id_edicao DESC"
)->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<h1 class="pagina__titulo">Diário Oficial</h1>
<p class="pagina__subtitulo">Monte, assine e publique as edições do Diário Oficial a partir daqui.</p>

<?php if (!empty($_GET['ok'])): ?>
  <div class="alerta alerta-sucesso" style="max-width:none;">
    <i class="fa-solid fa-circle-check"></i>
    <?= $_GET['ok'] === 'excluido' ? 'Edição excluída com sucesso.' : 'Operação concluída com sucesso.' ?>
  </div>
<?php endif; ?>
<?php if (!empty($_GET['erro'])): ?>
  <div class="alerta alerta-erro" style="max-width:none;">
    <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($_GET['erro']) ?>
  </div>
<?php endif; ?>

<div class="painel" style="margin-bottom:1.25rem;">
  <h2 class="painel__titulo"><i class="fa-solid fa-plus"></i> Nova edição</h2>
  <form class="form-grid" method="post" action="diario-oficial-criar.php">
    <div class="campo">
      <label for="numero_edicao">Número da edição *</label>
      <input type="text" id="numero_edicao" name="numero_edicao" maxlength="20" required placeholder="ex: 007">
    </div>
    <div class="campo">
      <label for="ano_exercicio">Ano</label>
      <input type="number" id="ano_exercicio" name="ano_exercicio" min="2000" max="2100" value="<?= date('Y') ?>">
    </div>
    <div class="campo">
      <label for="data_edicao">Data de publicação</label>
      <input type="date" id="data_edicao" name="data_edicao" value="<?= date('Y-m-d') ?>">
    </div>
    <div class="campo" style="align-self:end;">
      <button type="submit" class="btn btn-primario" style="width:auto;">
        <i class="fa-solid fa-file-circle-plus"></i> Criar edição e começar a montar
      </button>
    </div>
  </form>
</div>

<div class="painel">
  <?php if (empty($edicoes)): ?>
    <div class="vazio">
      <i class="fa-solid fa-newspaper"></i>
      <p>Nenhuma edição criada ainda.</p>
    </div>
  <?php else: ?>
    <table class="tabela-simples">
      <thead>
        <tr>
          <th>Edição</th>
          <th>Data</th>
          <th>Atos</th>
          <th>Situação</th>
          <th>Integridade</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($edicoes as $e): ?>
          <tr>
            <td class="titulo-linha">Nº <?= htmlspecialchars($e['numero_edicao']) ?>/<?= htmlspecialchars($e['ano_exercicio']) ?></td>
            <td><?= date('d/m/Y', strtotime($e['data_edicao'])) ?></td>
            <td><?= (int) $e['total_atos'] ?></td>
            <td>
              <?php if ($e['status'] === 'publicada'): ?>
                <span class="badge badge-ok">Publicada</span>
              <?php else: ?>
                <span class="badge badge-pendente">Rascunho</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($e['assinado_icp']): ?>
                <span class="badge badge-ok" title="Assinado digitalmente (ICP-Brasil)"><i class="fa-solid fa-signature"></i> Assinado</span>
              <?php elseif ($e['hash_sha256']): ?>
                <span class="badge badge-pendente" title="<?= htmlspecialchars($e['hash_sha256']) ?>"><i class="fa-solid fa-fingerprint"></i> Hash SHA-256</span>
              <?php else: ?>
                <span class="badge badge-inativo">PDF não gerado</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="coluna-acoes">
                <?php if ($e['arquivo_pdf']): ?>
                  <a class="btn-icone" href="assets/uploads/diario-oficial/<?= htmlspecialchars($e['arquivo_pdf']) ?>" target="_blank" title="Ver PDF">
                    <i class="fa-solid fa-file-pdf"></i>
                  </a>
                <?php endif; ?>
                <a class="btn-icone" href="diario-oficial-editor.php?id=<?= $e['id_edicao'] ?>" title="<?= $e['status'] === 'publicada' ? 'Ver edição' : 'Continuar montagem' ?>">
                  <i class="fa-solid <?= $e['status'] === 'publicada' ? 'fa-eye' : 'fa-pen' ?>"></i>
                </a>
                <?php if ($e['status'] === 'rascunho'): ?>
                  <form method="post" action="diario-oficial-excluir.php" onsubmit="return confirm('Excluir esta edição em rascunho? Essa ação não pode ser desfeita.');" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $e['id_edicao'] ?>">
                    <button type="submit" class="btn-icone perigo" title="Excluir"><i class="fa-solid fa-trash"></i></button>
                  </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
