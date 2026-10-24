// Header background on scroll + back-to-top button
const header = document.querySelector('.site-header');
const toTop = document.querySelector('.to-top');
const onScroll = () => {
  header?.classList.toggle('scrolled', window.scrollY > 40);
  toTop?.classList.toggle('show', window.scrollY > 700);
};
window.addEventListener('scroll', onScroll, { passive: true });
onScroll();
toTop?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

// Mobile menu
const burger = document.querySelector('.burger');
const menu = document.querySelector('.menu');
function closeMenu() {
  menu.classList.remove('open');
  document.body.classList.remove('menu-open');
  burger.setAttribute('aria-expanded', false);
  burger.textContent = '☰';
}
if (burger && menu) {
  burger.addEventListener('click', () => {
    const open = menu.classList.toggle('open');
    document.body.classList.toggle('menu-open', open);
    burger.setAttribute('aria-expanded', open);
    burger.textContent = open ? '✕' : '☰';
  });
  menu.querySelectorAll('a').forEach(a => a.addEventListener('click', closeMenu));
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && menu.classList.contains('open')) closeMenu(); });
}

// Enquiry route preselect (from buttons on the contact page or ?route= in the URL)
const routeSelect = document.getElementById('route');
const routeButtons = document.querySelectorAll('.routes-list button');
function setRoute(value) {
  if (!routeSelect || !value) return;
  if (![...routeSelect.options].some(o => o.value === value)) return;
  routeSelect.value = value;
  routeButtons.forEach(b => b.classList.toggle('on', b.dataset.route === value));
}
routeButtons.forEach(el => {
  el.addEventListener('click', () => {
    setRoute(el.dataset.route);
    document.getElementById('name')?.focus();
  });
});
routeSelect?.addEventListener('change', () => setRoute(routeSelect.value));
setRoute(new URLSearchParams(location.search).get('route'));

// Enquiry form: send-enquiry.php emails info@ and sends the visitor back with ?sent=1
const formParams = new URLSearchParams(location.search);
if (formParams.has('sent')) document.querySelector('.form-ok')?.classList.add('show');
// Tidy the address bar so a reload doesn't repeat the message
if (formParams.has('sent') || formParams.has('error')) history.replaceState(null, '', location.pathname + '#enquiry');
document.getElementById('enquiry-form')?.addEventListener('submit', e => {
  e.target.querySelector('button[type="submit"]').textContent = 'Sending…';
});

// Product filter chips
const chips = document.querySelectorAll('.chips button');
chips.forEach(chip => chip.addEventListener('click', () => {
  const f = chip.dataset.filter;
  chips.forEach(c => c.setAttribute('aria-pressed', c === chip));
  document.querySelectorAll('.product[data-cat]').forEach(p => {
    p.classList.toggle('hide', f !== 'all' && !p.dataset.cat.split(' ').includes(f));
  });
}));

// Animated counters
function runCounter(el) {
  const end = +el.dataset.count;
  const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduce) { el.textContent = end + (el.dataset.suffix || ''); return; }
  const start = +(el.dataset.from || 0);
  const t0 = performance.now();
  const step = t => {
    const p = Math.min((t - t0) / 1400, 1);
    el.textContent = Math.round(start + (end - start) * (1 - Math.pow(1 - p, 3))) + (el.dataset.suffix || '');
    if (p < 1) requestAnimationFrame(step);
  };
  requestAnimationFrame(step);
}

// Reveal on scroll
const io = 'IntersectionObserver' in window ? new IntersectionObserver(entries => {
  entries.forEach(e => {
    if (!e.isIntersecting) return;
    e.target.classList.add('in');
    e.target.querySelectorAll('[data-count]').forEach(runCounter);
    io.unobserve(e.target);
  });
}, { threshold: 0.12 }) : null;
document.querySelectorAll('.reveal').forEach(el => {
  if (io) io.observe(el);
  else { el.classList.add('in'); el.querySelectorAll('[data-count]').forEach(runCounter); }
});

const yr = document.getElementById('yr');
if (yr) yr.textContent = new Date().getFullYear();
