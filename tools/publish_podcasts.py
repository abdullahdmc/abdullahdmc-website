"""Publish new episodes of the Spotify podcast to the WordPress site in .env.

  python tools/publish_podcasts.py            # publish new episodes
  python tools/publish_podcasts.py --dry-run  # only show what would happen

The audio lives on Spotify for Creators. This reads the show's public RSS feed (tools/podcast.json)
and, for each episode not yet on the website, creates a post in "AI in health research" tagged
"Podcast": a player that streams the episode from Spotify's servers (no audio is stored on the
website), a "Listen on Spotify" button, the episode description, links to the AI roundups
published since the previous episode, and a headphones cover.

podcasts/ on this PC is only the author's personal archive; nothing there is uploaded.
"""
import html
import json
import re
import sys
import urllib.request
import xml.etree.ElementTree as ET
from datetime import date, timedelta
from email.utils import parsedate_to_datetime
from pathlib import Path

import make_covers
from wp import WP

CONFIG = Path(__file__).resolve().parent / "podcast.json"
AI_CATEGORY = "ai-in-health-research"
PODCAST_TAG = ("Podcast", "podcast")
ITUNES = "{http://www.itunes.com/dtds/podcast-1.0.dtd}"
NOTE = "Audio created with Google Gemini Notebook (Audio Overview) from the AI in Health Research roundups listed below."


def nice_date(d):
    return d.strftime("%d %B %Y").lstrip("0")


def term_id(wp, kind, name, slug):
    found = wp.get(f"/wp/v2/{kind}", slug=slug, _fields="id")
    return found[0]["id"] if found else wp.post(f"/wp/v2/{kind}", {"name": name, "slug": slug})["id"]


def duration(text):
    """itunes:duration as seconds or h:mm:ss -> "m:ss" / "h:mm:ss"."""
    if not text:
        return ""
    if text.isdigit():
        s = int(text)
        h, rest = divmod(s, 3600)
        m, s = divmod(rest, 60)
        return f"{h}:{m:02d}:{s:02d}" if h else f"{m}:{s:02d}"
    return text.lstrip("0:") or text


def plain(text, limit=600):
    t = re.sub(r"\s+", " ", html.unescape(re.sub(r"<[^>]+>", " ", text or ""))).strip()
    return t if len(t) <= limit else t[:limit].rsplit(" ", 1)[0] + "…"


def episodes(feed_url):
    req = urllib.request.Request(feed_url, headers={"User-Agent": "abdullahdmc-website"})
    root = ET.fromstring(urllib.request.urlopen(req, timeout=60).read())
    out = []
    for item in root.iter("item"):
        enc = item.find("enclosure")
        out.append({
            "title": (item.findtext("title") or "").strip(),
            "date": parsedate_to_datetime(item.findtext("pubDate")).date(),
            "audio": enc.get("url") if enc is not None else "",
            "duration": duration((item.findtext(f"{ITUNES}duration") or "").strip()),
            "link": (item.findtext("link") or "").strip(),
            "description": plain(item.findtext("description") or item.findtext(f"{ITUNES}summary")),
        })
    return sorted(out, key=lambda e: e["date"])


def episode_html(ep, spotify_url, roundups):
    parts = [f'<figure class="wp-block-audio amk-podcast" data-duration="{html.escape(ep["duration"])}">'
             f'<audio controls preload="none" src="{html.escape(ep["audio"])}"></audio></figure>']
    if spotify_url:
        parts.append(f'<p><a class="spotify-btn" href="{html.escape(spotify_url)}" target="_blank" rel="noopener">Listen on Spotify</a></p>')
    if ep["description"]:
        parts.append(f"<p>{html.escape(ep['description'])}</p>")
    if roundups:
        items = "".join(f'<li><a href="{html.escape(r["link"])}">{html.escape(html.unescape(r["title"]["rendered"]))}</a></li>' for r in roundups)
        parts.append(f"<h3>Roundups discussed</h3><ul>{items}</ul>")
    parts.append(f"<p><em>{NOTE}</em></p>")
    return "\n".join(parts)


def main(dry_run):
    cfg = json.loads(CONFIG.read_text(encoding="utf-8")) if CONFIG.exists() else {}
    if not cfg.get("rss"):
        print("Spotify feed not set up yet (add the RSS address to tools/podcast.json).")
        return
    wp = WP()
    ai = wp.get("/wp/v2/categories", slug=AI_CATEGORY, _fields="id")[0]["id"]
    tag = term_id(wp, "tags", *PODCAST_TAG)
    published = 0
    for ep in episodes(cfg["rss"]):
        if not ep["audio"]:
            continue
        slug = f"podcast-{ep['date'].isoformat()}"
        if wp.get("/wp/v2/posts", slug=slug, status="publish,future,draft,private", _fields="id"):
            continue
        prev = wp.get("/wp/v2/posts", tags=tag, before=f"{ep['date'].isoformat()}T00:00:00", per_page=1, _fields="date")
        # Roundups covered: those since the previous episode (or the last 7 days), up to this one.
        start = date.fromisoformat(prev[0]["date"][:10]) if prev else ep["date"] - timedelta(days=7)
        roundups = [r for r in wp.get_all("/wp/v2/posts", categories=ai, after=f"{start.isoformat()}T23:59:59",
                                          before=f"{ep['date'].isoformat()}T23:59:59", _fields="id,link,title,date,tags")
                    if tag not in r.get("tags", [])]
        roundups.sort(key=lambda r: r["date"])
        print(f"{'would publish' if dry_run else 'publish'} {ep['date']} | {ep['title']} | {ep['duration']} | {len(roundups)} roundups", flush=True)
        if dry_run:
            continue
        post = wp.post("/wp/v2/posts", {
            "title": ep["title"], "slug": slug, "status": "publish", "date": f"{ep['date'].isoformat()}T10:00:00",
            "content": episode_html(ep, ep["link"] or cfg.get("show_url"), roundups),
            "excerpt": ep["description"][:300], "categories": [ai], "tags": [tag],
        })
        published += 1
        print(f"published {post['link']}", flush=True)
    if published:
        make_covers.main(upload=True)
    elif not dry_run:
        print("No new Spotify episodes.")


if __name__ == "__main__":
    main("--dry-run" in sys.argv)
