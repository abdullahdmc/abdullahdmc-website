// Shared header, footer, themes and the generated post cover image.

const THEMES = {
  science:    { bn: "বিজ্ঞান ও বিবর্তন", en: "Science & Evolution", c1: "#0f5c5a", c2: "#178f86", motif: "helix" },
  mind:       { bn: "মন ও মস্তিষ্ক", en: "Mind & Brain", c1: "#3f3685", c2: "#6a5fc1", motif: "neuron" },
  health:     { bn: "চিকিৎসা ও গবেষণা", en: "Medicine & Research", c1: "#9b3434", c2: "#d0644a", motif: "pulse" },
  faith:      { bn: "বিশ্বাস ও দর্শন", en: "Faith & Philosophy", c1: "#1d3f66", c2: "#3a6ea5", motif: "star" },
  literature: { bn: "সাহিত্য ও রিভিউ", en: "Poems, Stories & Reviews", c1: "#7a4a17", c2: "#c08a3e", motif: "book" },
  essays:     { bn: "ভাবনা ও প্রবন্ধ", en: "Essays & Reflections", c1: "#34433a", c2: "#5f7a67", motif: "ripple" },
  ai:         { bn: "AI in Health Research", en: "Daily research roundup", c1: "#0b3d5c", c2: "#1f8ab0", motif: "circuit" },
  // Podcast episodes are AI in Health Research posts; this entry only styles their covers.
  podcast:    { bn: "Podcast", en: "Podcast", c1: "#082f47", c2: "#0f7fa6", motif: "headphones" },
};

// WordPress posts plus the auto-written feeds (philosophy posts, daily AI roundups), newest first.
function allPosts() {
  return [...(window.POSTS || []), ...(window.FEEDS || [])].sort((a, b) => b.date.localeCompare(a.date));
}

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
  headphones: `<path d="M30 120 V100 a70 70 0 0 1 140 0 V120" stroke-width="6" /><rect x="22" y="112" width="34" height="62" rx="12" /><rect x="144" y="112" width="34" height="62" rx="12" />
    ${[[78, 28], [90, 52], [102, 70], [114, 44], [126, 24]].map(([x, h]) => `<line x1="${x}" y1="${143 - h / 2}" x2="${x}" y2="${143 + h / 2}" stroke-width="6" />`).join("")}`,
  circuit: `<rect x="65" y="65" width="70" height="70" rx="8" /><rect x="85" y="85" width="30" height="30" rx="4" />
    ${[75, 100, 125].map(v => `<path d="M${v} 65 V20 M${v} 135 V180 M65 ${v} H20 M135 ${v} H180" /><circle cx="${v}" cy="16" r="4" /><circle cx="${v}" cy="184" r="4" /><circle cx="16" cy="${v}" r="4" /><circle cx="184" cy="${v}" r="4" />`).join("")}`,
};

// Simple line icons for profile links.
const LINK_ICONS = {
  scholar: `<path d="M12 3 2 9l10 6 10-6-10-6Z"/><path d="M6 11.5V16c0 1.7 2.7 3 6 3s6-1.3 6-3v-4.5"/>`,
  rg: `<rect x="3" y="3" width="18" height="18" rx="3"/><text x="12" y="16" text-anchor="middle" font-size="9" font-weight="700" fill="currentColor" stroke="none" font-family="Inter,sans-serif">RG</text>`,
  orcid: `<circle cx="12" cy="12" r="9"/><path d="M9 10v6M9 7.5v.1M12 10h2a3 3 0 0 1 0 6h-2v-6Z"/>`,
  scopus: `<circle cx="12" cy="12" r="9"/><path d="M15 9.5c-.6-.9-1.7-1.5-3-1.5-1.7 0-3 1-3 2.2 0 2.8 6 1.4 6 4.2 0 1.3-1.3 2.3-3 2.3-1.4 0-2.6-.6-3.2-1.6"/>`,
  wos: `<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 3.8 5.6 3.8 9s-1.3 6.4-3.8 9c-2.5-2.6-3.8-5.6-3.8-9S9.5 5.6 12 3Z"/>`,
  github: `<path d="M9 19c-4 1.3-4-2-6-2.5M15 21v-3.5c0-1 .1-1.4-.5-2 2.8-.3 5.5-1.4 5.5-6a4.6 4.6 0 0 0-1.3-3.2 4.2 4.2 0 0 0-.1-3.2s-1.1-.3-3.5 1.3a12 12 0 0 0-6.2 0C6.5 2.8 5.4 3.1 5.4 3.1a4.2 4.2 0 0 0-.1 3.2A4.6 4.6 0 0 0 4 9.5c0 4.6 2.7 5.7 5.5 6-.6.6-.6 1.2-.5 2V21"/>`,
  linkedin: `<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M8 10v7M8 7v.1M12 17v-4a2 2 0 0 1 4 0v4M12 10v7"/>`,
  facebook: `<path d="M15 3h-2.5A3.5 3.5 0 0 0 9 6.5V10H6.5v3.5H9V21h3.5v-7.5H15l.5-3.5h-3V7a1 1 0 0 1 1-1H15V3Z"/>`,
  x: `<path d="M4 4l16 16M20 4 4 20"/>`,
  youtube: `<rect x="2.5" y="5.5" width="19" height="13" rx="4"/><path d="m10 9 5 3-5 3V9Z" fill="currentColor"/>`,
  spotify: `<circle cx="12" cy="12" r="9.5"/><path d="M7.5 9.6c3-1 6.4-.8 9.2.7M8 12.6c2.5-.7 5.2-.5 7.4.7M8.6 15.4c1.9-.5 3.9-.3 5.6.6"/>`,
  mail: `<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>`,
};
function linkIcon(name) {
  return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${LINK_ICONS[name]}</svg>`;
}
function linkChips(list) {
  return list.map(([label, url, icon, sub]) =>
    `<a class="profile-link" href="${url}" target="_blank" rel="noopener">${linkIcon(icon)}<span>${label}${sub ? `<small>${sub}</small>` : ""}</span></a>`).join("");
}

function motifSVG(name, extra = "") {
  return `<svg class="motif" viewBox="-10 -10 220 220" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" ${extra} aria-hidden="true">${MOTIFS[name]}</svg>`;
}

function hash(id) {
  const n = typeof id === "number" ? id : [...String(id)].reduce((a, c) => (a * 31 + c.charCodeAt(0)) % 1000003, 7);
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

// Publications: preprints are listed separately and left out of publication counts.
function publishedPubs(list) {
  return list.filter(p => p.type !== "preprint");
}
function pubGroups(list) {
  const pub = publishedPubs(list);
  return [["first", "First author", pub.filter(p => p.first)], ["co", "Co-author", pub.filter(p => !p.first)],
    ["preprints", "Preprints", list.filter(p => p.type === "preprint")], ["all", "All", list]];
}
function pubTypePill(p) {
  return p.type === "chapter" ? `<span class="pill kind">Book chapter</span>` : p.type === "preprint" ? `<span class="pill kind pre">Preprint</span>` : "";
}

function postURL(post) { return `post.html?id=${post.id}`; }

function postDate(post) {
  return post.lang === "en" ? enDate(post.date) : bnDate(post.date);
}

function cardHTML(post) {
  const t = THEMES[post.theme];
  return `<a class="card" href="${postURL(post)}" style="--c1:${t.c1}">
    ${coverHTML(post)}
    <div class="card-body">
      <div class="card-meta"><span class="dot"></span>${t.bn} · ${postDate(post)}</div>
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
function enDate(iso) {
  return new Date(iso + "T00:00:00").toLocaleDateString("en-GB", { day: "numeric", month: "short", year: "numeric" });
}
function esc(s) { return String(s).replace(/[&<>"]/g, c => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" }[c])); }

function renderChrome(active) {
  const links = [["index.html", "Home"], ["writing.html", "লেখা · Writing"], ["research.html", "Research"], ["about.html", "Biography"], ["consultation.html", "Consultation", "nav-consult"]];
  document.getElementById("site-header").outerHTML = `
  <div class="mock-note">Design mockup — not the live site. Posts load from abdullahdmc.com.</div>
  <header class="site-header"><div class="wrap">
    <a class="brand" href="index.html"><span class="brand-mark">AK</span>
      <span class="brand-name">Md Abdullah Saeed Khan<small>MBBS, MPH, MPhil, DPhil (Ongoing)</small><small>Assistant Professor (Epidemiology)</small><small>Health Research &amp; Data Analytics Consultant</small></span></a>
    <button class="menu-btn" aria-label="Menu" onclick="document.querySelector('.nav').classList.toggle('open')">☰</button>
    <nav class="nav">${links.map(([h, l, cls = ""]) => `<a href="${h}" class="${cls} ${h === active ? "active" : ""}">${l}</a>`).join("")}</nav>
  </div></header>`;
  document.getElementById("site-footer").outerHTML = `
  <footer class="site-footer"><div class="wrap">
    <div>© ${new Date().getFullYear()} Md Abdullah Saeed Khan<br><a href="mailto:abdullahdmc@gmail.com">abdullahdmc@gmail.com</a></div>
    ${window.PROFILE ? `<div class="footer-icons">${[...PROFILE.links.research, ...PROFILE.links.social, ...PROFILE.links.youtube]
      .map(([label, url, icon]) => `<a href="${url}" target="_blank" rel="noopener" title="${label}" aria-label="${label}">${linkIcon(icon)}</a>`).join("")}</div>` : ""}
    <div><a href="writing.html">Writing</a><a href="research.html">Research</a><a href="about.html">Biography</a><a href="https://abdullahdmc.com/">Current site ↗</a></div>
  </div></footer>`;
}
