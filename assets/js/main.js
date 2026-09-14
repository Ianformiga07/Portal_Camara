/**
 * CÂMARA MUNICIPAL — main.js
 * Funcionalidades: menu mobile, carousel, slider vereadores, scroll-top, acessibilidade
 */

document.addEventListener('DOMContentLoaded', function () {

  /* =============================================
     MENU MOBILE (Toggle)
     ============================================= */
  const navToggle = document.getElementById('navToggle');
  const mainNav   = document.getElementById('mainNav');

  if (navToggle && mainNav) {
    navToggle.addEventListener('click', function () {
      mainNav.classList.toggle('open');
      const icon = navToggle.querySelector('i');
      icon.classList.toggle('fa-bars');
      icon.classList.toggle('fa-times');
    });

    // Dropdowns no mobile (clique)
    document.querySelectorAll('.navmenu .dropdown > a').forEach(function (link) {
      link.addEventListener('click', function (e) {
        if (window.innerWidth <= 768) {
          e.preventDefault();
          const parent = this.closest('.dropdown');
          parent.classList.toggle('open');
        }
      });
    });

    // Fechar menu ao clicar fora
    document.addEventListener('click', function (e) {
      if (!navToggle.contains(e.target) && !mainNav.contains(e.target)) {
        mainNav.classList.remove('open');
        const icon = navToggle.querySelector('i');
        icon.classList.add('fa-bars');
        icon.classList.remove('fa-times');
      }
    });
  }

  /* =============================================
     HERO CAROUSEL
     ============================================= */
  const slides     = document.querySelectorAll('.hero-carousel .slide');
  const dots       = document.querySelectorAll('.carousel-dots .dot');
  const btnPrev    = document.querySelector('.carousel-prev');
  const btnNext    = document.querySelector('.carousel-next');
  let   current    = 0;
  let   autoplayTimer;

  function goToSlide(index) {
    slides[current].classList.remove('active');
    dots[current]?.classList.remove('active');
    current = (index + slides.length) % slides.length;
    slides[current].classList.add('active');
    dots[current]?.classList.add('active');
  }

  function nextSlide() { goToSlide(current + 1); }
  function prevSlide() { goToSlide(current - 1); }

  function startAutoplay() {
    autoplayTimer = setInterval(nextSlide, 5000);
  }

  function resetAutoplay() {
    clearInterval(autoplayTimer);
    startAutoplay();
  }

  if (slides.length > 0) {
    btnNext?.addEventListener('click', function () { nextSlide(); resetAutoplay(); });
    btnPrev?.addEventListener('click', function () { prevSlide(); resetAutoplay(); });

    dots.forEach(function (dot, i) {
      dot.addEventListener('click', function () { goToSlide(i); resetAutoplay(); });
    });

    startAutoplay();
  }

  /* =============================================
     SLIDER DE VEREADORES
     ============================================= */
  const track     = document.querySelector('.vereadores-track');
  const btnLeft   = document.querySelector('.slider-btn.prev');
  const btnRight  = document.querySelector('.slider-btn.next');

  if (track && btnLeft && btnRight) {
    const cardWidth = 160 + 16; // largura + gap
    let   offset    = 0;
    const maxOffset = () => track.scrollWidth - track.parentElement.offsetWidth;

    btnRight.addEventListener('click', function () {
      offset = Math.min(offset + cardWidth * 3, maxOffset());
      track.style.transform = 'translateX(-' + offset + 'px)';
    });

    btnLeft.addEventListener('click', function () {
      offset = Math.max(offset - cardWidth * 3, 0);
      track.style.transform = 'translateX(-' + offset + 'px)';
    });

    // Auto-scroll suave
    let autoScroll = setInterval(function () {
      if (offset >= maxOffset()) {
        offset = 0;
      } else {
        offset = Math.min(offset + cardWidth, maxOffset());
      }
      track.style.transform = 'translateX(-' + offset + 'px)';
    }, 3000);

    track.parentElement.addEventListener('mouseenter', () => clearInterval(autoScroll));
    track.parentElement.addEventListener('mouseleave', function () {
      autoScroll = setInterval(function () {
        if (offset >= maxOffset()) offset = 0;
        else offset = Math.min(offset + cardWidth, maxOffset());
        track.style.transform = 'translateX(-' + offset + 'px)';
      }, 3000);
    });
  }

  /* =============================================
     SCROLL TO TOP
     ============================================= */
  const scrollTopBtn = document.getElementById('scrollTop');

  if (scrollTopBtn) {
    window.addEventListener('scroll', function () {
      if (window.scrollY > 400) {
        scrollTopBtn.classList.add('visible');
      } else {
        scrollTopBtn.classList.remove('visible');
      }
    });

    scrollTopBtn.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  /* =============================================
     TABS — NOTÍCIAS
     ============================================= */
  const tabs     = document.querySelectorAll('.noticias-tab');
  const tabPanes = document.querySelectorAll('.tab-pane');

  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      tabs.forEach(t => t.classList.remove('active'));
      tabPanes.forEach(p => p.classList.remove('active'));
      this.classList.add('active');
      const target = document.getElementById(this.dataset.target);
      if (target) target.classList.add('active');
    });
  });

  /* =============================================
     ANIMAÇÃO DE ENTRADA (IntersectionObserver)
     ============================================= */
  const observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });

  document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));

});

/* =============================================
   ACESSIBILIDADE — Contraste e Fonte
   ============================================= */
function toggleContraste() {
  document.body.classList.toggle('alto-contraste');
  localStorage.setItem('contraste', document.body.classList.contains('alto-contraste') ? '1' : '0');
}

function alterarFonte(delta) {
  const html    = document.documentElement;
  const current = parseFloat(getComputedStyle(html).fontSize);
  const nova    = Math.min(Math.max(current + delta, 12), 22);
  html.style.fontSize = nova + 'px';
  localStorage.setItem('fontSize', nova);
}

// Restaurar preferências salvas
(function () {
  if (localStorage.getItem('contraste') === '1') {
    document.body.classList.add('alto-contraste');
  }
  const savedSize = localStorage.getItem('fontSize');
  if (savedSize) {
    document.documentElement.style.fontSize = savedSize + 'px';
  }
})();