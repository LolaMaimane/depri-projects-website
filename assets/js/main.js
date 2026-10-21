// Header background on scroll
const header = document.querySelector('.site-header');
const onScroll = () => header && header.classList.toggle('scrolled', window.scrollY > 40);
window.addEventListener('scroll', onScroll, { passive: true });
onScroll();

// Mobile menu
const burger = document.querySelector('.burger');
const menu = document.querySelector('.menu');
if (burger && menu) {
  burger.addEventListener('click', () => {
    const open = menu.classList.toggle('open');
    burger.setAttribute('aria-expanded', open);
    burger.textContent = open ? '✕' : '☰';
  });
  menu.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
    menu.classList.remove('open');
    burger.setAttribute('aria-expanded', false);
    burger.textContent = '☰';
  }));
}

// Enquiry route preselect
const routeSelect = document.getElementById('route');
const routeButtons = document.querySelectorAll('.routes-list button');
function setRoute(value) {
  if (!routeSelect) return;
  routeSelect.value = value;
  routeButtons.forEach(b => b.classList.toggle('on', b.dataset.route === value));
}
document.querySelectorAll('[data-route]').forEach(el => {
  el.addEventListener('click', () => {
    setRoute(el.dataset.route);
    if (el.tagName === 'BUTTON') document.getElementById('name')?.focus();
  });
});
routeSelect?.addEventListener('change', () => setRoute(routeSelect.value));

// Reveal on scroll
const io = 'IntersectionObserver' in window ? new IntersectionObserver(entries => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
}, { threshold: 0.12 }) : null;
document.querySelectorAll('.reveal').forEach(el => io ? io.observe(el) : el.classList.add('in'));

const yr = document.getElementById('yr');
if (yr) yr.textContent = new Date().getFullYear();
