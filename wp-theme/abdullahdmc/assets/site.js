'use strict';
const menuButton = document.querySelector('#menu-toggle');
const nav = document.querySelector('#main-nav');
const searchButton = document.querySelector('#search-toggle');
const panel = document.querySelector('#search-panel');
const input = document.querySelector('#site-search');
const results = document.querySelector('#search-results');
const status = document.querySelector('#search-status');
let searchIndex;
let searchRequest;
let searchVersion = 0;
function closeMenu() { menuButton.setAttribute('aria-expanded', 'false'); nav.classList.remove('open'); }
menuButton.addEventListener('click', () => {
  const open = menuButton.getAttribute('aria-expanded') !== 'true';
  menuButton.setAttribute('aria-expanded', String(open));
  nav.classList.toggle('open', open);
  if (open) closeSearch(false);
});
nav.addEventListener('click', e => { if (e.target.closest('a')) closeMenu(); });
function closeSearch(restoreFocus = true) {
  if (panel.hidden) return;
  panel.hidden = true;
  searchButton.setAttribute('aria-expanded', 'false');
  if (restoreFocus) searchButton.focus();
}
searchButton.addEventListener('click', () => {
  if (!panel.hidden) return closeSearch();
  closeMenu();
  panel.hidden = false;
  searchButton.setAttribute('aria-expanded', 'true');
  input.focus();
});
document.querySelector('.close-search').addEventListener('click', () => closeSearch());
document.addEventListener('keydown', e => {
  if (e.key !== 'Escape') return;
  if (!panel.hidden) closeSearch();
  else if (nav.classList.contains('open')) { closeMenu(); menuButton.focus(); }
});
document.addEventListener('click', e => {
  if (!e.target.closest('.header') && nav.classList.contains('open')) closeMenu();
});
// Matching ignores Bangla spelling variants: joiners, hasanta and the ya-phala in অ্যা spellings.
const normalizeSearch = s => String(s).normalize('NFC').toLocaleLowerCase().replace(/[\u200c\u200d]/g, '').replace(/\u09cd\u09af/g, '').replace(/\u0985\u09be/g, '\u0986').replace(/\u09cd/g, '');
async function getIndex() {
  if (searchIndex) return searchIndex;
  if (!searchRequest) searchRequest = fetch(window.AMK.searchIndex).then(response => {
    if (!response.ok) throw new Error('Search unavailable');
    return response.json();
  }).then(data => searchIndex = data).catch(error => { searchRequest = null; throw error; });
  return searchRequest;
}
input.addEventListener('input', async () => {
  const version = ++searchVersion;
  const query = input.value.trim().toLocaleLowerCase(), needle = normalizeSearch(query);
  results.replaceChildren();
  if (!query) { status.textContent = 'Search the latest writing and AI research roundups.'; return; }
  status.textContent = 'Searching…';
  try {
    const items = await getIndex();
    if (version !== searchVersion) return;
    const matches = items.filter(p => normalizeSearch(`${p.title} ${p.excerpt} ${p.keywords || ''}`).includes(needle));
    status.textContent = matches.length ? `${matches.length} result${matches.length === 1 ? '' : 's'} in the latest writing.` : 'No matching articles. Try a different word, or explore the full archive.';
    for (const p of matches.slice(0, 8)) {
      const li = document.createElement('li');
      const link = document.createElement('a'); link.href = p.url; link.textContent = p.title;
      const time = document.createElement('time'); time.dateTime = p.date;
      time.textContent = new Date(p.date + 'T12:00:00').toLocaleDateString('en-GB', {day:'numeric',month:'short',year:'numeric'});
      link.append(time); li.append(link); results.append(li);
    }
    const li = document.createElement('li'), archive = document.createElement('a');
    archive.href = window.AMK.search + encodeURIComponent(query);
    archive.textContent = 'See all results →'; li.append(archive); results.append(li);
  } catch {
    if (version !== searchVersion) return;
    status.textContent = 'Quick search is unavailable. Use the full search instead.';
    const a = document.createElement('a'); a.href = window.AMK.search + encodeURIComponent(query); a.textContent = 'Search the site →';
    const li = document.createElement('li'); li.append(a); results.append(li);
  }
});
// Reveals only run once, as sections enter view; no hidden content or scroll interception.
const motion = matchMedia('(prefers-reduced-motion: reduce)');
if ('IntersectionObserver' in window) {
  const observer = new IntersectionObserver(entries => {
    for (const entry of entries) if (entry.isIntersecting) {
      if (!motion.matches) entry.target.classList.add('reveal-in');
      observer.unobserve(entry.target);
    }
  }, {threshold: 0.12});
  document.querySelectorAll('.profile-top, .section-heading, .series-grid, .channels, .newsletter, .writing-grid, .roundup, .topics, .publications, .profile-bottom, .profile-links').forEach(el => observer.observe(el));
  motion.addEventListener('change', () => { if (motion.matches) document.querySelectorAll('.reveal-in').forEach(el => el.classList.remove('reveal-in')); });
}
const themeButton = document.querySelector('#theme-toggle');
const darkQuery = matchMedia('(prefers-color-scheme: dark)');
function currentTheme() { return document.documentElement.dataset.theme || (darkQuery.matches ? 'dark' : 'light'); }
function labelTheme() { themeButton?.setAttribute('aria-label', currentTheme() === 'dark' ? 'Use light theme' : 'Use dark theme'); }
themeButton?.addEventListener('click', () => {
  const next = currentTheme() === 'dark' ? 'light' : 'dark';
  document.documentElement.dataset.theme = next;
  document.querySelectorAll('meta[name="theme-color"]').forEach(m => { m.removeAttribute('media'); m.content = next === 'dark' ? '#0e1d19' : '#102d27'; });
  try { localStorage.setItem('theme', next); } catch (e) {}
  labelTheme();
});
darkQuery.addEventListener('change', labelTheme);
labelTheme();
// Restore a visible navigation state when crossing the mobile breakpoint.
matchMedia('(min-width: 801px)').addEventListener('change', () => closeMenu());


// Run ambient paint only while it is visible and the tab is active.
const fogSection = document.querySelector('.series-section');
let fogVisible = false;
function syncFog() {
  fogSection?.classList.toggle('fog-active', fogVisible && !document.hidden && !motion.matches);
}
if (fogSection && 'IntersectionObserver' in window) {
  new IntersectionObserver(entries => {
    fogVisible = entries[0].isIntersecting;
    syncFog();
  }, {threshold: 0}).observe(fogSection);
  document.addEventListener('visibilitychange', syncFog);
  motion.addEventListener('change', syncFog);
}

// Reveal the existing illustration through alternating brush passes, once.
const oxfordSketch = document.querySelector('.oxford-sketch');
if (oxfordSketch && !motion.matches && 'IntersectionObserver' in window) {
  oxfordSketch.classList.add('sketch-ready');
  const sketchObserver = new IntersectionObserver(entries => {
    if (!entries.some(entry => entry.isIntersecting)) return;
    oxfordSketch.classList.add('sketch-drawing');
    sketchObserver.disconnect();
  }, {threshold: .1});
  sketchObserver.observe(oxfordSketch);
  motion.addEventListener('change', () => {
    if (motion.matches) {
      oxfordSketch.classList.remove('sketch-ready', 'sketch-drawing');
      sketchObserver.disconnect();
    }
  });
}
