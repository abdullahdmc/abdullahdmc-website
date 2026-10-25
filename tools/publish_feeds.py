"""Publish the auto-written posts to the WordPress site in .env, then give new posts a cover image.

  python tools/publish_feeds.py            # publish anything new
  python tools/publish_feeds.py --dry-run  # only list what would be published

- Daily "AI in Healthcare" roundups (abdullahdmc/AI-in-healthcare) -> category "AI in health research".
- Philosophy posts (abdullahdmc/bangla-facebook-post-creator) that the author has reviewed, one week
  after they were written, with their Bangla title -> category দর্শন and tag "স্রষ্টার অস্তিত্বের যুক্তি".

Each post gets a fixed slug, so running this again never creates duplicates.
"""
import html
import re
import sys
from datetime import date

import make_covers
from build_feeds import ai_posts, philosophy_posts
from wp import WP

AI_CATEGORY = ("AI in health research", "ai-in-health-research")
PHILOSOPHY_CATEGORY_ID = 22  # দর্শন
SERIES_TAG = ("স্রষ্টার অস্তিত্বের যুক্তি", "arguments-for-god")
AI_NOTE = "Prepared with AI assistance from newly published research. See the sources for the original studies."


def term_id(wp, kind, name, slug):
    found = wp.get(f"/wp/v2/{kind}", slug=slug, _fields="id")
    return found[0]["id"] if found else wp.post(f"/wp/v2/{kind}", {"name": name, "slug": slug})["id"]


def to_html(post):
    parts = [f"<p>{html.escape(p)}</p>" for p in post["body"]]
    if post.get("refs"):
        items = "".join(f'<li><a href="{html.escape(r["url"])}" target="_blank" rel="noopener">{html.escape(r["title"])}</a></li>' for r in post["refs"])
        parts.append(f"<h3>Sources</h3><ol>{items}</ol>")
    if post["source"] == "ai":
        parts.append(f"<p><em>{AI_NOTE}</em></p>")
    return "\n".join(parts)


def slug_for(post):
    if post["source"] == "ai":
        return "ai-health-research-roundup-" + post["date"]
    return re.sub(r"^ph-\d{4}-\d{2}-\d{2}-", "", post["id"])


def main(dry_run):
    wp = WP()
    feed = philosophy_posts(date.today()) + ai_posts()
    ai_cat = term_id(wp, "categories", *AI_CATEGORY) if not dry_run else None
    series = term_id(wp, "tags", *SERIES_TAG) if not dry_run else None
    created = 0
    for post in sorted(feed, key=lambda p: p["date"]):
        slug = slug_for(post)
        if wp.get("/wp/v2/posts", slug=slug, status="publish,future,draft,private", _fields="id"):
            continue
        print(("would publish" if dry_run else "publish"), post["date"], slug, "|", post["title"][:60], flush=True)
        if dry_run:
            continue
        data = {
            "title": post["title"], "slug": slug, "status": "publish", "date": post["date"] + "T09:00:00",
            "content": to_html(post), "excerpt": post["excerpt"],
        }
        if post["source"] == "ai":
            data["categories"] = [ai_cat]
        else:
            data.update(categories=[PHILOSOPHY_CATEGORY_ID], tags=[series])
        wp.post("/wp/v2/posts", data)
        created += 1
    print(f"{created} new posts", flush=True)
    if created:
        make_covers.main(upload=True)


if __name__ == "__main__":
    main("--dry-run" in sys.argv)
