// Fills the data-driven parts of each page from window.PROFILE and window.RESEARCH
// (the amk_profile / amk_research options). Each block runs only if its element is on the page.
(function () {
  const P = window.PROFILE, R = window.RESEARCH;
  const $ = id => document.getElementById(id);
  const fmt = n => n == null ? "—" : Number(n).toLocaleString("en-US");
  const rows = (list, f) => list.map(f).join("");
  const S = R && R.scholar;

  // Footer icons and theme tile motifs (every page).
  if (P && $("footer-icons")) {
    $("footer-icons").innerHTML = [...P.links.research, ...P.links.social, ...P.links.youtube]
      .map(([label, url, icon]) => `<a href="${url}" target="_blank" rel="noopener" title="${label}" aria-label="${label}">${linkIcon(icon)}</a>`).join("");
  }
  document.querySelectorAll(".theme-tile[data-theme]").forEach(el => {
    const t = THEMES[el.dataset.theme];
    if (t) el.insertAdjacentHTML("afterbegin", motifSVG(t.motif));
  });

  // ---- Home ----
  if (R && $("pub-count")) $("pub-count").textContent = R.publications.length;
  if (S && $("scholar-card")) {
    const yrs = Object.entries(S.per_year).slice(-6);
    const peak = Math.max(...yrs.map(([, v]) => v));
    $("scholar-card").innerHTML = `
      <div class="sc-head"><span>${linkIcon("scholar")}Google Scholar</span><small>Updated ${enDate(R.updated)}</small></div>
      <div class="sc-body"><table><thead><tr><th></th><th>All</th><th>Since ${S.since}</th></tr></thead><tbody>
        <tr><td>Citations</td><td>${fmt(S.citations)}</td><td>${fmt(S.citations_recent)}</td></tr>
        <tr><td>h-index</td><td>${S.h_index}</td><td>${S.h_index_recent}</td></tr>
        <tr><td>i10-index</td><td>${S.i10_index}</td><td>${S.i10_index_recent}</td></tr>
      </tbody></table>
      <div class="sc-bars">${yrs.map(([y, v]) => `<div title="${y}: ${fmt(v)} citations"><i style="height:${Math.max(4, v / peak * 100)}%"></i><span>${y.slice(2)}</span></div>`).join("")}</div></div>`;
  }
  if (R && $("recent-pubs")) {
    $("recent-pubs").innerHTML = R.publications.slice(0, 4).map(p => `<li class="pub">
      <a href="${p.link || p.scholar || AMK.research}" target="_blank" rel="noopener"><span class="pub-title">${esc(p.title)}</span>
      <div class="pub-meta">${p.venue ? `<span class="j">${esc(p.venue)}</span>` : ""}<span>${p.year || "In press"}</span>${p.first ? "<span>First author</span>" : ""}${p.cites ? `<span>${fmt(p.cites)} citations</span>` : ""}</div></a></li>`).join("");
  }
  if (P && $("yt")) {
    $("yt").innerHTML = P.links.youtube.map(([l, u, ic, sub]) =>
      `<a href="${u}" target="_blank" rel="noopener">${linkIcon(ic)}<div><b>${l}</b><span>YouTube · ${sub}</span></div></a>`).join("");
  }
  if (P && $("all-links")) $("all-links").innerHTML = linkChips([...P.links.research, ...P.links.social]);

  // ---- Research ----
  if (R && P && $("metrics")) {
    $("rlinks").innerHTML = linkChips(P.links.research);
    const years = Object.entries(S.per_year).slice(-8);
    const max = Math.max(...years.map(([, v]) => v));
    const rg = R.researchgate || {};
    $("metrics").innerHTML = `
      <div class="metric"><b>${fmt(S.citations)}</b><span class="lbl">Citations</span><span class="sub">${fmt(S.citations_recent)} since ${S.since}</span></div>
      <div class="metric"><b>${S.h_index}</b><span class="lbl">h-index</span><span class="sub">${S.h_index_recent} since ${S.since}</span></div>
      <div class="metric"><b>${S.i10_index}</b><span class="lbl">i10-index</span><span class="sub">${S.i10_index_recent} since ${S.since}</span></div>
      <div class="metric"><b>${rg.research_interest_score ?? fmt(R.publications.length)}</b><span class="lbl">${rg.research_interest_score != null ? "ResearchGate interest" : "Publications listed"}</span>
        <span class="sub">${rg.research_interest_score != null ? `${fmt(rg.reads)} reads` : "peer-reviewed articles"}</span></div>
      <div class="metric chart"><span class="lbl" style="margin:0">Citations per year</span>
        <div class="bars">${years.map(([y, v]) => `<div style="height:${Math.max(4, v / max * 100)}%" title="${y}: ${fmt(v)}"></div>`).join("")}</div>
        <div class="bars-x">${years.map(([y]) => `<span>${y.slice(2)}</span>`).join("")}</div></div>`;
    $("mnote").innerHTML = `<span>Source: <a href="${S.url}" target="_blank" rel="noopener">Google Scholar</a>, updated monthly · last update ${enDate(R.updated)}</span>
      <span><a href="${rg.url || "https://www.researchgate.net/"}" target="_blank" rel="noopener">ResearchGate profile ↗</a>${rg.as_of ? ` (figures as of ${enDate(rg.as_of)})` : ""}</span>`;
    $("proj").innerHTML = rows(P.projects, p => `<div class="box"><span class="pill">${p.status}</span><h3>${p.title}</h3><div class="meta">${p.role}${p.with ? " · " + p.with : ""}</div></div>`);
    $("grants").innerHTML = rows(P.grants, g => `<div class="box"><span class="pill gold">${g.year} · ${g.role}</span><h3>${g.title}</h3><div class="amount">${g.amount}</div><div class="meta">${g.funder}</div></div>`);

    const PUB_ICONS = {
      burden:   ["#1d3f66", `<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 3.8 5.6 3.8 9s-1.3 6.4-3.8 9c-2.5-2.6-3.8-5.6-3.8-9S9.5 5.6 12 3Z"/>`],
      mosquito: ["#7a4a17", `<ellipse cx="12" cy="13" rx="2" ry="5"/><circle cx="12" cy="6.5" r="1.6"/><path d="M12 5 10 2M10.5 10 4 6.5M13.5 10 20 6.5M10.5 13l-5 4M13.5 13l5 4M10.8 16.5 8 21M13.2 16.5 16 21"/>`],
      virus:    ["#9b3434", `<circle cx="12" cy="12" r="5"/><path d="M12 2v5M12 17v5M2 12h5M17 12h5M4.9 4.9l3.5 3.5M15.6 15.6l3.5 3.5M4.9 19.1l3.5-3.5M15.6 8.4l3.5-3.5"/>`],
      syringe:  ["#0f5c5a", `<path d="m18 2 4 4M17 7l-3-3M15 5 5.5 14.5l4 4L19 9M7.5 16.5 3 21M10 9l2 2M8 11l2 2"/>`],
      bacteria: ["#5c6b1f", `<rect x="4" y="8" width="16" height="8" rx="4"/><path d="M8 8V5M12 8V4M16 8V5M8 16v3M12 16v4M16 16v3"/>`],
      brain:    ["#3f3685", `<path d="M12 5a3 3 0 0 0-5.8 1A3 3 0 0 0 4 11a3 3 0 0 0 1.5 5A3 3 0 0 0 12 18V5Zm0 0a3 3 0 0 1 5.8 1A3 3 0 0 1 20 11a3 3 0 0 1-1.5 5 3 3 0 0 1-6.5 2"/>`],
      ribbon:   ["#a3376b", `<path d="M12 3a3.5 3.5 0 0 0-3.5 3.5c0 2.2 1.6 4.8 3.5 7 1.9-2.2 3.5-4.8 3.5-7A3.5 3.5 0 0 0 12 3ZM9.5 11.5 5 21l3-1 1.5 2.5L13 15M14.5 11.5 19 21l-3-1-1.5 2.5L11 15"/>`],
      liver:    ["#8a3f1f", `<path d="M3 9c0-3 3-5 8-5 5.5 0 10 1.5 10 5 0 4-6 9-11 11C6 15 3 13 3 9Z"/><path d="M12 4c-1 3-1 6 1 9"/>`],
      heart:    ["#b0443a", `<path d="M20.4 5.6a5 5 0 0 0-7.1 0L12 7l-1.3-1.4a5 5 0 1 0-7.1 7.1L12 21l8.4-8.3a5 5 0 0 0 0-7.1Z"/><path d="M3 12h4l2-3 3 6 2-3h3"/>`],
      baby:     ["#c08a3e", `<circle cx="12" cy="8" r="5"/><path d="M10 8h.01M14 8h.01M10.5 10.5c.8.6 2.2.6 3 0M6 21c0-3.3 2.7-6 6-6s6 2.7 6 6"/>`],
      people:   ["#34433a", `<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.6 2.9-6.5 6.5-6.5s6.5 2.9 6.5 6.5"/><circle cx="17" cy="9" r="2.5"/><path d="M16 13.6c3 .3 5.5 2.9 5.5 6.4"/>`],
      building: ["#4a5263", `<path d="M3 21h18M5 21V8l7-5 7 5v13M9 21v-5h6v5M9 11h.01M15 11h.01M12 11h.01"/>`],
      paper:    ["#4a5263", `<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6Z"/><path d="M14 3v6h6M8 13h8M8 17h5"/>`],
    };
    const icon = k => { const [c, d] = PUB_ICONS[k] || PUB_ICONS.paper;
      return `<span class="picon" style="--ic:${c}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">${d}</svg></span>`; };
    const pubs = R.publications;
    const groups = [["first", "First author", pubs.filter(p => p.first)], ["co", "Co-author", pubs.filter(p => !p.first)], ["all", "All", pubs]];
    let tab = "first";
    const ptabs = $("ptabs");
    ptabs.innerHTML = groups.map(([k, l, list]) => `<button class="chip" data-k="${k}">${l} <small>${list.length}</small></button>`).join("");
    const pubHTML = p => {
      const title = p.link ? `<a class="pt" href="${p.link}" target="_blank" rel="noopener">${esc(p.title)}</a>` : `<span class="pt" style="font:600 16.5px/1.45 var(--serif)">${esc(p.title)}</span>`;
      const cite = p.cites == null ? `<span class="cite"><b>—</b><span>CITATIONS</span></span>`
        : `<a class="cite" href="${p.scholar}" target="_blank" rel="noopener" title="Google Scholar record"><b>${fmt(p.cites)}</b><span>CITATIONS</span></a>`;
      return `<li class="pitem">${icon(p.icon)}<div>${title}<div class="pm">${p.venue ? `<span class="j">${esc(p.venue)}</span>` : ""}${p.first ? `<span class="pill gold">First author</span>` : ""}${p.notes.map(n => `<span class="pill">${n}</span>`).join("")}</div></div>${cite}</li>`;
    };
    const drawPubs = () => {
      ptabs.querySelectorAll(".chip").forEach(c => c.setAttribute("aria-pressed", c.dataset.k === tab));
      const q = $("pq").value.trim().toLowerCase();
      const list = groups.find(g => g[0] === tab)[2].filter(p => !q || (p.title + " " + p.venue).toLowerCase().includes(q));
      const byYear = {};
      list.forEach(p => (byYear[p.year || "In press"] ||= []).push(p));
      $("plist").innerHTML = list.length ? Object.keys(byYear).sort((a, b) => (+b || 0) - (+a || 0))
        .map(y => `<div class="year-h">${y}</div><ul class="plist">${byYear[y].map(pubHTML).join("")}</ul>`).join("")
        : `<div class="empty">No publications match.</div>`;
    };
    ptabs.onclick = e => { const b = e.target.closest(".chip"); if (b) { tab = b.dataset.k; drawPubs(); } };
    $("pq").oninput = drawPubs;
    drawPubs();
    $("editorial").innerHTML = rows(P.editorial, e => `<li>${e}</li>`);
    $("rcount").textContent = P.reviewer.length;
    $("reviewer").innerHTML = rows(P.reviewer, j => `<span>${j}</span>`);
  }

  // ---- Biography ----
  if (P && $("bio")) {
    $("name").textContent = P.name;
    $("bio").innerHTML = rows(P.bio, p => `<p>${p}</p>`);
    $("links").innerHTML = linkChips([...P.links.research, ...P.links.social]);
    $("yt-side").innerHTML = linkChips(P.links.youtube.map(([l, u, ic]) => [l, u, ic]));
    $("affil").innerHTML = rows(P.affiliations, a => `<div class="box"><span class="pill">Since ${a.since}</span><h3>${a.org}</h3><div class="meta">${a.role}</div></div>`);
    $("edu").innerHTML = rows(P.education, e => `<li><b>${e.degree}</b><span>${e.inst} · ${e.year}</span>${e.note ? `<em>${e.note}</em>` : ""}</li>`);
    $("awards").innerHTML = rows(P.awards, a => `<li>${a}</li>`);
    $("members").innerHTML = rows(P.memberships, m => `<li>${m}</li>`);
    $("training").innerHTML = rows(P.training, ([t, i, y]) => `<li><b>${t}</b><span class="where">${i}</span><span class="when">${y}</span></li>`);
    $("conf").innerHTML = rows(P.conferences, ([t, i, y]) => `<li><b>${t}</b><span class="where">${i}</span><span class="when">${y}</span></li>`);
    $("social").innerHTML = linkChips([...P.links.social, ...P.links.youtube.map(([l, u, i]) => [l, u, i])]);
    const X = P.experience;
    const line = ([r, o, d]) => `<li><b>${r}</b><span class="where">${o}</span><span class="when">${d}</span></li>`;
    const tabs = {
      professional: ["Professional", () => `<ul class="rows">${rows(X.professional, line)}</ul>`],
      research: ["Research", () => `<ul class="rows">${rows(X.research, line)}</ul>`],
      analysis: ["Data analysis", () => `<ul class="rows">${rows(X.analysis, ([r, o, d, x]) => `<li><b>${r}</b><span class="where">${o}</span><span class="when">${d}</span><span class="extra">${x}</span></li>`)}</ul>
        <div class="chips-wrap" style="margin-top:14px">${X.tools.map(t => `<span>${t}</span>`).join("")}</div>`],
      teaching: ["Teaching", () => `<ul class="rows">${rows(X.teaching, ([t, o, d, x]) => `<li><b>${t}</b><span class="where">${o}${x ? " · " + x : ""}</span><span class="when">${d}</span></li>`)}</ul>`],
    };
    const xtabs = $("xtabs");
    xtabs.innerHTML = Object.entries(tabs).map(([k, [l]]) => `<button class="tab" role="tab" data-k="${k}">${l}</button>`).join("");
    const show = k => {
      xtabs.querySelectorAll(".tab").forEach(t => t.setAttribute("aria-selected", t.dataset.k === k));
      $("xpanel").innerHTML = tabs[k][1]();
    };
    xtabs.onclick = e => { const b = e.target.closest(".tab"); if (b) show(b.dataset.k); };
    show("professional");
  }

  // ---- Consultation ----
  if ($("services")) {
    const SERVICES = [
      ["Proposal development", "Research questions, study design, sample size, methods and analysis plans for grant applications, theses and ethics submissions.",
        `<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6Z"/><path d="M14 3v6h6M9 14h6M12 11v6"/>`],
      ["Data analysis", "Statistical analysis in R, Stata, SPSS or Python for cross-sectional, case-control, cohort and trial data, systematic reviews and meta-analyses, and prediction models.",
        `<path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/>`],
      ["Manuscript writing", "Turning your results into a clear manuscript: structure, tables and figures, interpretation and reporting to journal guidelines.",
        `<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/>`],
      ["Manuscript review", "A critical pre-submission review of methods, statistics, results and writing, with clear suggestions for improvement.",
        `<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4M8 11l2 2 4-4"/>`],
      ["Research consultation", "One-to-one advice on any stage of your project: methodology, analysis choices, responding to reviewers or planning your next study.",
        `<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2Z"/><path d="M8 9h8M8 13h5"/>`],
    ];
    $("services").innerHTML = SERVICES.map(([t, d, ic]) => `<div class="svc">
      <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">${ic}</svg></div>
      <h3>${t}</h3><p>${d}</p></div>`).join("");
    const body = [
      "Dear Dr. Khan,", "",
      "I would like an independent consultation and the fee details for:",
      "[ ] Proposal development  [ ] Data analysis  [ ] Manuscript writing  [ ] Manuscript review  [ ] Research consultation", "",
      "Project title and study design:", "", "Brief description:", "", "Deadline:", "", "Thank you.",
    ].join("\n");
    const mailto = `mailto:abdullahdmc@gmail.com?subject=${encodeURIComponent("Consultation request")}&body=${encodeURIComponent(body)}`;
    $("mail-top").href = mailto;
    $("mail-bottom").href = mailto;
  }
})();
