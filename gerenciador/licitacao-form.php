<?php
require_once __DIR__ . '/includes/auth.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$editando = $id !== null;
$tituloPagina = $editando ? 'Editar licitação' : 'Nova licitação';

$orgaos       = $pdo->query('SELECT id_orgao, descricao FROM orgaos ORDER BY descricao')->fetchAll();
$procedimentos = $pdo->query('SELECT id_procedimento, descricao FROM licitacao_procedimentos ORDER BY descricao')->fetchAll();
$modalidades  = $pdo->query('SELECT id_modalidade, descricao FROM licitacao_modalidades ORDER BY descricao')->fetchAll();
$tipos        = $pdo->query('SELECT id_tipo_licitacao, descricao FROM licitacao_tipos ORDER BY descricao')->fetchAll();
$finalidades  = $pdo->query('SELECT id_finalidade, descricao FROM licitacao_finalidades ORDER BY descricao')->fetchAll();
$regimes      = $pdo->query('SELECT id_regime_execucao, descricao FROM licitacao_regimes_execucao ORDER BY descricao')->fetchAll();
$situacoes    = $pdo->query('SELECT id_situacao, descricao FROM licitacao_situacoes ORDER BY id_situacao')->fetchAll();
$servidores   = $pdo->query('SELECT id_servidor, nome_completo FROM servidores WHERE ativo = 1 ORDER BY nome_completo')->fetchAll();
$fornecedoresDisponiveis = $pdo->query('SELECT id_fornecedor, razao_social FROM fornecedores WHERE ativo = 1 ORDER BY razao_social')->fetchAll();

$licitacao = [
    'id_orgao' => '', 'id_procedimento' => '', 'id_modalidade' => '', 'id_tipo_licitacao' => '',
    'id_finalidade' => '', 'id_regime_execucao' => '', 'id_situacao' => '', 'id_pregoeiro' => '',
    'numero_processo' => '', 'numero_licitacao' => '', 'ano_exercicio' => date('Y'),
    'data_abertura' => '', 'data_homologacao' => '', 'data_publicacao' => '',
    'valor_estimado' => '', 'valor_despesa' => '', 'objeto' => '', 'arquivo' => '', 'status' => 1,
];
$participantes = [];
$erros = [];

if ($editando) {
    $stmt = $pdo->prepare('SELECT * FROM licitacoes WHERE id_licitacao = :id');
    $stmt->execute(['id' => $id]);
    $registro = $stmt->fetch();
    if (!$registro) {
        header('Location: licitacoes.php');
        exit;
    }
    $licitacao = $registro;

    $stmtPart = $pdo->prepare(
        'SELECT lp.id, lp.vencedor, f.id_fornecedor, f.razao_social
         FROM licitacoes_participantes lp
         JOIN fornecedores f ON f.id_fornecedor = lp.id_fornecedor
         WHERE lp.id_licitacao = :id
         ORDER BY lp.vencedor DESC, f.razao_social'
    );
    $stmtPart->execute(['id' => $id]);
    $participantes = $stmtPart->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['id_orgao','id_procedimento','id_modalidade','id_tipo_licitacao','id_finalidade','id_regime_execucao','id_situacao','id_pregoeiro'] as $campo) {
        $licitacao[$campo] = $_POST[$campo] ?: null;
    }
    $licitacao['numero_processo']  = trim($_POST['numero_processo'] ?? '');
    $licitacao['numero_licitacao'] = trim($_POST['numero_licitacao'] ?? '');
    $licitacao['ano_exercicio']    = $_POST['ano_exercicio'] ?: null;
    $licitacao['data_abertura']    = $_POST['data_abertura'] ?: null;
    $licitacao['data_homologacao'] = $_POST['data_homologacao'] ?: null;
    $licitacao['data_publicacao']  = $_POST['data_publicacao'] ?: null;
    $licitacao['valor_estimado']   = $_POST['valor_estimado'] !== '' ? str_replace(',', '.', $_POST['valor_estimado']) : null;
    $licitacao['valor_despesa']    = $_POST['valor_despesa'] !== '' ? str_replace(',', '.', $_POST['valor_despesa']) : null;
    $licitacao['objeto']           = trim($_POST['objeto'] ?? '');
    $licitacao['status']           = isset($_POST['status']) ? 1 : 0;

    if ($licitacao['numero_processo'] === '' && $licitacao['objeto'] === '') {
        $erros[] = 'Informe pelo menos o número do processo ou o objeto da licitação.';
    }

    $novoArquivo = null;
    if (!empty($_FILES['arquivo']['name'])) {
        $extensao = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));
        $tamanhoMaximo = 10 * 1024 * 1024;

        if ($_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
            $erros[] = 'Falha ao enviar o arquivo. Tente novamente.';
        } elseif ($extensao !== 'pdf') {
            $erros[] = 'O anexo precisa ser um arquivo PDF.';
        } elseif ($_FILES['arquivo']['size'] > $tamanhoMaximo) {
            $erros[] = 'O arquivo deve ter no máximo 10MB.';
        } else {
            $novoArquivo = 'licitacao_' . uniqid() . '.pdf';
            $pastaDestino = __DIR__ . '/assets/uploads/licitacoes/';
            if (!is_dir($pastaDestino)) {
                mkdir($pastaDestino, 0755, true);
            }
            if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], $pastaDestino . $novoArquivo)) {
                $erros[] = 'Não foi possível salvar o arquivo no servidor.';
                $novoArquivo = null;
            }
        }
    }

    if (empty($erros)) {
        if ($novoArquivo) {
            if ($editando && !empty($licitacao['arquivo'])) {
                @unlink(__DIR__ . '/assets/uploads/licitacoes/' . $licitacao['arquivo']);
            }
            $licitacao['arquivo'] = $novoArquivo;
        }

        $parametros = [
            'id_orgao' => $licitacao['id_orgao'], 'id_procedimento' => $licitacao['id_procedimento'],
            'id_modalidade' => $licitacao['id_modalidade'], 'id_tipo_licitacao' => $licitacao['id_tipo_licitacao'],
            'id_finalidade' => $licitacao['id_finalidade'], 'id_regime_execucao' => $licitacao['id_regime_execucao'],
            'id_situacao' => $licitacao['id_situacao'], 'id_pregoeiro' => $licitacao['id_pregoeiro'],
            'numero_processo' => $licitacao['numero_processo'] ?: null,
            'numero_licitacao' => $licitacao['numero_licitacao'] ?: null,
            'ano_exercicio' => $licitacao['ano_exercicio'],
            'data_abertura' => $licitacao['data_abertura'], 'data_homologacao' => $licitacao['data_homologacao'],
            'data_publicacao' => $licitacao['data_publicacao'], 'valor_estimado' => $licitacao['valor_estimado'],
            'valor_despesa' => $licitacao['valor_despesa'], 'objeto' => $licitacao['objeto'] ?: null,
            'arquivo' => $licitacao['arquivo'] ?: null, 'status' => $licitacao['status'],
        ];

        if ($editando) {
            $parametros['id'] = $id;
            $pdo->prepare(
                'UPDATE licitacoes SET id_orgao=:id_orgao, id_procedimento=:id_procedimento, id_modalidade=:id_modalidade,
                    id_tipo_licitacao=:id_tipo_licitacao, id_finalidade=:id_finalidade, id_regime_execucao=:id_regime_execucao,
                    id_situacao=:id_situacao, id_pregoeiro=:id_pregoeiro, numero_processo=:numero_processo,
                    numero_licitacao=:numero_licitacao, ano_exercicio=:ano_exercicio, data_abertura=:data_abertura,
                    data_homologacao=:data_homologacao, data_publicacao=:data_publicacao, valor_estimado=:valor_estimado,
                    valor_despesa=:valor_despesa, objeto=:objeto, arquivo=:arquivo, status=:status
                 WHERE id_licitacao=:id'
            )->execute($parametros);
        } else {
            $pdo->prepare(
                'INSERT INTO licitacoes (id_orgao, id_procedimento, id_modalidade, id_tipo_licitacao, id_finalidade,
                    id_regime_execucao, id_situacao, id_pregoeiro, numero_processo, numero_licitacao, ano_exercicio,
                    data_abertura, data_homologacao, data_publicacao, valor_estimado, valor_despesa, objeto, arquivo, status)
                 VALUES (:id_orgao, :id_procedimento, :id_modalidade, :id_tipo_licitacao, :id_finalidade,
                    :id_regime_execucao, :id_situacao, :id_pregoeiro, :numero_processo, :numero_licitacao, :ano_exercicio,
                    :data_abertura, :data_homologacao, :data_publicacao, :valor_estimado, :valor_despesa, :objeto, :arquivo, :status)'
            )->execute($parametros);
        }
        header('Location: licitacoes.php?ok=1');
        exit;
    }
}

include __DIR__ . '/includes/header.php';

function selecionado($atual, $valor): string
{
    return (string) $atual === (string) $valor ? 'selected' : '';
}
?>

<h1 class="pagina__titulo"><?= $editando ? 'Editar licitação' : 'Nova licitação' ?></h1>
<p class="pagina__subtitulo"><a href="licitacoes.php">← Voltar para a lista</a></p>

<?php if (!empty($erros)): ?>
  <div class="alerta alerta-erro" style="max-width:none;">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <?= implode('<br>', array_map('htmlspecialchars', $erros)) ?>
  </div>
<?php endif; ?>

<form class="painel" method="post" enctype="multipart/form-data">
  <h2 class="painel__titulo"><i class="fa-solid fa-clipboard-list"></i> Identificação</h2>
  <div class="form-grid">
    <div class="campo">
      <label for="numero_processo">Número do processo</label>
      <input type="text" id="numero_processo" name="numero_processo" maxlength="50" placeholder="ex: 012/2025" value="<?= htmlspecialchars($licitacao['numero_processo'] ?? '') ?>">
    </div>

    <div class="campo">
      <label for="numero_licitacao">Número da licitação</label>
      <input type="text" id="numero_licitacao" name="numero_licitacao" maxlength="20" placeholder="ex: 003/2025" value="<?= htmlspecialchars($licitacao['numero_licitacao'] ?? '') ?>">
    </div>

    <div class="campo">
      <label for="ano_exercicio">Exercício / Ano</label>
      <input type="number" id="ano_exercicio" name="ano_exercicio" min="2000" max="2100" value="<?= htmlspecialchars($licitacao['ano_exercicio'] ?? '') ?>">
    </div>

    <div class="campo">
      <label for="id_orgao">Órgão</label>
      <select id="id_orgao" name="id_orgao">
        <option value="">Selecione...</option>
        <?php foreach ($orgaos as $o): ?>
          <option value="<?= $o['id_orgao'] ?>" <?= selecionado($licitacao['id_orgao'], $o['id_orgao']) ?>><?= htmlspecialchars($o['descricao']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="campo">
      <label for="id_pregoeiro">Pregoeiro(a) responsável</label>
      <select id="id_pregoeiro" name="id_pregoeiro">
        <option value="">Não se aplica</option>
        <?php foreach ($servidores as $s): ?>
          <option value="<?= $s['id_servidor'] ?>" <?= selecionado($licitacao['id_pregoeiro'], $s['id_servidor']) ?>><?= htmlspecialchars($s['nome_completo']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="campo">
      <label for="id_situacao">Situação</label>
      <select id="id_situacao" name="id_situacao">
        <option value="">Selecione...</option>
        <?php foreach ($situacoes as $s): ?>
          <option value="<?= $s['id_situacao'] ?>" <?= selecionado($licitacao['id_situacao'], $s['id_situacao']) ?>><?= htmlspecialchars($s['descricao']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="campo campo-largo">
      <label for="objeto">Objeto</label>
      <textarea id="objeto" name="objeto" rows="4"><?= htmlspecialchars($licitacao['objeto'] ?? '') ?></textarea>
    </div>
  </div>

  <h2 class="painel__titulo" style="margin-top:1.75rem;"><i class="fa-solid fa-gavel"></i> Classificação</h2>
  <div class="form-grid">
    <div class="campo">
      <label for="id_modalidade">Modalidade</label>
      <select id="id_modalidade" name="id_modalidade">
        <option value="">Selecione...</option>
        <?php foreach ($modalidades as $m): ?>
          <option value="<?= $m['id_modalidade'] ?>" <?= selecionado($licitacao['id_modalidade'], $m['id_modalidade']) ?>><?= htmlspecialchars($m['descricao']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="campo">
      <label for="id_procedimento">Procedimento</label>
      <select id="id_procedimento" name="id_procedimento">
        <option value="">Selecione...</option>
        <?php foreach ($procedimentos as $p): ?>
          <option value="<?= $p['id_procedimento'] ?>" <?= selecionado($licitacao['id_procedimento'], $p['id_procedimento']) ?>><?= htmlspecialchars($p['descricao']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="campo">
      <label for="id_tipo_licitacao">Tipo</label>
      <select id="id_tipo_licitacao" name="id_tipo_licitacao">
        <option value="">Selecione...</option>
        <?php foreach ($tipos as $t): ?>
          <option value="<?= $t['id_tipo_licitacao'] ?>" <?= selecionado($licitacao['id_tipo_licitacao'], $t['id_tipo_licitacao']) ?>><?= htmlspecialchars($t['descricao']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="campo">
      <label for="id_finalidade">Finalidade</label>
      <select id="id_finalidade" name="id_finalidade">
        <option value="">Selecione...</option>
        <?php foreach ($finalidades as $f): ?>
          <option value="<?= $f['id_finalidade'] ?>" <?= selecionado($licitacao['id_finalidade'], $f['id_finalidade']) ?>><?= htmlspecialchars($f['descricao']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="campo">
      <label for="id_regime_execucao">Regime de execução</label>
      <select id="id_regime_execucao" name="id_regime_execucao">
        <option value="">Selecione...</option>
        <?php foreach ($regimes as $r): ?>
          <option value="<?= $r['id_regime_execucao'] ?>" <?= selecionado($licitacao['id_regime_execucao'], $r['id_regime_execucao']) ?>><?= htmlspecialchars($r['descricao']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <h2 class="painel__titulo" style="margin-top:1.75rem;"><i class="fa-solid fa-calendar-days"></i> Datas e valores</h2>
  <div class="form-grid">
    <div class="campo">
      <label for="data_abertura">Data de abertura</label>
      <input type="date" id="data_abertura" name="data_abertura" value="<?= htmlspecialchars($licitacao['data_abertura'] ?? '') ?>">
    </div>
    <div class="campo">
      <label for="data_homologacao">Data de homologação</label>
      <input type="date" id="data_homologacao" name="data_homologacao" value="<?= htmlspecialchars($licitacao['data_homologacao'] ?? '') ?>">
    </div>
    <div class="campo">
      <label for="data_publicacao">Data de publicação</label>
      <input type="date" id="data_publicacao" name="data_publicacao" value="<?= htmlspecialchars($licitacao['data_publicacao'] ?? '') ?>">
    </div>
    <div class="campo">
      <label for="valor_estimado">Valor estimado (R$)</label>
      <input type="text" id="valor_estimado" name="valor_estimado" placeholder="0,00" value="<?= htmlspecialchars($licitacao['valor_estimado'] ?? '') ?>">
    </div>
    <div class="campo">
      <label for="valor_despesa">Valor da despesa (R$)</label>
      <input type="text" id="valor_despesa" name="valor_despesa" placeholder="0,00" value="<?= htmlspecialchars($licitacao['valor_despesa'] ?? '') ?>">
    </div>
  </div>

  <h2 class="painel__titulo" style="margin-top:1.75rem;"><i class="fa-solid fa-paperclip"></i> Anexo e publicação</h2>
  <div class="form-grid">
    <div class="campo campo-largo">
      <?php if (!empty($licitacao['arquivo'])): ?>
        <p style="margin:0 0 .5rem;">
          <a href="assets/uploads/licitacoes/<?= htmlspecialchars($licitacao['arquivo']) ?>" target="_blank">
            <i class="fa-solid fa-file-pdf"></i> Ver arquivo atual
          </a>
        </p>
      <?php endif; ?>
      <label for="arquivo">Arquivo (PDF)</label>
      <input type="file" id="arquivo" name="arquivo" accept=".pdf">
      <small class="ajuda">Somente PDF, até 10MB. <?= $editando ? 'Envie um novo arquivo apenas se quiser substituir o atual.' : '' ?></small>
    </div>

    <div class="campo campo-largo">
      <div class="campo-checkbox">
        <input type="checkbox" id="status" name="status" <?= $licitacao['status'] ? 'checked' : '' ?>>
        <label for="status">Publicada (visível no portal)</label>
      </div>
    </div>
  </div>

  <div class="form-acoes">
    <button type="submit" class="btn btn-primario" style="width:auto;">
      <i class="fa-solid fa-floppy-disk"></i> Salvar licitação
    </button>
    <a href="licitacoes.php" class="btn btn-secundario">Cancelar</a>
  </div>
</form>

<?php if ($editando): ?>
  <div class="painel" style="margin-top:1.25rem;">
    <h2 class="painel__titulo"><i class="fa-solid fa-people-group"></i> Empresas participantes</h2>
    <p class="pagina__subtitulo" style="margin-bottom:1rem;">Registre as empresas que participaram do certame e marque a vencedora.</p>

    <?php if (empty($participantes)): ?>
      <p style="color:var(--texto-claro); font-size:.85rem; margin-bottom:1.25rem;">Nenhuma empresa participante cadastrada ainda.</p>
    <?php else: ?>
      <table class="tabela-simples" style="margin-bottom:1.25rem;">
        <thead>
          <tr>
            <th>Empresa</th>
            <th>Situação</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($participantes as $p): ?>
            <tr>
              <td><?= htmlspecialchars($p['razao_social']) ?></td>
              <td>
                <?php if ($p['vencedor']): ?>
                  <span class="badge badge-ok">Vencedora</span>
                <?php else: ?>
                  <span class="badge badge-inativo">Participante</span>
                <?php endif; ?>
              </td>
              <td>
                <form method="post" action="licitacao-participante-excluir.php" onsubmit="return confirm('Remover esta empresa da lista de participantes?');" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $p['id'] ?>">
                  <input type="hidden" name="id_licitacao" value="<?= $id ?>">
                  <button type="submit" class="btn-icone perigo" title="Remover"><i class="fa-solid fa-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <?php if (empty($fornecedoresDisponiveis)): ?>
      <p style="color:var(--texto-claro); font-size:.85rem;">Cadastre fornecedores primeiro para poder vinculá-los aqui.</p>
    <?php else: ?>
      <form method="post" action="licitacao-participante-salvar.php">
        <input type="hidden" name="id_licitacao" value="<?= $id ?>">
        <div class="form-grid">
          <div class="campo">
            <label for="part_id_fornecedor">Empresa</label>
            <select id="part_id_fornecedor" name="id_fornecedor" required>
              <option value="">Selecione...</option>
              <?php foreach ($fornecedoresDisponiveis as $f): ?>
                <option value="<?= $f['id_fornecedor'] ?>"><?= htmlspecialchars($f['razao_social']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="campo">
            <div class="campo-checkbox" style="margin-top:1.6rem;">
              <input type="checkbox" id="part_vencedor" name="vencedor">
              <label for="part_vencedor">Esta é a empresa vencedora</label>
            </div>
          </div>
        </div>
        <div class="form-acoes">
          <button type="submit" class="btn btn-secundario btn-sm" style="width:auto;">
            <i class="fa-solid fa-plus"></i> Adicionar participante
          </button>
        </div>
      </form>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
