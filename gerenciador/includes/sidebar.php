<?php
/**
 * Sidebar. Depende de $usuario (definido em includes/auth.php).
 * Os links apontam para páginas que serão construídas nas próximas
 * etapas — por enquanto só Dashboard existe de fato.
 */
$fotoUsuario = !empty($usuario['foto_perfil'])
    ? 'assets/uploads/servidores/' . htmlspecialchars($usuario['foto_perfil'])
    : 'assets/img/avatar-padrao.svg';
?>
<aside class="sidebar">
  <div class="sidebar__marca">
    <i class="fa-solid fa-landmark"></i>
    <span>Câmara de Ananás</span>
  </div>

  <div class="sidebar__usuario">
    <img src="<?= $fotoUsuario ?>" alt="Foto do usuário" onerror="this.onerror=null; this.src='assets/img/avatar-padrao.svg';">
    <div>
      <div class="nome"><?= htmlspecialchars($usuario['nome_completo']) ?></div>
      <div class="cargo"><?= htmlspecialchars($usuario['cargo'] ?? 'Servidor') ?></div>
    </div>
  </div>

  <nav>
    <div class="grupo-titulo">Principal</div>
    <a href="index.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>

    <div class="grupo-titulo">Institucional</div>
    <a href="a-camara.php"><i class="fa-solid fa-landmark-dome"></i> A Câmara</a>
    <a href="vereadores.php"><i class="fa-solid fa-users"></i> Vereadores</a>
    <a href="mesa-diretora.php"><i class="fa-solid fa-people-roof"></i> Mesa Diretora</a>
    <a href="comissoes.php"><i class="fa-solid fa-sitemap"></i> Comissões</a>
    <a href="noticias.php"><i class="fa-solid fa-newspaper"></i> Notícias</a>
    <a href="diario-oficial-edicoes.php"><i class="fa-solid fa-file-lines"></i> Diário Oficial</a>
    <a href="documentos.php"><i class="fa-solid fa-gavel"></i> Legislação</a>

    <div class="grupo-titulo">Compras e Licitações</div>
    <a href="licitacoes.php"><i class="fa-solid fa-file-contract"></i> Licitações</a>
    <a href="contratos.php"><i class="fa-solid fa-file-signature"></i> Contratos</a>
    <a href="fornecedores.php"><i class="fa-solid fa-building"></i> Fornecedores</a>
    <a href="compras.php"><i class="fa-solid fa-cart-shopping"></i> Compras e Suprimentos</a>

    <div class="grupo-titulo">Atendimento</div>
    <a href="manifestacoes.php"><i class="fa-solid fa-comments"></i> Ouvidoria</a>
    <a href="esic.php"><i class="fa-solid fa-scale-balanced"></i> e-SIC</a>

    <?php if (ehAdministrador()): ?>
    <div class="grupo-titulo">Administração</div>
    <a href="usuarios.php"><i class="fa-solid fa-user-shield"></i> Usuários e Permissões</a>
    <a href="servidores.php"><i class="fa-solid fa-id-card"></i> Recursos Humanos</a>
    <?php endif; ?>
  </nav>
</aside>
