// Shared landing interactivity (theme toggle, icons, nav, mobile menu, reveal, demo tabs).
// Loaded on all 5 pages. Pages without .demo-tab / .reveal elements no-op safely.

const root = document.documentElement;
const themeBtn = document.getElementById('theme-btn');
if (themeBtn) {
  const syncPressed = () =>
    themeBtn.setAttribute('aria-pressed', String(root.getAttribute('data-theme') === 'light'));
  syncPressed();
  themeBtn.addEventListener('click', () => {
    const isLight = root.getAttribute('data-theme') === 'light';
    if (isLight) root.removeAttribute('data-theme');
    else root.setAttribute('data-theme', 'light');
    try {
      localStorage.setItem('vitalfy-theme', isLight ? 'dark' : 'light');
    } catch (e) {}
    syncPressed();
  });
}

if (typeof lucide !== 'undefined' && lucide.createIcons) {
  lucide.createIcons();
}

const navInner = document.getElementById('nav-inner');
if (navInner) {
  const onScroll = () => {
    if (window.scrollY > 16) {
      navInner.classList.add('glass', 'shadow-lg', 'shadow-black/30');
    } else {
      navInner.classList.remove('glass', 'shadow-lg', 'shadow-black/30');
    }
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
}

const menuBtn = document.getElementById('menu-btn');
const mobileMenu = document.getElementById('mobile-menu');
if (menuBtn && mobileMenu) {
  menuBtn.addEventListener('click', () => {
    const open = mobileMenu.classList.toggle('hidden') === false;
    menuBtn.setAttribute('aria-expanded', String(open));
  });
  mobileMenu.querySelectorAll('a').forEach((a) =>
    a.addEventListener('click', () => {
      mobileMenu.classList.add('hidden');
      menuBtn.setAttribute('aria-expanded', 'false');
    })
  );
}

const io = new IntersectionObserver(
  (entries) => {
    entries.forEach((e) => {
      if (e.isIntersecting) {
        e.target.classList.add('in');
        io.unobserve(e.target);
      }
    });
  },
  { threshold: 0.12, rootMargin: '0px 0px -8% 0px' }
);
document.querySelectorAll('.reveal').forEach((el) => io.observe(el));

const tabs = document.querySelectorAll('.demo-tab');
const panels = document.querySelectorAll('.demo-panel');
tabs.forEach((tab) => {
  tab.addEventListener('click', () => {
    tabs.forEach((t) => {
      t.classList.remove('border-accent', 'text-ink');
      t.classList.add('border-transparent', 'text-dim');
      t.setAttribute('aria-selected', 'false');
    });
    tab.classList.add('border-accent', 'text-ink');
    tab.classList.remove('border-transparent', 'text-dim');
    tab.setAttribute('aria-selected', 'true');
    const target = tab.dataset.tab;
    panels.forEach((p) => p.classList.toggle('active', p.dataset.panel === target));
  });
});
