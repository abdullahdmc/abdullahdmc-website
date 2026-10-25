"""Merge tools/publications.json (from the CV) with docs/assets/metrics.json (Google Scholar)
into docs/assets/research.js for the Research page.

Each CV entry is matched to a Scholar paper by title words; a match supplies the clean title,
venue, year, citation count and Scholar link.
"""
import json
import urllib.parse
import urllib.request
from datetime import date
import re
from pathlib import Path

HERE = Path(__file__).parent
ASSETS = HERE.parent / "docs" / "assets"

STOP = {"a", "an", "the", "of", "in", "and", "on", "for", "to", "with", "among", "from", "at", "by", "its", "is", "as"}

# First match wins; keys are icon names drawn in research.html.
TOPICS = [
    ("burden", r"global burden|gbd|204 countries|global, regional|life expectancy|causes of death"),
    ("mosquito", r"dengue|aedes|chikungunya|leishmaniasis|malaria"),
    ("virus", r"covid|sars|coronavirus|monkeypox|rabies|hiv|hepatitis|hbv"),
    ("syringe", r"vaccin|immuniz|hpv|tetanus"),
    ("bacteria", r"tubercul|\btb\b|antibiotic|antimicrobial|leprosy|poisoning"),
    ("brain", r"mental|suicid|anxiety|depress|psych|sleep|neuro|seizure|epilep|sclerosis|stroke"),
    ("ribbon", r"cancer|lymphoma|tumou?r|screening"),
    ("liver", r"liver|steatohepatitis|nafld|fatty|cirrhosis"),
    ("heart", r"cardio|hypertension|ldl|cholesterol|diabet|obesity|overweight|bmi|hyperglyc"),
    ("baby", r"child|newborn|neonat|adolescent|pediatr|paediatr|daughter|maternal"),
    ("people", r"quality of life|qol|worker|students|women|refugee|community|knowledge|awareness"),
    ("building", r"health system|grievance|referral|hospital|care-seeking|resilience|program"),
]


def words(s):
    return [w for w in re.findall(r"[a-z0-9]+", s.lower()) if w not in STOP]


def match(entry_text, papers, used):
    have = set(words(entry_text))
    best, score = None, 0.0
    for i, p in enumerate(papers):
        if i in used:
            continue
        tw = words(p["title"])
        if len(tw) < 3:
            continue
        s = sum(w in have for w in tw) / len(tw)
        if s > score:
            best, score = i, s
    return (best, score) if score >= 0.85 else (None, score)


def is_authors(seg):
    if "et al" in seg or "Collaborators" in seg:
        return True
    chunks = [c for c in seg.split(",") if c.strip()]
    return len(chunks) >= 2 and sum(len(c.split()) <= 3 for c in chunks) / len(chunks) > 0.7


def guess_title_venue(text):
    """Title and journal from an unmatched CV entry (APA or Vancouver style)."""
    m = re.search(r"\(\d{4}\)\.\s*(.+?[.?])\s+(.+?)[,.]", text)
    if m:
        return m.group(1).rstrip("."), m.group(2)
    parts = [p.strip() for p in re.split(r"\.\s+", text) if p.strip()]
    for i, p in enumerate(parts[:4]):
        if len(p.split()) > 4 and not is_authors(p):
            venue = parts[i + 1] if i + 1 < len(parts) else ""
            venue = re.split(r"\s*\[|\s\d|;|,", venue)[0]
            return p, venue
    return text[:140], ""


def venue_clean(v):
    return re.split(r"\s\d|,\s*\d", v)[0].strip(" ,") if v else ""


def topic(title):
    t = title.lower()
    for name, rx in TOPICS:
        if re.search(rx, t):
            return name
    return "paper"


def main():
    pubs = json.loads((HERE / "publications.json").read_text(encoding="utf-8"))
    metrics = json.loads((ASSETS / "metrics.json").read_text(encoding="utf-8"))
    papers = metrics["papers"]
    used, out, seen = set(), [], set()
    unmatched = 0
    for e in pubs:
        i, _ = match(e["text"], papers, used)
        if i is not None:
            used.add(i)
            p = papers[i]
            title, venue, year, cites, scholar = p["title"], venue_clean(p["venue"]), p["year"], p["cites"], p["scholar"]
        else:
            unmatched += 1
            title, venue = guess_title_venue(e["text"])
            # Titles mention study periods ("1990–2023"); the publication year is the latest one.
            years = [int(y) for y in re.findall(r"\b(20[0-3]\d|19\d\d)\b", e["text"]) if int(y) <= date.today().year]
            year = max(years) if years else None
            cites, scholar = None, None
        key = " ".join(words(title))[:80]
        if key in seen:
            continue
        seen.add(key)
        link = f"https://doi.org/{e['doi']}" if e["doi"] else (e["url"] or scholar)
        out.append({
            "title": title, "venue": venue, "year": year, "cites": cites, "link": link,
            "scholar": scholar, "first": e["first_author"], "notes": e["notes"], "icon": topic(title),
        })
    added, review = add_from_scholar(papers, used, pubs, out)
    out.sort(key=lambda p: -(p["year"] or 0))  # stable: CV order kept within a year
    data = {k: metrics[k] for k in ("updated", "scholar", "researchgate")}
    data["publications"] = out
    (ASSETS / "research.js").write_text("window.RESEARCH = " + json.dumps(data, ensure_ascii=False) + ";\n", encoding="utf-8")
    REVIEW.write_text(
        "Google Scholar entries NOT shown on the website (possible duplicates, preprints, corrections or older items).\n"
        "To show one, add it to tools/publications.json; to stop seeing one here, add its title to tools/publications_exclude.json.\n\n"
        + "\n".join(f"{p['year'] or '----'} | {p['venue'][:45]:45} | {p['title']}" for p in review) + "\n",
        encoding="utf-8")
    print(f"{len(out)} publications ({sum(p['first'] for p in out)} first-author), {unmatched} CV entries not matched to Scholar")
    print(f"added from Google Scholar: {len(added)}" + "".join(f"\n  + {p['year']} {p['title'][:90]}" for p in added))
    print(f"left for review in {REVIEW.name}: {len(review)}")


REVIEW = HERE / "scholar_review.txt"
EXCLUDE = HERE / "publications_exclude.json"
NOT_ARTICLE = re.compile(r"medrxiv|biorxiv|arxiv|ssrn|research square|preprint|socarxiv|osf", re.I)
MY_NAME_FIRST = re.compile(r"^(MAS|MA Saeed|M Abdullah Saeed|Md Abdullah Saeed|Md\.? A\.? S\.?) Khan\b")


def similarity(title, text):
    tw = words(title)
    have = set(words(text))
    return sum(w in have for w in tw) / len(tw) if tw else 0


def crossref_doi(title, year):
    """DOI for a title from Crossref, only when the top hit clearly is the same paper."""
    url = "https://api.crossref.org/works?" + urllib.parse.urlencode({"query.bibliographic": title, "rows": 3, "select": "DOI,title,issued"})
    try:
        req = urllib.request.Request(url, headers={"User-Agent": "abdullahdmc-website-builder"})
        items = json.loads(urllib.request.urlopen(req, timeout=30).read())["message"]["items"]
    except Exception:
        return None
    for it in items:
        t = (it.get("title") or [""])[0]
        if t and similarity(title, t) >= 0.9 and similarity(t, title) >= 0.9:
            return it["DOI"]
    return None


def add_from_scholar(papers, used, pubs, out):
    """Scholar papers missing from the CV list: recent, clearly new journal articles are added;
    everything else is left for the author to review (see scholar_review.txt)."""
    exclude = set(json.loads(EXCLUDE.read_text(encoding="utf-8"))) if EXCLUDE.exists() else set()
    known = [e["text"] for e in pubs] + [p["title"] for p in out]
    added, review = [], []
    recent = date.today().year - 1
    for i, p in enumerate(papers):
        if i in used or p["title"] in exclude:
            continue
        new = (p["year"] and p["year"] >= recent and p["venue"] and not NOT_ARTICLE.search(p["venue"] + " " + p["title"])
               and not p["title"].lower().startswith(("correction", "erratum"))
               and max(similarity(p["title"], k) for k in known) < 0.5)
        if not new:
            review.append(p)
            continue
        doi = crossref_doi(p["title"], p["year"])
        entry = {
            "title": p["title"], "venue": venue_clean(p["venue"]), "year": p["year"], "cites": p["cites"],
            "link": f"https://doi.org/{doi}" if doi else p["scholar"], "scholar": p["scholar"],
            "first": bool(MY_NAME_FIRST.match(p["authors"])), "notes": [], "icon": topic(p["title"]),
        }
        out.append(entry)
        known.append(p["title"])
        added.append(entry)
    return added, review


if __name__ == "__main__":
    main()
