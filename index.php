<?php
$page_title = 'Câmara Municipal de Ananás — Portal Institucional';
include 'includes/header.php';
require_once 'includes/funcoes.php';

// --- Notícias (destaque + recentes) ---
$noticiaPrincipal = null;
$noticiasRecentes = [];
if ($pdo) {
    $noticiaPrincipal = $pdo->query(
        "SELECT * FROM noticias WHERE status = 1 ORDER BY destaque DESC, publicado_em DESC LIMIT 1"
    )->fetch();

    if ($noticiaPrincipal) {
        $stmt = $pdo->prepare(
            "SELECT * FROM noticias WHERE status = 1 AND id_noticia != :id ORDER BY publicado_em DESC LIMIT 5"
        );
        $stmt->execute(['id' => $noticiaPrincipal['id_noticia']]);
        $noticiasRecentes = $stmt->fetchAll();
    }
}

// --- Diário Oficial (última edição + anteriores) + Documentos Recentes ---
$diarioAtual = null;
$diariosAnteriores = [];
$documentosRecentes = [];
if ($pdo) {
    $diarios = $pdo->query(
        "SELECT d.* FROM documentos d
         JOIN categorias_documentos c ON c.id_categoria = d.id_categoria
         WHERE c.slug = 'diario-oficial' AND d.status = 1
         ORDER BY d.data_publicacao DESC LIMIT 4"
    )->fetchAll();
    $diarioAtual = $diarios[0] ?? null;
    $diariosAnteriores = array_slice($diarios, 1);

    $documentosRecentes = $pdo->query(
        "SELECT d.numero_documento, d.titulo, d.data_publicacao, d.arquivo, c.descricao AS categoria_descricao
         FROM documentos d
         JOIN categorias_documentos c ON c.id_categoria = d.id_categoria
         WHERE c.slug != 'diario-oficial' AND d.status = 1 AND d.arquivo IS NOT NULL
         ORDER BY d.data_publicacao DESC LIMIT 6"
    )->fetchAll();
}

// --- Vereadores em exercício ---
$vereadoresAtivos = [];
if ($pdo) {
    $vereadoresAtivos = $pdo->query(
        "SELECT v.id_vereador, v.apelido, v.partido, s.nome_completo, s.foto_perfil,
                f.descricao AS funcao_descricao
         FROM vereadores v
         JOIN servidores s ON s.id_servidor = v.id_servidor
         LEFT JOIN mesa_diretora md ON md.id_vereador = v.id_vereador AND md.ativo = 1
         LEFT JOIN funcoes_legislativas f ON f.id_funcao = md.id_funcao
         WHERE v.ativo = 1
         ORDER BY s.nome_completo"
    )->fetchAll();
}

// --- História do município ---
$historiaMunicipio = null;
if ($pdo) {
    $historiaMunicipio = $pdo->query('SELECT * FROM historia_municipio ORDER BY id_historia LIMIT 1')->fetch();
}
?>

<main>

<!-- =====================================================================
     BANNER / HERO CAROUSEL
     ===================================================================== -->
<section class="hero-carousel" id="banner" aria-label="Destaques">

  <div class="slide active" style="background-image: url('assets/img/hero-carousel/slide1.jpg');">
    <div class="slide-overlay"></div>
    <div class="slide-content">
      <h2>Transparência e Democracia a Serviço do Cidadão</h2>
      <p>Acompanhe as atividades legislativas, sessões plenárias e decisões que moldam o futuro de Ananás.</p>
      <a href="sessoes.php" class="btn-slide"><i class="fas fa-play-circle"></i> Ver Sessões ao Vivo</a>
    </div>
  </div>

  <div class="slide" style="background-image: url('assets/img/hero-carousel/slide2.jpg');">
    <div class="slide-overlay"></div>
    <div class="slide-content">
      <h2>Diário Oficial Eletrônico</h2>
      <p>Acesse publicações oficiais, atos administrativos e normativos em tempo real, com validade jurídica.</p>
      <a href="diario-oficial.php" class="btn-slide"><i class="fas fa-newspaper"></i> Acessar Diário</a>
    </div>
  </div>

  <div class="slide" style="background-image: url('assets/img/hero-carousel/slide3.jpg');">
    <div class="slide-overlay"></div>
    <div class="slide-content">
      <h2>Participe das Audiências Públicas</h2>
      <p>Sua voz importa! Participe das audiências públicas e contribua com o processo legislativo do município.</p>
      <a href="audiencias.php" class="btn-slide"><i class="fas fa-users"></i> Saiba Como Participar</a>
    </div>
  </div>

  <div class="carousel-controles">
    <button class="carousel-prev" aria-label="Anterior"><i class="fas fa-chevron-left"></i></button>
    <div class="carousel-dots">
      <button class="dot active" aria-label="Slide 1"></button>
      <button class="dot" aria-label="Slide 2"></button>
      <button class="dot" aria-label="Slide 3"></button>
    </div>
    <button class="carousel-next" aria-label="Próximo"><i class="fas fa-chevron-right"></i></button>
  </div>

</section>
<!-- /Banner -->

<div class="divider-verde"></div>

<!-- =====================================================================
     e-SIC / PORTAL DA TRANSPARÊNCIA / RADAR DA TRANSPARÊNCIA
     ===================================================================== -->
<section class="portais-section" id="portais" aria-label="Portais de Transparência">
  <div class="portais-inner">

    <!-- e-SIC -->
    <a href="esic.php" class="portal-card esic">
      <div class="portal-icon"><i class="fas fa-envelope-open-text"></i></div>
      <div class="portal-card-texto">
        <h3>e-SIC</h3>
        <p>Serviço de Informação ao Cidadão. Solicite informações públicas.</p>
        <span class="portal-btn"><i class="fas fa-arrow-right"></i> Acessar</span>
      </div>
    </a>

    <!-- Portal da Transparência -->
    <a href="portal-transparencia.php" class="portal-card transparencia">
      <div class="portal-icon"><i class="fas fa-search-dollar"></i></div>
      <div class="portal-card-texto">
        <h3>Portal da Transparência</h3>
        <p>Receitas, despesas, contratos e dados orçamentários abertos.</p>
        <span class="portal-btn"><i class="fas fa-arrow-right"></i> Acessar</span>
      </div>
    </a>

    <!-- Radar da Transparência -->
    <a href="radar-transparencia.php" class="portal-card radar">
      <div class="portal-icon"><i class="fas fa-satellite-dish"></i></div>
      <div class="portal-card-texto">
        <h3>Radar da Transparência</h3>
        <p>Indicadores de conformidade e boas práticas na gestão pública.</p>
        <span class="portal-btn"><i class="fas fa-arrow-right"></i> Acessar</span>
      </div>
    </a>

  </div>
</section>
<!-- /Portais -->

<!-- =====================================================================
     MAIS ACESSADOS
     ===================================================================== -->
<section class="mais-acessados" id="mais-acessados">
  <div class="section-container">
    <h2 class="section-titulo">Mais Acessados</h2>
    <div class="mais-acessados-grid">

      <a href="leis.php" class="acesso-card">
        <div class="acesso-icone"><i class="fas fa-balance-scale"></i></div>
        <span>Leis Municipais</span>
      </a>
      <a href="sessoes.php" class="acesso-card">
        <div class="acesso-icone"><i class="fas fa-gavel"></i></div>
        <span>Sessões Plenárias</span>
      </a>
      <a href="vereadores.php" class="acesso-card">
        <div class="acesso-icone"><i class="fas fa-user-tie"></i></div>
        <span>Vereadores</span>
      </a>
      <a href="licitacoes.php" class="acesso-card">
        <div class="acesso-icone"><i class="fas fa-file-contract"></i></div>
        <span>Licitações</span>
      </a>
      <a href="contratos.php" class="acesso-card">
        <div class="acesso-icone"><i class="fas fa-handshake"></i></div>
        <span>Contratos</span>
      </a>
      <a href="atas.php" class="acesso-card">
        <div class="acesso-icone"><i class="fas fa-clipboard-list"></i></div>
        <span>Atas das Sessões</span>
      </a>
      <a href="servidores.php" class="acesso-card">
        <div class="acesso-icone"><i class="fas fa-id-badge"></i></div>
        <span>Servidores</span>
      </a>
      <a href="receitas-despesas.php" class="acesso-card">
        <div class="acesso-icone"><i class="fas fa-chart-line"></i></div>
        <span>Receitas e Despesas</span>
      </a>
      <a href="diario-oficial.php" class="acesso-card">
        <div class="acesso-icone"><i class="fas fa-newspaper"></i></div>
        <span>Diário Oficial</span>
      </a>
      <a href="projetos.php" class="acesso-card">
        <div class="acesso-icone"><i class="fas fa-file-alt"></i></div>
        <span>Projetos de Lei</span>
      </a>
      <a href="audiencias.php" class="acesso-card">
        <div class="acesso-icone"><i class="fas fa-microphone"></i></div>
        <span>Audiências Públicas</span>
      </a>
      <a href="contato.php" class="acesso-card">
        <div class="acesso-icone"><i class="fas fa-phone-alt"></i></div>
        <span>Fale Conosco</span>
      </a>

    </div>
  </div>
</section>
<!-- /Mais Acessados -->

<!-- =====================================================================
     NOTÍCIAS
     ===================================================================== -->
<section class="noticias-section" id="noticias">
  <div class="section-container">
    <h2 class="section-titulo">Notícias</h2>

    <div class="noticias-grid">

      <!-- Notícia Principal -->
      <?php if ($noticiaPrincipal): ?>
        <article class="noticia-principal">
          <div class="img-wrapper">
            <img src="<?= $noticiaPrincipal['imagem'] ? 'gerenciador/assets/uploads/noticias/' . htmlspecialchars($noticiaPrincipal['imagem']) : 'assets/img/noticias/noticia-destaque.jpg' ?>" alt="<?= htmlspecialchars($noticiaPrincipal['titulo']) ?>" loading="lazy">
          </div>
          <div class="conteudo">
            <?php if ($noticiaPrincipal['destaque']): ?><span class="tag-noticia">Destaque</span><?php endif; ?>
            <h3 class="titulo">
              <a href="noticia-detalhe.php?id=<?= $noticiaPrincipal['id_noticia'] ?>"><?= htmlspecialchars($noticiaPrincipal['titulo']) ?></a>
            </h3>
            <div class="noticia-meta">
              <span><i class="fas fa-calendar-alt"></i> <?= formatarDataExtenso($noticiaPrincipal['publicado_em']) ?></span>
              <?php if ($noticiaPrincipal['autor']): ?><span><i class="fas fa-user"></i> <?= htmlspecialchars($noticiaPrincipal['autor']) ?></span><?php endif; ?>
            </div>
            <p class="noticia-excerpt">
              <?= htmlspecialchars(resumirTexto($noticiaPrincipal['subtitulo'] ?: $noticiaPrincipal['conteudo'])) ?>
            </p>
            <a href="noticia-detalhe.php?id=<?= $noticiaPrincipal['id_noticia'] ?>" class="btn-leia-mais">Leia mais <i class="fas fa-arrow-right"></i></a>
          </div>
        </article>
      <?php else: ?>
        <article class="noticia-principal">
          <div class="conteudo">
            <h3 class="titulo">Nenhuma notícia publicada ainda</h3>
            <p class="noticia-excerpt">Assim que uma notícia for publicada no gerenciador, ela aparece aqui automaticamente.</p>
          </div>
        </article>
      <?php endif; ?>

      <!-- Lista de Notícias Recentes -->
      <aside class="noticias-lista">
        <h3><i class="fas fa-clock"></i> Notícias Recentes</h3>

        <?php if (empty($noticiasRecentes)): ?>
          <p style="font-size:.85rem; color:var(--texto-claro,#7a9186);">Nenhuma outra notícia publicada no momento.</p>
        <?php else: ?>
          <?php foreach ($noticiasRecentes as $n): ?>
            <article class="noticia-item">
              <div class="thumb">
                <img src="<?= $n['imagem'] ? 'gerenciador/assets/uploads/noticias/' . htmlspecialchars($n['imagem']) : 'assets/img/noticias/noticia1.jpg' ?>" alt="" loading="lazy">
              </div>
              <div class="info">
                <h4><a href="noticia-detalhe.php?id=<?= $n['id_noticia'] ?>"><?= htmlspecialchars($n['titulo']) ?></a></h4>
                <time datetime="<?= date('Y-m-d', strtotime($n['publicado_em'])) ?>"><?= formatarDataExtenso($n['publicado_em']) ?></time>
              </div>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>

        <div style="text-align:center; margin-top:1rem;">
          <a href="noticias.php" class="btn-leia-mais">Ver todas as notícias <i class="fas fa-arrow-right"></i></a>
        </div>
      </aside>

    </div>
  </div>
</section>
<!-- /Notícias -->

<!-- =====================================================================
     DIÁRIO OFICIAL + DOCUMENTOS RECENTES
     ===================================================================== -->
<section class="diario-docs-section" id="diario-oficial">
  <div class="section-container">
    <h2 class="section-titulo">Diário Oficial</h2>

    <div class="diario-docs-grid">

      <!-- Última Edição -->
      <div class="edicao-card">
        <?php if ($diarioAtual): ?>
          <div class="label-edicao">Última edição</div>
          <div class="num-edicao">
            <?= $diarioAtual['numero_documento'] ? 'Edição Nº ' . htmlspecialchars($diarioAtual['numero_documento']) : 'Diário Oficial' ?>
            <span>– <?= $diarioAtual['data_publicacao'] ? date('d/m/Y', strtotime($diarioAtual['data_publicacao'])) : '' ?></span>
          </div>
          <?php if ($diarioAtual['arquivo']): ?>
            <div class="diario-capa">
              <iframe
                src="gerenciador/assets/uploads/documentos/<?= htmlspecialchars($diarioAtual['arquivo']) ?>#toolbar=0&navpanes=0&scrollbar=0&view=FitH"
                class="diario-iframe"
                title="Diário Oficial"
                loading="lazy">
                <div class="diario-capa-mock">
                  <i class="fas fa-file-pdf"></i>
                  <p>Clique em "Ler" para visualizar o PDF</p>
                </div>
              </iframe>
            </div>
            <div class="edicao-actions">
              <a href="gerenciador/assets/uploads/documentos/<?= htmlspecialchars($diarioAtual['arquivo']) ?>" target="_blank" class="btn-ler"><i class="fas fa-book-open"></i> Ler</a>
              <a href="gerenciador/assets/uploads/documentos/<?= htmlspecialchars($diarioAtual['arquivo']) ?>" download class="btn-baixar"><i class="fas fa-download"></i> Baixar PDF</a>
            </div>
          <?php endif; ?>
        <?php else: ?>
          <div class="label-edicao">Diário Oficial</div>
          <p style="padding:1rem 0; color:var(--texto-claro,#7a9186); font-size:.9rem;">Nenhuma edição publicada ainda.</p>
        <?php endif; ?>
      </div>

      <!-- Edições Anteriores + Assinatura -->
      <div>
        <div class="edicoes-lista-card">
          <div class="edicoes-lista-header">Edições Anteriores</div>

          <?php if (empty($diariosAnteriores)): ?>
            <p style="padding:.75rem 1rem; color:var(--texto-claro,#7a9186); font-size:.85rem;">Nenhuma edição anterior cadastrada.</p>
          <?php else: ?>
            <?php foreach ($diariosAnteriores as $d): ?>
              <a href="gerenciador/assets/uploads/documentos/<?= htmlspecialchars($d['arquivo']) ?>" target="_blank" class="edicao-item" style="text-decoration:none; color:inherit;">
                <div class="edicao-info">
                  <strong><?= $d['numero_documento'] ? 'Edição Nº ' . htmlspecialchars($d['numero_documento']) : 'Diário Oficial' ?></strong>
                  <span>Publicado em: <?= $d['data_publicacao'] ? date('d/m/Y', strtotime($d['data_publicacao'])) : '—' ?></span>
                </div>
                <div class="edicao-stats"><i class="fas fa-file-pdf"></i></div>
              </a>
            <?php endforeach; ?>
          <?php endif; ?>

          <a href="diario-todos.php" class="ver-todas-btn">
            <i class="fas fa-folder-open"></i> Ver todas as edições
          </a>
        </div>
      </div>

      <!-- Documentos Recentes -->
      <div class="docs-recentes-card">
        <div class="docs-recentes-header">
          <i class="fas fa-file-alt"></i> Documentos Recentes
        </div>

        <?php if (empty($documentosRecentes)): ?>
          <p style="padding:.75rem 1rem; color:var(--texto-claro,#7a9186); font-size:.85rem;">Nenhum documento publicado ainda.</p>
        <?php else: ?>
          <?php foreach ($documentosRecentes as $doc): ?>
            <div class="doc-item">
              <div class="doc-info">
                <strong><?= htmlspecialchars($doc['categoria_descricao']) ?><?= $doc['numero_documento'] ? ' Nº ' . htmlspecialchars($doc['numero_documento']) : '' ?></strong>
                <span><?= $doc['data_publicacao'] ? date('d/m/Y', strtotime($doc['data_publicacao'])) : '—' ?></span>
              </div>
              <div class="doc-acoes">
                <a href="gerenciador/assets/uploads/documentos/<?= htmlspecialchars($doc['arquivo']) ?>" target="_blank" class="btn-ver" title="Visualizar"><i class="fas fa-eye"></i></a>
                <a href="gerenciador/assets/uploads/documentos/<?= htmlspecialchars($doc['arquivo']) ?>" download class="btn-dl" title="Download"><i class="fas fa-download"></i></a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>

      </div>

    </div>
  </div>
</section>
<!-- /Diário Oficial + Documentos Recentes -->

<!-- =====================================================================
     VEREADORES
     ===================================================================== -->
<section class="vereadores-section" id="vereadores">
  <div class="section-container">
    <h2 class="section-titulo">Vereadores</h2>

    <div class="vereadores-slider">
      <div class="vereadores-track" id="vereadores-track">

        <?php if (empty($vereadoresAtivos)): ?>
          <p style="padding:1rem; color:var(--texto-claro,#7a9186); font-size:.9rem;">Nenhum vereador cadastrado ainda.</p>
        <?php else: ?>
          <?php foreach ($vereadoresAtivos as $v): ?>
            <a href="vereador-detalhe.php?id=<?= $v['id_vereador'] ?>" class="vereador-card">
              <?php if (!empty($v['foto_perfil'])): ?>
                <div class="vereador-foto-placeholder" style="padding:0; overflow:hidden;">
                  <img src="gerenciador/assets/uploads/vereadores/<?= htmlspecialchars($v['foto_perfil']) ?>" alt="<?= htmlspecialchars($v['nome_completo']) ?>" style="width:100%; height:100%; object-fit:cover;">
                </div>
              <?php else: ?>
                <div class="vereador-foto-placeholder">
                  <i class="fas fa-user"></i>
                </div>
              <?php endif; ?>
              <span class="nome"><?= htmlspecialchars($v['apelido'] ?: $v['nome_completo']) ?></span>
              <span class="partido"><?= htmlspecialchars($v['partido'] ?: '') ?><?= $v['funcao_descricao'] ? ' — ' . htmlspecialchars($v['funcao_descricao']) : '' ?></span>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>

      </div>
    </div>

    <div class="vereadores-nav">
      <button class="slider-btn prev" aria-label="Anterior"><i class="fas fa-chevron-left"></i></button>
      <a href="vereadores.php" style="font-size:0.85rem; font-weight:600; color:var(--verde-medio);">
        Ver todos os vereadores
      </a>
      <button class="slider-btn next" aria-label="Próximo"><i class="fas fa-chevron-right"></i></button>
    </div>
  </div>
</section>
<!-- /Vereadores -->

<!-- =====================================================================
     HISTÓRIA DA CIDADE
     ===================================================================== -->
<section class="historia-section" id="historia">
  <div class="section-container">
    <div class="historia-grid">

      <div class="historia-imagem">
        <?php if ($historiaMunicipio && !empty($historiaMunicipio['imagem_cidade'])): ?>
          <img src="gerenciador/assets/uploads/institucional/<?= htmlspecialchars($historiaMunicipio['imagem_cidade']) ?>" alt="Ananás — Tocantins" style="width:100%; height:100%; object-fit:cover; border-radius:inherit;">
        <?php else: ?>
          <div class="historia-imagem-placeholder">
            <i class="fas fa-city"></i>
            <p>Ananás — Tocantins</p>
          </div>
        <?php endif; ?>
      </div>

      <div class="historia-conteudo">
        <div class="subtitulo">Conheça nossa cidade</div>
        <h2>História de Ananás</h2>
        <?php if ($historiaMunicipio && !empty($historiaMunicipio['conteudo'])): ?>
          <?php
            $paragrafos = preg_split('/\r\n|\r|\n/', trim($historiaMunicipio['conteudo']));
            $paragrafos = array_filter($paragrafos, fn($p) => trim($p) !== '');
            $paragrafos = array_slice($paragrafos, 0, 3);
          ?>
          <?php foreach ($paragrafos as $p): ?>
            <p><?= htmlspecialchars(trim($p)) ?></p>
          <?php endforeach; ?>
        <?php else: ?>
          <p>A história do município ainda não foi cadastrada. Assim que for preenchida em "A Câmara" no gerenciador, este texto aparece automaticamente aqui.</p>
        <?php endif; ?>
        <a href="historia.php" class="btn-saiba-mais">
          <i class="fas fa-book-open"></i> Saiba Mais
        </a>
      </div>

    </div>
  </div>
</section>
<!-- /História -->

</main>

<?php include 'includes/footer.php'; ?>