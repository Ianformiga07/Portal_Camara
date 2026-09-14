<?php
require_once __DIR__ . '/includes/auth.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$editando = $id !== null;
$tituloPagina = $editando ? 'Editar contrato' : 'Novo contrato';

$licitacoesDisponiveis = $pdo->query('SELECT id_licitacao, numero_processo, objeto FROM licitacoes ORDER BY id_licitacao DESC')->fetchAll();
$tiposContratacao = $pdo->query('SELECT id_tipo_contratacao, descricao FROM tipos_contratacao ORDER BY descricao')->fetchAll();
$finalidades = $pdo->query('SELECT id_finalidade, descricao FROM licitacao_finalidades ORDER BY descricao')->fetchAll();
$fornecedores = $pdo->query('SELECT id_fornecedor, razao_social FROM fornecedores WHERE ativo = 1 ORDER BY razao_social')->fetchAll();
$servidores = $pdo->query('SELECT id_servidor, nome_completo FROM servidores WHERE ativo = 1 ORDER BY nome_completo')->fetchAll();
$situacoes = $pdo->query('SELECT id_situacao, descricao FROM contrato_situacoes ORDER BY id_situacao')->fetchAll();

$contrato = [
    'id_licitacao' => '', 'id_tipo_contratacao' => '', 'id_finalidade' => '',
    'numero_contrato' => '', 'ano_exercicio' => date('Y'),
    'inicio_vigencia' => '', 'fim_vigencia' => '', 'data_publicacao_extrato' => '',
    'valor_estimado' => '', 'id_fornecedor' => '', 'id_fiscal' => '', 'id_gestor' => '', 'id_situacao' => '',
    'objeto' => '', 'arquivo' => '', 'status' => 1,
];
$aditivos = [];
$erros = [];

if ($editando) {
    $stmt = $pdo->prepare('SELECT * FROM contratos WHERE id_contrato = :id');
    $stmt->execute(['id' => $id]);
    $registro = $stmt->fetch();
    if (!$registro) {
        header('Location: contratos.php');
        exit;
    }
    $contrato = $registro;

    $stmtAdit = $pdo->prepare('SELECT * FROM contratos_aditivos WHERE id_contrato = :id ORDER BY data_aditivo DESC');
    $stmtAdit->execute(['id' => $id]);
    $aditivos = $stmtAdit->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['id_licitacao','id_tipo_contratacao','id_finalidade','id_fornecedor','id_fiscal','id_gestor','id_situacao'] as $campo) {
        $contrato[$campo] = $_POST[$campo] ?: null;
    }
    $contrato['numero_contrato']         = trim($_POST['numero_contrato'] ?? '');
    $contrato['ano_exercicio']           = $_POST['ano_exercicio'] ?: null;
    $contrato['inicio_vigencia']         = $_POST['inicio_vigencia'] ?: null;
    $contrato['fim_vigencia']            = $_POST['fim_vigencia'] ?: null;
    $contrato['data_publicacao_extrato'] = $_POST['data_publicacao_extrato'] ?: null;
    $contrato['valor_estimado']          = $_POST['valor_estimado'] !== '' ? str_replace(',', '.', $_POST['valor_estimado']) : null;
    $contrato['objeto']                  = trim($_POST['objeto'] ?? '');
    $contrato['status']                  = isset($_POST['status']) ? 1 : 0;

    if ($contrato['numero_contrato'] === '') {
        $erros[] = 'Informe o número do contrato.';
    }
    if (!$contrato['id_fornecedor']) {
        $erros[] = 'Selecione a empresa contratada.';
    }
    if ($contrato['objeto'] === '') {
        $erros[] = 'Descreva o objeto do contrato.';
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
            $novoArquivo = 'contrato_' . uniqid() . '.pdf';
            $pastaDestino = __DIR__ . '/assets/uploads/contratos/';
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
            if ($editando && !empty($contrato['arquivo'])) {
                @unlink(__DIR__ . '/assets/uploads/contratos/' . $contrato['arquivo']);
            }
            $contrato['arquivo'] = $novoArquivo;
        }

        $parametros = [
            'id_licitacao' => $contrato['id_licitacao'], 'id_tipo_contratacao' => $contrato['id_tipo_contratacao'],
            'id_finalidade' => $contrato['id_finalidade'], 'numero_contrato' => $contrato['numero_contrato'],
            'ano_exercicio' => $contrato['ano_exercicio'], 'inicio_vigencia' => $contrato['inicio_vigencia'],
            'fim_vigencia' => $contrato['fim_vigencia'], 'data_publicacao_extrato' => $contrato['data_publicacao_extrato'],
            'valor_estimado' => $contrato['valor_estimado'], 'id_fornecedor' => $contrato['id_fornecedor'],
            'id_fiscal' => $contrato['id_fiscal'], 'id_gestor' => $contrato['id_gestor'],
            'id_situacao' => $contrato['id_situacao'], 'objeto' => $contrato['objeto'],
            'arquivo' => $contrato['arquivo'] ?: null, 'status' => $contrato['status'],
        ];

        if ($editando) {
            $parametros['id'] = $id;
            $pdo->prepare(
                'UPDATE contratos SET id_licitacao=:id_licitacao, id_tipo_contratacao=:id_tipo_contratacao,
                    id_finalidade=:id_finalidade, numero_contrato=:numero_contrato, ano_exercicio=:ano_exercicio,
                    inicio_vigencia=:inicio_vigencia, fim_vigencia=:fim_vigencia, data_publicacao_extrato=:data_publicacao_extrato,
                    valor_estimado=:valor_estimado, id_fornecedor=:id_fornecedor, id_fiscal=:id_fiscal, id_gestor=:id_gestor,
                    id_situacao=:id_situacao, objeto=:objeto, arquivo=:arquivo, status=:status
                 WHERE id_contrato=:id'
            )->execute($parametros);
        } else {
            $pdo->prepare(
                'INSERT INTO contratos (id_licitacao, id_tipo_contratacao, id_finalidade, numero_contrato, ano_exercicio,
                    inicio_vigencia, fim_vigencia, data_publicacao_extrato, valor_estimado, id_fornecedor, id_fiscal,
                    id_gestor, id_situacao, objeto, arquivo, status)
                 VALUES (:id_licitacao, :id_tipo_contratacao, :id_finalidade, :numero_contrato, :ano_exercicio,
                    :inicio_vigencia, :fim_vigencia, :data_publicacao_extrato, :valor_estimado, :id_fornecedor, :id_fiscal,
                    :id_gestor, :id_situacao, :objeto, :arquivo, :status)'
            )->execute($parametros);
        }
        header('Location: contratos.php?ok=1');
        exit;
    }
}

include __DIR__ . '/includes/header.php';

function selecionadoCon($atual, $valor): string
{
    return (string) $atual === (string) $valor ? 'selected' : '';
}
?>

<h1 class="pagina__titulo"><?= $editando ? 'Editar contrato' : 'Novo contrato' ?></h1>
<p class="pagina__subtitulo"><a href="contratos.php">← Voltar para a lista</a></p>

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
      <label for="numero_contrato">Número do contrato *</label>
      <input type="text" id="numero_contrato" name="numero_contrato" maxlength="20" required placeholder="ex: 007/2025" value="<?= htmlspecialchars($contrato['numero_contrato']) ?>">
    </div>

    <div class="campo">
      <label for="ano_exercicio">Exercício / Ano</label>
      <input type="number" id="ano_exercicio" name="ano_exercicio" min="2000" max="2100" value="<?= htmlspecialchars($contrato['ano_exercicio'] ?? '') ?>">
    </div>

    <div class="campo">
      <label for="id_fornecedor">Contratada *</label>
      <select id="id_fornecedor" name="id_fornecedor" required>
        <option value="">Selecione...</option>
        <?php foreach ($fornecedores as $f): ?>
          <option value="<?= $f['id_fornecedor'] ?>" <?= selecionadoCon($contrato['id_fornecedor'], $f['id_fornecedor']) ?>><?= htmlspecialchars($f['razao_social']) ?></option>
        <?php endforeach; ?>
      </select>
      <?php if (empty($fornecedores)): ?>
        <small class="ajuda">Nenhum fornecedor cadastrado. <a href="fornecedor-form.php">Cadastrar agora</a>.</small>
      <?php endif; ?>
    </div>

    <div class="campo">
      <label for="id_licitacao">Licitação de origem</label>
      <select id="id_licitacao" name="id_licitacao">
        <option value="">Contratação direta / não se aplica</option>
        <?php foreach ($licitacoesDisponiveis as $l): ?>
          <option value="<?= $l['id_licitacao'] ?>" <?= selecionadoCon($contrato['id_licitacao'], $l['id_licitacao']) ?>>
            <?= htmlspecialchars($l['numero_processo'] ?: ('#' . $l['id_licitacao'])) ?> — <?= htmlspecialchars(mb_strimwidth($l['objeto'] ?? '', 0, 40, '...')) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="campo campo-largo">
      <label for="objeto">Objeto *</label>
      <textarea id="objeto" name="objeto" rows="4" required><?= htmlspecialchars($contrato['objeto'] ?? '') ?></textarea>
    </div>
  </div>

  <h2 class="painel__titulo" style="margin-top:1.75rem;"><i class="fa-solid fa-gavel"></i> Classificação</h2>
  <div class="form-grid">
    <div class="campo">
      <label for="id_tipo_contratacao">Tipo de contratação</label>
      <select id="id_tipo_contratacao" name="id_tipo_contratacao">
        <option value="">Selecione...</option>
        <?php foreach ($tiposContratacao as $t): ?>
          <option value="<?= $t['id_tipo_contratacao'] ?>" <?= selecionadoCon($contrato['id_tipo_contratacao'], $t['id_tipo_contratacao']) ?>><?= htmlspecialchars($t['descricao']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="campo">
      <label for="id_finalidade">Finalidade</label>
      <select id="id_finalidade" name="id_finalidade">
        <option value="">Selecione...</option>
        <?php foreach ($finalidades as $f): ?>
          <option value="<?= $f['id_finalidade'] ?>" <?= selecionadoCon($contrato['id_finalidade'], $f['id_finalidade']) ?>><?= htmlspecialchars($f['descricao']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="campo">
      <label for="id_situacao">Situação</label>
      <select id="id_situacao" name="id_situacao">
        <option value="">Selecione...</option>
        <?php foreach ($situacoes as $s): ?>
          <option value="<?= $s['id_situacao'] ?>" <?= selecionadoCon($contrato['id_situacao'], $s['id_situacao']) ?>><?= htmlspecialchars($s['descricao']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <h2 class="painel__titulo" style="margin-top:1.75rem;"><i class="fa-solid fa-user-shield"></i> Responsáveis</h2>
  <div class="form-grid">
    <div class="campo">
      <label for="id_fiscal">Fiscal do contrato</label>
      <select id="id_fiscal" name="id_fiscal">
        <option value="">Não definido</option>
        <?php foreach ($servidores as $s): ?>
          <option value="<?= $s['id_servidor'] ?>" <?= selecionadoCon($contrato['id_fiscal'], $s['id_servidor']) ?>><?= htmlspecialchars($s['nome_completo']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="campo">
      <label for="id_gestor">Gestor do contrato</label>
      <select id="id_gestor" name="id_gestor">
        <option value="">Não definido</option>
        <?php foreach ($servidores as $s): ?>
          <option value="<?= $s['id_servidor'] ?>" <?= selecionadoCon($contrato['id_gestor'], $s['id_servidor']) ?>><?= htmlspecialchars($s['nome_completo']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <h2 class="painel__titulo" style="margin-top:1.75rem;"><i class="fa-solid fa-calendar-days"></i> Vigência e valores</h2>
  <div class="form-grid">
    <div class="campo">
      <label for="inicio_vigencia">Início da vigência</label>
      <input type="date" id="inicio_vigencia" name="inicio_vigencia" value="<?= htmlspecialchars($contrato['inicio_vigencia'] ?? '') ?>">
    </div>
    <div class="campo">
      <label for="fim_vigencia">Fim da vigência</label>
      <input type="date" id="fim_vigencia" name="fim_vigencia" value="<?= htmlspecialchars($contrato['fim_vigencia'] ?? '') ?>">
    </div>
    <div class="campo">
      <label for="data_publicacao_extrato">Data de publicação do extrato</label>
      <input type="date" id="data_publicacao_extrato" name="data_publicacao_extrato" value="<?= htmlspecialchars($contrato['data_publicacao_extrato'] ?? '') ?>">
    </div>
    <div class="campo">
      <label for="valor_estimado">Valor do contrato (R$)</label>
      <input type="text" id="valor_estimado" name="valor_estimado" placeholder="0,00" value="<?= htmlspecialchars($contrato['valor_estimado'] ?? '') ?>">
    </div>
  </div>

  <h2 class="painel__titulo" style="margin-top:1.75rem;"><i class="fa-solid fa-paperclip"></i> Anexo e publicação</h2>
  <div class="form-grid">
    <div class="campo campo-largo">
      <?php if (!empty($contrato['arquivo'])): ?>
        <p style="margin:0 0 .5rem;">
          <a href="assets/uploads/contratos/<?= htmlspecialchars($contrato['arquivo']) ?>" target="_blank">
            <i class="fa-solid fa-file-pdf"></i> Ver contrato atual
          </a>
        </p>
      <?php endif; ?>
      <label for="arquivo">Contrato assinado (PDF)</label>
      <input type="file" id="arquivo" name="arquivo" accept=".pdf">
      <small class="ajuda">Somente PDF, até 10MB. <?= $editando ? 'Envie um novo arquivo apenas se quiser substituir o atual.' : '' ?></small>
    </div>

    <div class="campo campo-largo">
      <div class="campo-checkbox">
        <input type="checkbox" id="status" name="status" <?= $contrato['status'] ? 'checked' : '' ?>>
        <label for="status">Publicado (visível no portal)</label>
      </div>
    </div>
  </div>

  <div class="form-acoes">
    <button type="submit" class="btn btn-primario" style="width:auto;">
      <i class="fa-solid fa-floppy-disk"></i> Salvar contrato
    </button>
    <a href="contratos.php" class="btn btn-secundario">Cancelar</a>
  </div>
</form>

<?php if ($editando): ?>
  <div class="painel" style="margin-top:1.25rem;">
    <h2 class="painel__titulo"><i class="fa-solid fa-file-circle-plus"></i> Termos aditivos</h2>
    <p class="pagina__subtitulo" style="margin-bottom:1rem;">O TCE-TO exige que os termos aditivos estejam disponíveis junto ao contrato original.</p>

    <?php if (empty($aditivos)): ?>
      <p style="color:var(--texto-claro); font-size:.85rem; margin-bottom:1.25rem;">Nenhum termo aditivo cadastrado.</p>
    <?php else: ?>
      <table class="tabela-simples" style="margin-bottom:1.25rem;">
        <thead>
          <tr>
            <th>Número</th>
            <th>Data</th>
            <th>Descrição</th>
            <th></th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($aditivos as $a): ?>
            <tr>
              <td><?= htmlspecialchars($a['numero_aditivo'] ?: '—') ?></td>
              <td><?= $a['data_aditivo'] ? date('d/m/Y', strtotime($a['data_aditivo'])) : '—' ?></td>
              <td><?= htmlspecialchars($a['descricao'] ?: '—') ?></td>
              <td>
                <?php if ($a['arquivo']): ?>
                  <a class="btn-icone" href="assets/uploads/contratos/<?= htmlspecialchars($a['arquivo']) ?>" target="_blank" title="Ver PDF">
                    <i class="fa-solid fa-file-pdf"></i>
                  </a>
                <?php endif; ?>
              </td>
              <td>
                <form method="post" action="contrato-aditivo-excluir.php" onsubmit="return confirm('Remover este termo aditivo?');" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $a['id'] ?>">
                  <input type="hidden" name="id_contrato" value="<?= $id ?>">
                  <button type="submit" class="btn-icone perigo" title="Remover"><i class="fa-solid fa-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <form method="post" action="contrato-aditivo-salvar.php" enctype="multipart/form-data">
      <input type="hidden" name="id_contrato" value="<?= $id ?>">
      <div class="form-grid">
        <div class="campo">
          <label for="adit_numero">Número do aditivo</label>
          <input type="text" id="adit_numero" name="numero_aditivo" maxlength="20" placeholder="ex: 1º Termo Aditivo">
        </div>
        <div class="campo">
          <label for="adit_data">Data</label>
          <input type="date" id="adit_data" name="data_aditivo">
        </div>
        <div class="campo campo-largo">
          <label for="adit_descricao">Descrição</label>
          <input type="text" id="adit_descricao" name="descricao" maxlength="255" placeholder="ex: Prorrogação de vigência por 12 meses">
        </div>
        <div class="campo campo-largo">
          <label for="adit_arquivo">Arquivo (PDF)</label>
          <input type="file" id="adit_arquivo" name="arquivo" accept=".pdf">
        </div>
      </div>
      <div class="form-acoes">
        <button type="submit" class="btn btn-secundario btn-sm" style="width:auto;">
          <i class="fa-solid fa-plus"></i> Adicionar termo aditivo
        </button>
      </div>
    </form>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
