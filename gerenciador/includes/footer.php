    </main>

    <footer class="rodape-sistema">
      <span>© <?= date('Y') ?> Câmara Municipal de Ananás — Sistema de Gerenciamento</span>
      <span>Logado como <?= htmlspecialchars($usuario['nome_completo'] ?? '') ?></span>
    </footer>
  </div>
</div>
<script src="assets/js/admin.js?v=<?= filemtime(__DIR__ . '/../assets/js/admin.js') ?>"></script>
</body>
</html>
