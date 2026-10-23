"""Turn the two auto-writing repos into website posts -> docs/assets/feeds.js

- abdullahdmc/bangla-facebook-post-creator: philosophy (arguments for God's existence) posts.
  Only posts the author has reviewed ("edited by author" in LOG.md) are used. Each goes live
  one week after it was written, dated with that publish day.
- abdullahdmc/AI-in-healthcare: daily "AI in Healthcare: Research Roundup", one post per day,
  tagged "AI in health research".

Needs the GitHub CLI (gh) signed in.
"""
import base64
import json
import re
import subprocess
from datetime import date, timedelta
from pathlib import Path

OUT = Path(__file__).parent.parent / "docs" / "assets" / "feeds.js"
PHILOSOPHY_REPO = "abdullahdmc/bangla-facebook-post-creator"
AI_REPO = "abdullahdmc/AI-in-healthcare"
PUBLISH_DELAY = timedelta(days=7)
TITLES = Path(__file__).parent / "philosophy_titles.json"


def gh(path):
    return json.loads(subprocess.run(["gh", "api", path], capture_output=True, text=True, encoding="utf-8", check=True).stdout)


def file_text(repo, path):
    return base64.b64decode(gh(f"repos/{repo}/contents/{path}")["content"]).decode("utf-8")


def paragraphs(text):
    return [p.strip() for p in re.split(r"\n\s*\n", text) if p.strip()]


def excerpt(text, n=170):
    t = re.sub(r"\s+", " ", text).strip()
    return t if len(t) <= n else t[:n].rsplit(" ", 1)[0] + "…"


def philosophy_posts(today):
    log = file_text(PHILOSOPHY_REPO, "LOG.md")
    # Bangla titles by post file name; posts without one fall back to the English LOG.md topic.
    titles = json.loads(TITLES.read_text(encoding="utf-8"))
    posts = []
    for row in re.findall(r"^\| (\d{4}-\d{2}-\d{2}) \| (.+?) \| \[(posts/[^\]]+)\]\([^)]+\) \| (.+?) \|$", log, re.M):
        written, topic, path, status = row
        if not status.startswith("edited by author"):
            continue
        live = date.fromisoformat(written) + PUBLISH_DELAY
        if live > today:
            continue
        body = paragraphs(file_text(PHILOSOPHY_REPO, path))
        stem = Path(path).stem
        if stem not in titles:
            print(f"No Bangla title for {stem}; add one to {TITLES.name}")
        posts.append({
            "id": "ph-" + stem,
            "date": live.isoformat(),
            "title": titles.get(stem, topic),
            "title_en": topic,
            "excerpt": excerpt(body[0]),
            "theme": "faith",
            "cats": ["স্রষ্টার অস্তিত্বের যুক্তি", "দর্শন"],
            "body": body,
            "source": "philosophy",
        })
    return posts


def ai_posts():
    tree = gh(f"repos/{AI_REPO}/git/trees/HEAD?recursive=1")["tree"]
    files = sorted(t["path"] for t in tree if re.fullmatch(r"linkedin_posts/linkedin_post_\d{4}-\d{2}-\d{2}\.txt", t["path"]))
    posts = []
    for path in files:
        day = re.search(r"(\d{4}-\d{2}-\d{2})", path).group(1)
        text = file_text(AI_REPO, path)
        main, _, sources = text.partition("\nSources:")
        paras = paragraphs(main)
        title = paras.pop(0)
        if not re.search(r"\d{4}", title):  # most daily titles are identical; the date tells them apart
            title += " — " + date.fromisoformat(day).strftime("%d %B %Y").lstrip("0")
        tags = [p for p in paras if p.startswith("#")]
        body = [p for p in paras if not p.startswith("#")]
        refs = []
        for line in sources.strip().splitlines():
            m = re.match(r"\d+\.\s*(.+?)\s+[—-]\s+(https?://\S+)", line.strip())
            if m:
                refs.append({"title": m.group(1), "url": m.group(2)})
        posts.append({
            "id": "ai-" + day,
            "date": day,
            "title": title,
            "excerpt": excerpt(body[0]) if body else "",
            "theme": "ai",
            "cats": ["AI in health research"] + [t.lstrip("#") for t in " ".join(tags).split()],
            "body": body,
            "refs": refs,
            "lang": "en",
            "source": "ai",
        })
    return posts


def main():
    today = date.today()
    posts = philosophy_posts(today) + ai_posts()
    posts.sort(key=lambda p: p["date"], reverse=True)
    OUT.write_text("window.FEEDS = " + json.dumps(posts, ensure_ascii=False) + ";\n", encoding="utf-8")
    print(f"{sum(p['source'] == 'philosophy' for p in posts)} philosophy + {sum(p['source'] == 'ai' for p in posts)} AI posts -> {OUT}")


if __name__ == "__main__":
    main()
