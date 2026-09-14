<?php
require_once __DIR__ . '/../config/database.php';

$legislaturaAtual = null;
if ($pdo) {
    $legislaturaAtual = $pdo->query('SELECT descricao FROM mandatos_eletivos ORDER BY id_mandato DESC LIMIT 1')->fetchColumn();
}
?>
<!-- ===================== FOOTER ===================== -->
<footer class="footer">
  <div class="footer-top">

    <!-- Coluna 1: Identidade -->
    <div class="footer-logo">
      <div class="logo-nome">Câmara Municipal de Ananás</div>
      <div class="logo-sub">Estado do Tocantins — Poder Legislativo</div>
      <div class="footer-contato">
        <p><i class="fas fa-map-marker-alt"></i> Praça da Câmara, S/N – Centro, Ananás/TO</p>
        <p><i class="fas fa-phone"></i> (63) 3471-0000</p>
        <p><i class="fas fa-envelope"></i> contato@camaraananas.to.gov.br</p>
        <p><i class="fas fa-clock"></i> Seg a Sex: 08h às 12h — 14h às 18h</p>
      </div>
      <div class="footer-social">
        <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
        <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
        <a href="#" title="YouTube"><i class="fab fa-youtube"></i></a>
        <a href="#" title="Twitter/X"><i class="fab fa-x-twitter"></i></a>
        <a href="#" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
      </div>
    </div>

    <!-- Coluna 2: A Câmara -->
    <div class="footer-col">
      <h4>A Câmara</h4>
      <ul>
        <li><a href="historia.php">História</a></li>
        <li><a href="mesa-diretora.php">Mesa Diretora</a></li>
        <li><a href="vereadores.php">Vereadores</a></li>
        <li><a href="comissoes.php">Comissões</a></li>
        <li><a href="regimento.php">Regimento Interno</a></li>
        <li><a href="lei-organica.php">Lei Orgânica</a></li>
      </ul>
    </div>

    <!-- Coluna 3: Legislação -->
    <div class="footer-col">
      <h4>Legislação</h4>
      <ul>
        <li><a href="leis.php">Leis Municipais</a></li>
        <li><a href="decretos.php">Decretos</a></li>
        <li><a href="resolucoes.php">Resoluções</a></li>
        <li><a href="portarias.php">Portarias</a></li>
        <li><a href="projetos.php">Projetos de Lei</a></li>
        <li><a href="diario-oficial.php">Diário Oficial</a></li>
      </ul>
    </div>

    <!-- Coluna 4: Transparência -->
    <div class="footer-col footer-atendimento">
      <h4>Transparência</h4>
      <ul>
        <li><a href="portal-transparencia.php">Portal da Transparência</a></li>
        <li><a href="esic.php">e-SIC</a></li>
        <li><a href="licitacoes.php">Licitações</a></li>
        <li><a href="contratos.php">Contratos</a></li>
        <li><a href="servidores.php">Servidores</a></li>
      </ul>
      <br>
      <p><strong>CNPJ:</strong> 00.767.228/0001-01</p>
      <p><strong>Legislatura:</strong> <?= htmlspecialchars($legislaturaAtual ?: '2025–2028') ?></p>
    </div>

  </div>

  <div class="footer-bottom">
    <p>
      &copy; <?= date('Y') ?> Câmara Municipal de Ananás — Todos os direitos reservados.
      Desenvolvido com <i class="fas fa-heart" style="color:#c9a227;"></i> para a democracia.
      &nbsp;|&nbsp; <a href="politica-privacidade.php">Política de Privacidade</a>
      &nbsp;|&nbsp; <a href="acessibilidade.php">Acessibilidade</a>
    </p>
  </div>
</footer>

<!-- Scroll to Top -->
<button class="scroll-top" id="scrollTop" title="Voltar ao topo">
  <i class="fas fa-chevron-up"></i>
</button>

<!-- JS Principal -->
<script src="assets/js/main.js"></script>
</body>
</html>