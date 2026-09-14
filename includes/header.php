<?php
require_once __DIR__ . '/../config/database.php';

$brasaoUrl = null;
if ($pdo) {
    $historia = $pdo->query('SELECT imagem_brasao FROM historia_municipio ORDER BY id_historia LIMIT 1')->fetch();
    if ($historia && !empty($historia['imagem_brasao'])) {
        $brasaoUrl = 'gerenciador/assets/uploads/institucional/' . $historia['imagem_brasao'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Portal Oficial da Câmara Municipal de Ananás - Transparência, democracia e cidadania.">
  <title><?= $page_title ?? 'Câmara Municipal de Ananás' ?></title>

  <!-- Fontes Institucionais -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- CSS Principal -->
  <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>

<!-- ===================== TOPBAR ===================== -->
<div class="topbar">
  <div class="topbar-inner">
    <div class="topbar-contato">
      <a href="tel:+556334710000"><i class="fas fa-phone"></i> (63) 99286-3557</a>
      <a href="mailto:contato@camaraananas.to.gov.br"><i class="fas fa-envelope"></i> contato@camaraananas.to.gov.br</a>
      <span><i class="fas fa-map-marker-alt"></i> Praça da Câmara, S/N – Ananás/TO</span>
    </div>
    <div class="topbar-acoes">
      <button class="btn-acessibilidade" onclick="alterarFonte(-1)" title="Diminuir fonte"><i class="fas fa-text-height"></i> A-</button>
      <button class="btn-acessibilidade" onclick="alterarFonte(1)" title="Aumentar fonte"><i class="fas fa-text-height"></i> A+</button>
      <button class="btn-contraste" onclick="toggleContraste()"><i class="fas fa-adjust"></i> Contraste</button>
    </div>
  </div>
</div>

<!-- ===================== HEADER / BRANDING ===================== -->
<header class="header">
  <div class="branding">
    <div class="logo-wrapper">
      <div class="logo-brasao">
        <?php if ($brasaoUrl): ?>
          <img src="<?= htmlspecialchars($brasaoUrl) ?>" alt="Brasão da Câmara Municipal de Ananás" style="width:100%; height:100%; object-fit:contain;">
        <?php else: ?>
          <i class="fas fa-landmark"></i>
        <?php endif; ?>
      </div>
      <div class="logo-texto">
        <div class="nome-camara">Câmara Municipal de Ananás</div>
        <div class="cidade">Estado do Tocantins — Poder Legislativo Municipal</div>
      </div>
    </div>

    <button class="nav-toggle" id="navToggle" aria-label="Menu">
      <i class="fas fa-bars"></i>
    </button>

    <!-- ===================== NAVEGAÇÃO ===================== -->
    <nav class="navmenu">
      <ul id="mainNav">
        <li><a href="index.php" class="active">Início</a></li>

        <li class="dropdown">
          <a href="#">A Câmara <i class="fas fa-chevron-down"></i></a>
          <ul class="dropdown-menu-custom">
            <li><a href="historia.php">História</a></li>
            <li><a href="mesa-diretora.php">Mesa Diretora</a></li>
            <li><a href="vereadores.php">Vereadores</a></li>
            <li><a href="comissoes.php">Comissões</a></li>
            <li><a href="regimento.php">Regimento Interno</a></li>
            <li><a href="lei-organica.php">Lei Orgânica</a></li>
          </ul>
        </li>

        <li class="dropdown">
          <a href="#">Legislação <i class="fas fa-chevron-down"></i></a>
          <ul class="dropdown-menu-custom">
            <li><a href="leis.php">Leis Municipais</a></li>
            <li><a href="decretos.php">Decretos</a></li>
            <li><a href="resolucoes.php">Resoluções</a></li>
            <li><a href="portarias.php">Portarias</a></li>
            <li><a href="projetos.php">Projetos de Lei</a></li>
          </ul>
        </li>

        <li class="dropdown">
          <a href="#">Transparência <i class="fas fa-chevron-down"></i></a>
          <ul class="dropdown-menu-custom">
            <li><a href="portal-transparencia.php">Portal da Transparência</a></li>
            <li><a href="esic.php">e-SIC</a></li>
            <li><a href="ouvidoria.php">Ouvidoria</a></li>
            <li><a href="licitacoes.php">Licitações</a></li>
            <li><a href="contratos.php">Contratos</a></li>
            <li><a href="receitas-despesas.php">Receitas e Despesas</a></li>
            <li><a href="servidores.php">Servidores</a></li>
          </ul>
        </li>

        <li class="dropdown">
          <a href="#">Atividades <i class="fas fa-chevron-down"></i></a>
          <ul class="dropdown-menu-custom">
            <li><a href="sessoes.php">Sessões Plenárias</a></li>
            <li><a href="atas.php">Atas</a></li>
            <li><a href="pauta.php">Pauta</a></li>
            <li><a href="audiencias.php">Audiências Públicas</a></li>
            <li><a href="votacoes.php">Votações</a></li>
          </ul>
        </li>

        <li><a href="noticias.php">Notícias</a></li>
        <li><a href="diario-oficial.php">Diário Oficial</a></li>
        <li><a href="contato.php">Contato</a></li>
      </ul>
    </nav>
  </div>
</header>
<!-- Fim Header -->