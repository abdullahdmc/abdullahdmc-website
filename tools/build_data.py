"""Turn the WordPress REST exports (posts1/2.json) into docs/assets/posts.js for the mockup.

Re-export with:
  curl "https://abdullahdmc.com/wp-json/wp/v2/posts?per_page=100&page=N&_fields=id,date,slug,link,title,excerpt,categories"
"""
import html
import json
import re
from pathlib import Path

HERE = Path(__file__).parent
OUT = HERE.parent / "docs" / "assets" / "posts.js"

# WordPress category id -> mockup theme. First match in THEME_ORDER wins.
THEMES = {
    "health": {21, 26, 20},
    "mind": {10, 19},
    "science": {3, 11, 17, 27, 23},
    "faith": {8, 7, 25, 16, 22, 24, 28},
    "literature": {1, 30, 14, 15, 9, 12, 29},
}
THEME_ORDER = ["health", "mind", "science", "faith", "literature"]


def theme_of(cats):
    for t in THEME_ORDER:
        if THEMES[t] & set(cats):
            return t
    return "essays"


def clean(s):
    s = re.sub(r"<[^>]+>", "", s)
    s = html.unescape(s)
    s = s.replace("[…]", "").replace("[&hellip;]", "")
    return re.sub(r"\s+", " ", s).strip()


def main():
    posts = []
    for name in ("posts1.json", "posts2.json"):
        posts += json.loads((HERE / name).read_text(encoding="utf-8"))
    cats = {c["id"]: html.unescape(c["name"]) for c in json.loads((HERE / "cats.json").read_text(encoding="utf-8"))}

    rows = []
    for p in posts:
        excerpt = clean(p["excerpt"]["rendered"])
        if len(excerpt) > 170:
            excerpt = excerpt[:170].rsplit(" ", 1)[0] + "…"
        rows.append({
            "id": p["id"],
            "date": p["date"][:10],
            "title": clean(p["title"]["rendered"]),
            "excerpt": excerpt,
            "theme": theme_of(p["categories"]),
            "cats": [cats[c] for c in p["categories"] if c in cats],
            "link": p["link"],
        })
    rows.sort(key=lambda r: (r["date"], r["id"]), reverse=True)
    OUT.write_text("window.POSTS = " + json.dumps(rows, ensure_ascii=False, indent=0) + ";\n", encoding="utf-8")
    print(f"{len(rows)} posts -> {OUT}")
    from collections import Counter
    print(Counter(r["theme"] for r in rows))


if __name__ == "__main__":
    main()
