"""Build the publication list for the Research page from Google Scholar -> docs/assets/research.js

Google Scholar (docs/assets/metrics.json, refreshed by update_metrics.py) is the master list.
- Duplicate Scholar records of the same paper are merged (the most complete one is kept).
- A preprint is dropped once the same paper appears as a published article.
- Corrections/errata are left out.
- tools/publications.json (from the CV) only adds details: DOI, first author, co-first/corresponding notes.
- Links: DOI from the CV, else from Crossref (cached in tools/doi_cache.json), else the Scholar record.
- tools/publication_types.json marks special types by title, e.g. {"<title>": "chapter"}.
- tools/publications_exclude.json lists titles to leave out.
Everything left out, and CV entries not found on Scholar, is listed in tools/scholar_review.txt.
"""
import json
import re
import time
import urllib.parse
import urllib.request
from pathlib import Path

HERE = Path(__file__).parent
ASSETS = HERE.parent / "docs" / "assets"
REVIEW = HERE / "scholar_review.txt"
TYPES = HERE / "publication_types.json"
EXCLUDE = HERE / "publications_exclude.json"
DOI_CACHE = HERE / "doi_cache.json"

STOP = {"a", "an", "the", "of", "in", "and", "on", "for", "to", "with", "among", "from", "at", "by", "its", "is", "as"}
PREPRINT = re.compile(r"medrxiv|biorxiv|arxiv|ssrn|research square|preprint|socarxiv|osf preprints", re.I)
CORRECTION = re.compile(r"^(correction|erratum|corrigendum)\b", re.I)
MY_NAME_FIRST = re.compile(r"^(MAS|MA Saeed|M Abdullah Saeed|Md Abdullah Saeed|AS) Khan\b")

# First match wins; keys are icon names drawn on the Research page.
TOPICS = [
    ("burden", r"global burden|gbd|204 countries|global, regional|life expectancy|causes of death"),
    ("mosquito", r"dengue|aedes|chikungunya|leishmaniasis|malaria"),
    ("virus", r"covid|sars|coronavirus|monkeypox|rabies|hiv|hepatitis|hbv|measles"),
    ("syringe", r"vaccin|immuniz|hpv|tetanus"),
    ("bacteria", r"tubercul|\btb\b|antibiotic|antimicrobial|leprosy|poisoning"),
    ("brain", r"mental|suicid|anxiety|depress|psych|sleep|neuro|seizure|epilep|sclerosis|stroke"),
    ("ribbon", r"cancer|lymphoma|tumou?r|screening"),
    ("liver", r"liver|steatohepatitis|nafld|fatty|cirrhosis"),
    ("heart", r"cardio|hypertension|blood pressure|ldl|cholesterol|dyslipid|diabet|insulin|obesity|overweight|bmi|hyperglyc"),
    ("baby", r"child|newborn|neonat|adolescent|pediatr|paediatr|daughter|maternal"),
    ("people", r"quality of life|qol|worker|students|women|refugee|community|knowledge|awareness|class|income"),
    ("building", r"health system|grievance|referral|hospital|care-seeking|resilience|program"),
]


def words(s):
    return [w for w in re.findall(r"[a-z0-9]+", s.lower()) if w not in STOP]


def similarity(title, text):
    """Share of the title's words that appear in text (0..1)."""
    tw = words(title)
    have = set(words(text))
    return sum(w in have for w in tw) / len(tw) if tw else 0


def same_paper(a, b):
    return similarity(a, b) >= 0.85 and similarity(b, a) >= 0.85


def clean_title(t):
    return re.sub(r"^Research Article\s+", "", t).strip()


def venue_clean(v):
    return re.split(r"\s\d|,\s*\d", v)[0].strip(" ,") if v else ""


def topic(title):
    t = title.lower()
    for name, rx in TOPICS:
        if re.search(rx, t):
            return name
    return "paper"


def load(path, default):
    return json.loads(path.read_text(encoding="utf-8")) if path.exists() else default


def crossref(title, cache):
    """Crossref records that clearly are this paper: [{doi, venue, year, preprint}] (cached by title)."""
    if title in cache:
        return cache[title]
    url = "https://api.crossref.org/works?" + urllib.parse.urlencode(
        {"query.bibliographic": title, "rows": 5, "select": "DOI,title,type,container-title,issued"})
    req = urllib.request.Request(url, headers={"User-Agent": "abdullahdmc-website-builder"})
    for attempt in range(3):
        try:
            time.sleep(0.3 + 2 * attempt)  # stay well under Crossref's rate limit
            items = json.loads(urllib.request.urlopen(req, timeout=30).read())["message"]["items"]
            break
        except Exception:
            items = None
    if items is None:
        return []  # not cached: try again next time
    hits = []
    for it in items:
        t = (it.get("title") or [""])[0]
        if not t or not same_paper(title, t):
            continue
        year = ((it.get("issued") or {}).get("date-parts") or [[None]])[0][0]
        venue = (it.get("container-title") or [""])[0]
        hits.append({"doi": it["DOI"], "venue": venue, "year": year, "preprint": it.get("type") == "posted-content"})
    cache[title] = hits
    return hits


def completeness(p):
    return (bool(p["venue"]), p["year"] is not None, not PREPRINT.search(p["venue"]), p["cites"])


def main():
    metrics = load(ASSETS / "metrics.json", None)
    cv = load(HERE / "publications.json", [])
    types = load(TYPES, {})
    exclude = set(load(EXCLUDE, []))
    cache = load(DOI_CACHE, {})
    left_out = []

    # 1. Scholar records, minus exclusions and corrections, with duplicate records merged.
    groups = []
    for p in metrics["papers"]:
        p = dict(p, title=clean_title(p["title"]))
        if p["title"] in exclude:
            left_out.append(("excluded", p))
            continue
        if CORRECTION.search(p["title"]):
            left_out.append(("correction", p))
            continue
        for g in groups:
            if same_paper(p["title"], g[0]["title"]):
                g.append(p)
                break
        else:
            groups.append([p])
    papers = []
    for g in groups:
        g.sort(key=completeness, reverse=True)
        best = dict(g[0], cites=max(x["cites"] for x in g))
        papers.append(best)
        left_out += [("duplicate record", x) for x in g[1:]]

    # 2. Types; drop preprints that were later published as articles.
    for p in papers:
        p["type"] = types.get(p["title"]) or ("preprint" if PREPRINT.search(p["venue"]) else "article")
    published = [p for p in papers if p["type"] != "preprint"]
    kept = []
    for p in papers:
        if p["type"] == "preprint" and any(similarity(p["title"], q["title"]) >= 0.75 for q in published):
            left_out.append(("preprint later published", p))
        else:
            kept.append(p)

    # 3. Details from the CV and links.
    cv_used = set()
    out = []
    for p in kept:
        e = next((e for i, e in enumerate(cv) if i not in cv_used and similarity(p["title"], e["text"]) >= 0.85), None)
        if e:
            cv_used.add(cv.index(e))
        hits = crossref(p["title"], cache)
        journal = next((h for h in hits if not h["preprint"]), None)
        preprint_rec = next((h for h in hits if h["preprint"]), None)
        venue = "medRxiv" if re.search("medrxiv", p["venue"], re.I) else venue_clean(p["venue"])
        year = p["year"]
        if p["type"] == "preprint" and journal:
            # Scholar only lists the preprint, but Crossref shows it was published in a journal.
            if any(same_paper(p["title"], q["title"]) for q in out if q["type"] != "preprint"):
                left_out.append(("preprint later published", p))
                continue
            p["type"], venue, year = "article", journal["venue"] or venue, journal["year"] or year
        rec = preprint_rec if p["type"] == "preprint" else journal
        doi = (e or {}).get("doi") if p["type"] != "preprint" else None
        doi = doi or (rec or {}).get("doi")
        year = year or (rec or {}).get("year")
        out.append({
            "title": p["title"], "venue": venue, "year": year, "cites": p["cites"],
            "link": f"https://doi.org/{doi}" if doi else ((e or {}).get("url") or p["scholar"]), "scholar": p["scholar"],
            "first": bool(MY_NAME_FIRST.match(p["authors"])) or bool(e and e["first_author"]),
            "notes": (e or {}).get("notes", []), "type": p["type"], "icon": topic(p["title"]),
        })
    out.sort(key=lambda p: -(p["year"] or 0))  # stable: Scholar's newest-first order kept within a year
    DOI_CACHE.write_text(json.dumps(cache, ensure_ascii=False, indent=1), encoding="utf-8")

    data = {k: metrics[k] for k in ("updated", "scholar", "researchgate")}
    data["publications"] = out
    (ASSETS / "research.js").write_text("window.RESEARCH = " + json.dumps(data, ensure_ascii=False) + ";\n", encoding="utf-8")

    # CV entries whose paper is on Scholar under a reworded title only miss the CV details; report the real gaps.
    cv_missing = [e for i, e in enumerate(cv) if i not in cv_used
                  and max((similarity(p["title"], e["text"]) for p in out), default=0) < 0.6]
    lines = ["Google Scholar records NOT shown on the website, and why.",
             "To hide a paper, add its exact title to tools/publications_exclude.json; to mark a book chapter, add it to tools/publication_types.json.", ""]
    lines += [f"{why:26} | {p['year'] or '----'} | {p['venue'][:40]:40} | {p['title']}" for why, p in left_out]
    maybe = [(a, b) for i, a in enumerate(out) for b in out[i + 1:]
             if 0.7 <= min(similarity(a["title"], b["title"]), similarity(b["title"], a["title"])) and not same_paper(a["title"], b["title"])
             and not ("global burden of disease" in a["title"].lower() and "global burden of disease" in b["title"].lower())]
    lines += ["", "Shown on the website but possibly the same paper twice (check on Google Scholar; merge or hide one):", ""]
    lines += [f"  - {a['year']} {a['title']}\n    {b['year']} {b['title']}" for a, b in maybe]
    lines += ["", "In the CV list but not found on Google Scholar (add them to Scholar if they should appear):", ""]
    lines += ["  - " + e["text"][:200] for e in cv_missing]
    REVIEW.write_text("\n".join(lines) + "\n", encoding="utf-8")

    n = {t: sum(p["type"] == t for p in out) for t in ("article", "chapter", "preprint")}
    print(f"{len(out)} on the website: {n['article']} articles, {n['chapter']} book chapters, {n['preprint']} preprints; "
          f"{sum(p['first'] for p in out)} first-author; {sum(1 for p in out if 'doi.org' in p['link'])} with DOI links")
    print(f"left out: {len(left_out)} ({', '.join(sorted({w for w, _ in left_out}))}); CV entries not on Scholar: {len(cv_missing)}")


if __name__ == "__main__":
    main()
