document.addEventListener('DOMContentLoaded', function () {
  var sidebar = document.querySelector('.sidebar');
  var toggle  = document.querySelector('.topbar__toggle');

  if (toggle && sidebar) {
    toggle.addEventListener('click', function () {
      sidebar.classList.toggle('aberta');
    });
  }

  var btnSair = document.querySelector('.topbar__sair');
  if (btnSair) {
    btnSair.addEventListener('click', function (e) {
      e.preventDefault();
      if (confirm('Deseja encerrar a sessão atual?')) {
        window.location.href = 'logout.php';
      }
    });
  }

  // marca o item do menu correspondente à página atual como ativo
  // (também reconhece telas "filhas" e diferencia por categoria, ex:
  // documentos.php?categoria=diario-oficial x documentos.php sem filtro)
  var caminhoAtual   = window.location.pathname.split('/').pop();
  var categoriaAtual = new URLSearchParams(window.location.search).get('categoria') || '';
  var prefixoAtual    = caminhoAtual.replace('.php', '').replace(/-(form|excluir|editar)$/, '').replace(/s$/, '');

  document.querySelectorAll('.sidebar nav a').forEach(function (link) {
    var hrefBruto = link.getAttribute('href') || '';
    if (hrefBruto === '' || hrefBruto === '#') return;

    var [hrefBase, hrefQuery] = hrefBruto.split('?');
    var categoriaLink = new URLSearchParams(hrefQuery || '').get('categoria') || '';
    var prefixoLink = hrefBase.replace('.php', '').replace(/s$/, '');

    var mesmaPagina = hrefBase === caminhoAtual || prefixoLink === prefixoAtual;
    var mesmaCategoria = categoriaLink === categoriaAtual;

    if (mesmaPagina && mesmaCategoria) {
      link.classList.add('ativo');
    }
  });
});
