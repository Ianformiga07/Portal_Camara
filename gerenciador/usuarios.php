<?php
require_once __DIR__ . '/includes/auth.php';

if (!ehAdministrador()) {
    header('Location: index.php');
    exit;
}

$tituloPagina = 'Usuários e Permissões';

$busca = trim($_GET['busca'] ?? '');

$condicoes = [];
$parametros = [];
if ($busca !== '') {
    $condicoes[] = '(s.nome_completo LIKE :busca OR s.cpf LIKE :busca)';
    $parametros['busca'] = '%' . $busca . '%';
}
$whereSql = $condicoes ? 'WHERE ' . implode(' AND ', $condicoes) : '';

$stmt = $pdo->prepare(
    "SELECT s.id_servidor, s.nome_completo, s.cpf, s.ativo, s.nivel_acesso, s.senha_hash,
            c.descricao AS cargo_descricao
     FROM servidores s
     LEFT JOIN cargos c ON c.id_cargo = s.id_cargo
     $whereSql
     ORDER BY s.nome_completo"
);
$stmt->execute($parametros);
$servidores = $stmt->fetchAll();

$rotulosNivel = [1 => 'Admin Master', 2 => 'Admin', 3 => 'Operador'];

include __DIR__ . '/includes/header.php';
?>

<h1 class="pagina__titulo">Usuários e Permissões</h1>
<p class="pagina__subtitulo">Controle de acesso ao gerenciador. Visível apenas para administradores.</p>

<?php if (!empty($_GET['ok'])): ?>
  <div class="alerta alerta-sucesso" style="max-width:none;">
    <i class="fa-solid fa-circle-check"></i> Acesso atualizado com sucesso.
  </div>
<?php endif; ?>

<div class="toolbar">
  <form class="toolbar__busca" method="get" style="max-width:400px;">
    <input type="text" name="busca" placeholder="Buscar por nome ou CPF..." value="<?= htmlspecialchars($busca) ?>">
    <button type="submit" class="btn btn-secundario"><i class="fa-solid fa-magnifying-glass"></i></button>
  </form>
</div>

<div class="painel">
  <?php if (empty($servidores)): ?>
    <div class="vazio">
      <i class="fa-solid fa-user-shield"></i>
      <p>Nenhum servidor encontrado.</p>
    </div>
  <?php else: ?>
    <table class="tabela-simples">
      <thead>
        <tr>
          <th>Nome</th>
          <th>Cargo</th>
          <th>Nível de acesso</th>
          <th>Senha definida</th>
          <th>Situação</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($servidores as $s): ?>
          <tr>
            <td class="titulo-linha"><?= htmlspecialchars($s['nome_completo']) ?></td>
            <td><?= htmlspecialchars($s['cargo_descricao'] ?: '—') ?></td>
            <td><?= htmlspecialchars($rotulosNivel[(int) $s['nivel_acesso']] ?? 'Operador') ?></td>
            <td>
              <?php if ($s['senha_hash']): ?>
                <span class="badge badge-ok">Definida</span>
              <?php else: ?>
                <span class="badge badge-inativo">Sem acesso</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($s['ativo']): ?>
                <span class="badge badge-ok">Ativo</span>
              <?php else: ?>
                <span class="badge badge-inativo">Inativo</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="coluna-acoes">
                <a class="btn-icone" href="usuario-permissoes.php?id=<?= $s['id_servidor'] ?>" title="Definir acesso">
                  <i class="fa-solid fa-key"></i>
                </a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
