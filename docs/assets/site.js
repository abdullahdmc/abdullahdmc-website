// Shared header, footer, themes and the generated post cover image.

const THEMES = {
  science:    { bn: "বিজ্ঞান ও বিবর্তন", en: "Science & Evolution", c1: "#0f5c5a", c2: "#178f86", motif: "helix" },
  mind:       { bn: "মন ও মস্তিষ্ক", en: "Mind & Brain", c1: "#3f3685", c2: "#6a5fc1", motif: "neuron" },
  health:     { bn: "চিকিৎসা ও গবেষণা", en: "Medicine & Research", c1: "#9b3434", c2: "#d0644a", motif: "pulse" },
  faith:      { bn: "বিশ্বাস ও দর্শন", en: "Faith & Philosophy", c1: "#1d3f66", c2: "#3a6ea5", motif: "star" },
  literature: { bn: "সাহিত্য ও রিভিউ", en: "Poems, Stories & Reviews", c1: "#7a4a17", c2: "#c08a3e", motif: "book" },
  essays:     { bn: "ভাবনা ও প্রবন্ধ", en: "Essays & Reflections", c1: "#34433a", c2: "#5f7a67", motif: "ripple" },
};

const MOTIFS = {
  helix: `<path d="M40 0 C 160 50, 160 50, 40 100 S -80 150, 40 200" /><path d="M160 0 C 40 50, 40 50, 160 100 S 280 150, 160 200" />
    ${[12, 30, 50, 70, 88, 112, 130, 150, 170, 188].map(y => `<line x1="${70 - 25 * Math.cos(y / 16)}" y1="${y}" x2="${130 + 25 * Math.cos(y / 16)}" y2="${y}" />`).join("")}`,
  neuron: `${[[100,100],[40,40],[160,30],[170,120],[120,180],[30,150],[70,95],[140,70]].map(([x,y]) => `<circle cx="${x}" cy="${y}" r="${x === 100 ? 14 : 7}" />`).join("")}
    <path d="M100 100 L40 40 M100 100 L160 30 M100 100 L170 120 M100 100 L120 180 M100 100 L30 150 M70 95 L40 40 M140 70 L160 30 M140 70 L170 120 M70 95 L30 150" />`,
  pulse: `<path d="M0 110 H55 L70 80 L85 150 L105 40 L122 130 L135 110 H200" stroke-width="5" /><path d="M150 20 v40 M130 40 h40" stroke-width="8" />
    <circle cx="150" cy="40" r="32" />`,
  star: `<rect x="40" y="40" width="120" height="120" /><rect x="40" y="40" width="120" height="120" transform="rotate(45 100 100)" />
    <circle cx="100" cy="100" r="42" /><circle cx="100" cy="100" r="88" /><rect x="70" y="70" width="60" height="60" transform="rotate(22.5 100 100)" />`,
  book: `<path d="M100 60 C 70 40, 30 40, 10 50 V 160 C 30 150, 70 150, 100 170 C 130 150, 170 150, 190 160 V 50 C 170 40, 130 40, 100 60 Z" />
    <path d="M100 60 V170" /><path d="M30 75 C 50 70, 70 72, 85 80 M30 100 C 50 95, 70 97, 85 105 M30 125 C 50 120, 70 122, 85 130 M115 80 C 130 72, 150 70, 170 75 M115 105 C 130 97, 150 95, 170 100" />`,
  ripple: `${[18, 38, 58, 78, 98].map(r => `<circle cx="100" cy="100" r="${r}" />`).join("")}`,
};

function motifSVG(name, extra = "") {
  return `<svg class="motif" viewBox="-10 -10 220 220" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" ${extra} aria-hidden="true">${MOTIFS[name]}</svg>`;
}

function hash(n) {
  let h = n * 2654435761 % 4294967296;
  return () => (h = (h * 1103515245 + 12345) % 2147483648) / 2147483648;
}

// The generated "featured image": theme colours + motif + title. Each post gets its own placement.
function coverHTML(post, cls = "") {
  const t = THEMES[post.theme];
  const r = hash(post.id);
  const ang = 110 + Math.round(r() * 70);
  const x = Math.round(r() * 40) + 38, y = Math.round(r() * 60) - 30, rot = Math.round(r() * 60) - 30;
  const style = `--c1:${t.c1};--c2:${t.c2};--ang:${ang}deg`;
  const motifPos = `style="left:${x}%;top:${y}%;transform:rotate(${rot}deg)"`;
  return `<div class="cover ${cls}" style="${style}" role="img" aria-label="${esc(post.title)}">
    ${motifSVG(t.motif, motifPos)}
    <span class="c-chip">${t.bn}</span>
    <span class="c-sig">abdullahdmc.com</span>
    <div class="c-title">${esc(post.title)}</div>
  </div>`;
}

function postURL(post) { return `post.html?id=${post.id}`; }

function cardHTML(post) {
  const t = THEMES[post.theme];
  return `<a class="card" href="${postURL(post)}" style="--c1:${t.c1}">
    ${coverHTML(post)}
    <div class="card-body">
      <div class="card-meta"><span class="dot"></span>${t.bn} · ${bnDate(post.date)}</div>
      <h3>${esc(post.title)}</h3>
      <p>${esc(post.excerpt)}</p>
    </div>
  </a>`;
}

const BN_DIGITS = "০১২৩৪৫৬৭৮৯";
const BN_MONTHS = ["জানুয়ারি", "ফেব্রুয়ারি", "মার্চ", "এপ্রিল", "মে", "জুন", "জুলাই", "আগস্ট", "সেপ্টেম্বর", "অক্টোবর", "নভেম্বর", "ডিসেম্বর"];
function bn(n) { return String(n).replace(/\d/g, d => BN_DIGITS[d]); }
function bnDate(iso) {
  const [y, m, d] = iso.split("-").map(Number);
  return `${bn(d)} ${BN_MONTHS[m - 1]} ${bn(y)}`;
}
function esc(s) { return String(s).replace(/[&<>"]/g, c => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" }[c])); }

function renderChrome(active) {
  const links = [["index.html", "Home"], ["writing.html", "লেখা · Writing"], ["about.html#research", "Research"], ["about.html", "About"]];
  document.getElementById("site-header").outerHTML = `
  <div class="mock-note">Design mockup — not the live site. Posts load from abdullahdmc.com.</div>
  <header class="site-header"><div class="wrap">
    <a class="brand" href="index.html"><span class="brand-mark">AK</span>
      <span class="brand-name">Md. Abdullah Saeed Khan<small>Physician · Epidemiologist · Writer</small></span></a>
    <button class="menu-btn" aria-label="Menu" onclick="document.querySelector('.nav').classList.toggle('open')">☰</button>
    <nav class="nav">${links.map(([h, l]) => `<a href="${h}" class="${h === active ? "active" : ""}">${l}</a>`).join("")}</nav>
  </div></header>`;
  document.getElementById("site-footer").outerHTML = `
  <footer class="site-footer"><div class="wrap">
    <div>© ${new Date().getFullYear()} Md. Abdullah Saeed Khan</div>
    <div><a href="writing.html">Writing</a><a href="about.html#research">Publications</a><a href="about.html">About</a><a href="https://abdullahdmc.com/">Current site ↗</a></div>
  </div></footer>`;
}
