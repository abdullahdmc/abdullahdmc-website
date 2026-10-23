"""Merge tools/publications.json (from the CV) with docs/assets/metrics.json (Google Scholar)
into docs/assets/research.js for the Research page.

Each CV entry is matched to a Scholar paper by title words; a match supplies the clean title,
venue, year, citation count and Scholar link.
"""
import json
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
    out.sort(key=lambda p: -(p["year"] or 0))  # stable: CV order kept within a year
    data = {k: metrics[k] for k in ("updated", "scholar", "researchgate")}
    data["publications"] = out
    (ASSETS / "research.js").write_text("window.RESEARCH = " + json.dumps(data, ensure_ascii=False) + ";\n", encoding="utf-8")
    print(f"{len(out)} publications ({sum(p['first'] for p in out)} first-author), {unmatched} not matched to Scholar")


if __name__ == "__main__":
    main()
