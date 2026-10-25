"""Set up the redesign on the WordPress site in .env (staging first). Safe to run repeatedly.

  python tools/setup_wp.py

1. Creates the Writing, Research, Biography and Consultation pages if they don't exist.
2. Once the "abdullahdmc" child theme is active: assigns the page templates, makes Home the front page
   and Writing the posts page, and loads profile/research data (docs/assets/profile.js, research.js)
   into the amk_profile / amk_research options the theme reads.
"""
import json
import subprocess
from pathlib import Path

from wp import WP

ROOT = Path(__file__).resolve().parent.parent
ASSETS = ROOT / "docs" / "assets"

PAGES = {  # slug: (title, template)
    "writing": ("Writing", ""),
    "research": ("Research", "templates/template-research.php"),
    "biography": ("Biography", "templates/template-biography.php"),
    "consultation": ("Consultation", "templates/template-consultation.php"),
}


def load_profile():
    js = "global.window={};require(process.argv[1]);process.stdout.write(JSON.stringify(window.PROFILE))"
    return subprocess.run(["node", "-e", js, str(ASSETS / "profile.js")], capture_output=True, text=True, encoding="utf-8", check=True).stdout


def load_research():
    text = (ASSETS / "research.js").read_text(encoding="utf-8").strip()
    return json.dumps(json.loads(text[text.index("=") + 1:].rstrip(";")), ensure_ascii=False)


def main():
    wp = WP()
    active = next(t["stylesheet"] for t in wp.get("/wp/v2/themes", status="active"))
    print("active theme:", active)

    pages = {}
    for slug, (title, _) in PAGES.items():
        found = wp.get("/wp/v2/pages", slug=slug, status="publish,draft", _fields="id,slug,template")
        if found:
            pages[slug] = found[0]["id"]
        else:
            pages[slug] = wp.post("/wp/v2/pages", {"title": title, "slug": slug, "status": "publish", "content": ""})["id"]
            print("created page", slug, pages[slug])

    if active != "abdullahdmc":
        print("Child theme not active yet: pages are ready; activate the theme, then run this again.")
        return

    for slug, (_, template) in PAGES.items():
        if template:
            wp.post(f"/wp/v2/pages/{pages[slug]}", {"template": template})
    home = wp.get("/wp/v2/pages", slug="home", _fields="id")
    settings = {
        "show_on_front": "page",
        "page_on_front": home[0]["id"] if home else 0,
        "page_for_posts": pages["writing"],
        "amk_profile": load_profile(),
        "amk_research": load_research(),
    }
    result = wp.post("/wp/v2/settings", settings)
    print("front page:", result["page_on_front"], "| posts page:", result["page_for_posts"],
          "| profile:", len(result.get("amk_profile") or ""), "chars | research:", len(result.get("amk_research") or ""), "chars")


if __name__ == "__main__":
    main()
