"""One-off: pull the publication list out of the CV .docx into tools/publications.json.

Usage: python tools/cv_to_publications.py "path/to/CV.docx"
After this, publications.json is the master list; edit it by hand or re-run on a newer CV.
"""
import json
import re
import sys
from pathlib import Path

import docx

HERE = Path(__file__).parent


def cell_texts(doc):
    for tbl in doc.tables:
        for row in tbl.rows:
            yield [c.text.strip() for c in row.cells]


def trim_doi(d):
    # DOIs may contain parentheses, e.g. S2352-3018(26)00147-5; drop only unbalanced trailing ones.
    d = d.rstrip(".,;")
    while d.endswith(")") and d.count("(") < d.count(")"):
        d = d[:-1]
    return d


def main(path):
    doc = docx.Document(path)
    section, out = None, []
    for cells in cell_texts(doc):
        if len(cells) < 2:
            continue
        details = cells[1]
        low = details.lower()
        if low == "as first author":
            section = "first"; continue
        if low == "as co-author":
            section = "co"; continue
        if low == "abstract":
            section = "abstract"; continue
        if section is None or not details or section == "abstract":
            continue
        if cells[0] == "No" or details == "Details":
            continue
        doi = re.search(r"10\.\d{4,9}/[^\s\]]+", details)
        url = re.search(r"https?://\S+", details)
        notes = re.findall(r"\[(Co-first author|Corresponding [Aa]uthor)\]", details)
        out.append({
            "text": details,
            "first_author": section == "first",
            "notes": [n.capitalize() for n in notes],
            "doi": trim_doi(doi.group(0)) if doi else None,
            "url": url.group(0).rstrip(".") if url else None,
            "contribution": cells[2] if len(cells) > 2 else "",
        })
    (HERE / "publications.json").write_text(json.dumps(out, ensure_ascii=False, indent=1), encoding="utf-8")
    print(len(out), "entries;", sum(p["first_author"] for p in out), "first-author")


if __name__ == "__main__":
    main(sys.argv[1])
