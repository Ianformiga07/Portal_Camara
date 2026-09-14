<?php
require_once 'config/database.php';
require_once 'includes/funcoes.php';

$id = (int) ($_GET['id'] ?? 0);

if (!$pdo) {
    header('Location: servidores.php');
    exit;
}

$stmt = $pdo->prepare(
    "SELECT s.*, c.descricao AS cargo_descricao, d.descricao AS departamento_descricao,
            e.descricao AS escolaridade_descricao
     FROM servidores s
     LEFT JOIN cargos c ON c.id_cargo = s.id_cargo
     LEFT JOIN departamentos d ON d.id_departamento = s.id_departamento
     LEFT JOIN escolaridades e ON e.id_escolaridade = s.id_escolaridade
     WHERE s.id_servidor = :id AND s.ativo = 1"
);
$stmt->execute(['id' => $id]);
$servidor = $stmt->fetch();

if (!$servidor) {
    header('Location: servidores.php');
    exit;
}

$page_title = htmlspecialchars($servidor['nome_completo']) . ' — Câmara Municipal de Ananás';
include 'includes/header.php';

$tituloPaginaInterna = $servidor['nome_completo'];
$subtituloPaginaInterna = htmlspecialchars($servidor['cargo_descricao'] ?: 'Servidor') . ' — Portal da Transparência.';
include 'includes/pagina-header.php';
?>

<section class="pagina-interna-conteudo">
  <div class="section-container">
    <p style="margin-bottom:1.5rem;"><a href="servidores.php"><i class="fas fa-arrow-left"></i> Voltar para todos os servidores</a></p>

    <div class="vereador-perfil">
      <div class="vereador-perfil-foto">
        <?php if (!empty($servidor['foto_perfil'])): ?>
          <img src="gerenciador/assets/uploads/servidores/<?= htmlspecialchars($servidor['foto_perfil']) ?>" alt="<?= htmlspecialchars($servidor['nome_completo']) ?>">
        <?php else: ?>
          <i class="fas fa-user"></i>
        <?php endif; ?>
      </div>

      <div>
        <h2 class="vereador-perfil-nome"><?= htmlspecialchars($servidor['nome_completo']) ?></h2>

        <div class="vereador-perfil-tags">
          <?php if ($servidor['cargo_descricao']): ?><span class="tag-info"><i class="fas fa-briefcase"></i> <?= htmlspecialchars($servidor['cargo_descricao']) ?></span><?php endif; ?>
          <?php if ($servidor['departamento_descricao']): ?><span class="tag-info"><i class="fas fa-building"></i> <?= htmlspecialchars($servidor['departamento_descricao']) ?></span><?php endif; ?>
          <span class="tag-info dourado"><i class="fas fa-circle-check"></i> Ativo</span>
        </div>

        <div class="vereador-perfil-secao">
          <h3><i class="fas fa-id-card"></i> Dados funcionais</h3>
          <table class="tabela-mandatos">
            <tbody>
              <tr><td style="font-weight:700; width:40%;">CPF</td><td class="col-cpf" style="font-variant-numeric:tabular-nums; font-family:'Courier New',monospace;"><?= mascararCpf($servidor['cpf']) ?></td></tr>
              <tr><td style="font-weight:700;">Matrícula</td><td><?= htmlspecialchars($servidor['matricula'] ?: '—') ?></td></tr>
              <tr><td style="font-weight:700;">Cargo</td><td><?= htmlspecialchars($servidor['cargo_descricao'] ?: '—') ?></td></tr>
              <tr><td style="font-weight:700;">Departamento</td><td><?= htmlspecialchars($servidor['departamento_descricao'] ?: '—') ?></td></tr>
              <tr><td style="font-weight:700;">Escolaridade</td><td><?= htmlspecialchars($servidor['escolaridade_descricao'] ?: '—') ?></td></tr>
              <tr><td style="font-weight:700;">Data de admissão</td><td><?= $servidor['data_admissao'] ? date('d/m/Y', strtotime($servidor['data_admissao'])) : '—' ?></td></tr>
              <tr><td style="font-weight:700;">Situação</td><td><span class="badge-pill">Ativo</span></td></tr>
            </tbody>
          </table>
        </div>

        <div class="vereador-perfil-secao">
          <h3><i class="fas fa-sack-dollar"></i> Vencimentos</h3>
          <p style="color:var(--texto-claro); font-size:.88rem; line-height:1.6;">
            <i class="fas fa-circle-info"></i>
            Os dados de proventos, descontos e valor líquido dependem da integração com o sistema de folha de pagamento,
            ainda não disponível neste portal. Assim que a integração estiver ativa, essas informações passam a ser
            exibidas automaticamente nesta seção, em conformidade com a Lei de Acesso à Informação.
          </p>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
