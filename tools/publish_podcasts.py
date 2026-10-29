"""Publish podcast episodes dropped into podcasts/inbox to the WordPress site in .env.

  python tools/publish_podcasts.py            # publish new episodes
  python tools/publish_podcasts.py --dry-run  # only show what would happen

File name: "YYYY-MM-DD Episode title.mp3" (title optional; see podcasts/README.txt).
Each episode becomes a post in "AI in health research" tagged "Podcast", with the audio player at
the top, links to the AI roundups published since the previous episode, and a headphones cover.
The audio (and its optional notes .txt) then moves to podcasts/published.
"""
import html
import re
import shutil
import sys
from datetime import date, timedelta
from pathlib import Path

import make_covers
from wp import WP

ROOT = Path(__file__).resolve().parent.parent
INBOX = ROOT / "podcasts" / "inbox"
DONE = ROOT / "podcasts" / "published"
AUDIO = {".mp3", ".m4a", ".wav", ".ogg"}
NAME = re.compile(r"^(\d{4}-\d{2}-\d{2})\s*[-–:]?\s*(.*)$")
AI_CATEGORY = "ai-in-health-research"
PODCAST_TAG = ("Podcast", "podcast")
NOTE = "Audio created with Google Gemini Notebook (Audio Overview) from the AI in Health Research roundups listed below."


def nice_date(d):
    return d.strftime("%d %B %Y").lstrip("0")


def term_id(wp, kind, name, slug):
    found = wp.get(f"/wp/v2/{kind}", slug=slug, _fields="id")
    return found[0]["id"] if found else wp.post(f"/wp/v2/{kind}", {"name": name, "slug": slug})["id"]


def read_notes(audio):
    txt = audio.with_suffix(".txt")
    if not txt.exists():
        return None, [], None
    lines = [l.strip() for l in txt.read_text(encoding="utf-8-sig").splitlines() if l.strip()]
    links = [l for l in lines[1:] if re.match(r"https?://", l)]
    return (lines[0] if lines else None), links, txt


def episode_html(audio_url, duration, description, roundups, links):
    parts = [f'<figure class="wp-block-audio amk-podcast" data-duration="{html.escape(duration or "")}">'
             f'<audio controls preload="metadata" src="{html.escape(audio_url)}"></audio></figure>',
             f"<p>{html.escape(description)}</p>"]
    if roundups:
        items = "".join(f'<li><a href="{html.escape(r["link"])}">{html.escape(html.unescape(r["title"]["rendered"]))}</a></li>' for r in roundups)
        parts.append(f"<h3>Roundups discussed</h3><ul>{items}</ul>")
    if links:
        parts.append("<h3>Further reading</h3><ul>" + "".join(f'<li><a href="{html.escape(u)}" target="_blank" rel="noopener">{html.escape(u)}</a></li>' for u in links) + "</ul>")
    parts.append(f"<p><em>{NOTE}</em></p>")
    return "\n".join(parts)


def main(dry_run):
    files = sorted(p for p in INBOX.glob("*") if p.suffix.lower() in AUDIO) if INBOX.exists() else []
    if not files:
        print("No new podcast episodes in", INBOX)
        return
    wp = WP()
    ai = wp.get("/wp/v2/categories", slug=AI_CATEGORY, _fields="id")[0]["id"]
    tag = term_id(wp, "tags", *PODCAST_TAG) if not dry_run else None
    published = 0
    for audio in files:
        m = NAME.match(audio.stem)
        if not m:
            print(f"SKIPPED {audio.name}: name must start with the date, e.g. '2026-10-29 Episode title{audio.suffix}'")
            continue
        try:
            day = date.fromisoformat(m.group(1))
        except ValueError:
            print(f"SKIPPED {audio.name}: '{m.group(1)}' is not a real date")
            continue
        slug = f"podcast-{day.isoformat()}"
        if wp.get("/wp/v2/posts", slug=slug, status="publish,future,draft,private", _fields="id"):
            print(f"SKIPPED {audio.name}: an episode for {day} is already published (slug {slug}); rename the file if this is a second episode that day")
            continue

        # Roundups covered: those since the previous episode (or the last 7 days), up to this date.
        prev = wp.get("/wp/v2/posts", tags=tag, before=f"{day.isoformat()}T00:00:00", per_page=1, _fields="date") if tag else []
        start = date.fromisoformat(prev[0]["date"][:10]) if prev else day - timedelta(days=7)
        roundups = [r for r in wp.get_all("/wp/v2/posts", categories=ai, after=f"{start.isoformat()}T23:59:59",
                                          before=f"{day.isoformat()}T23:59:59", _fields="id,link,title,date,tags")
                    if not tag or tag not in r.get("tags", [])]
        roundups.sort(key=lambda r: r["date"])
        span = f"{nice_date(date.fromisoformat(roundups[0]['date'][:10]))} – {nice_date(day)}" if roundups else nice_date(day)

        title = m.group(2).strip() or f"AI in Health Research podcast: {span}"
        description, links, notes = read_notes(audio)
        description = description or f"A conversation about the latest research on AI in medicine, from the roundups of {span}."
        size_mb = audio.stat().st_size / 1e6
        print(f"{'would publish' if dry_run else 'publish'} {day} | {title} | {size_mb:.1f} MB | {len(roundups)} roundups", flush=True)
        if dry_run:
            continue

        try:
            media = wp.upload(audio, title=f"Podcast: {title}")
        except Exception as e:
            print(f"FAILED to upload {audio.name} ({size_mb:.1f} MB): {str(e)[:200]}")
            continue
        duration = (media.get("media_details") or {}).get("length_formatted")
        post = wp.post("/wp/v2/posts", {
            "title": title, "slug": slug, "status": "publish", "date": f"{day.isoformat()}T10:00:00",
            "content": episode_html(media["source_url"], duration, description, roundups, links),
            "excerpt": description, "categories": [ai], "tags": [tag],
        })
        wp.post(f"/wp/v2/media/{media['id']}", {"post": post["id"]})  # attach the audio to its episode
        DONE.mkdir(parents=True, exist_ok=True)
        for f in (audio, notes):
            if f:
                shutil.move(str(f), str(DONE / f.name))
        published += 1
        print(f"published {post['link']} ({duration or 'length unknown'})", flush=True)
    if published:
        make_covers.main(upload=True)


if __name__ == "__main__":
    main("--dry-run" in sys.argv)
