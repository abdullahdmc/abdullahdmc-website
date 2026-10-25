"""Render a cover image (1600x900 PNG) for every WordPress post that has no featured image yet.

  python tools/make_covers.py            -> covers/<post id>.png for posts on the site in .env
  python tools/make_covers.py --upload   -> also upload each PNG and set it as the post's featured image

Covers use the same design as the mockup (docs/assets/style.css + coverHTML in site.js), rendered with headless Chrome.
"""
import html
import json
import subprocess
import sys
import threading
import time
from concurrent.futures import ThreadPoolExecutor
from functools import partial
from http.server import SimpleHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path

from PIL import Image

from build_data import THEME_ORDER, THEMES
from wp import WP

ROOT = Path(__file__).resolve().parent.parent
OUT = ROOT / "covers"
CHROME = r"C:\Program Files\Google\Chrome\Application\chrome.exe"
PORT = 8766


def theme_of(cats, ai_cat=None):
    if ai_cat and ai_cat in cats:
        return "ai"
    for t in THEME_ORDER:
        if THEMES[t] & set(cats):
            return t
    return "essays"


def render(i, post, tmp):
    png = OUT / f"{post['id']}.png"
    if png.exists():
        return png
    profile = tmp / f"chrome-{i % 4}"
    subprocess.run([CHROME, "--headless=new", "--disable-gpu", "--hide-scrollbars", "--force-device-scale-factor=1",
                    f"--user-data-dir={profile}", "--window-size=1600,900", "--virtual-time-budget=8000",
                    f"--screenshot={png}", f"http://localhost:{PORT}/tools/cover.html?i={i}"],
                   capture_output=True, timeout=120)
    return png


def main(upload):
    wp = WP()
    posts = wp.get_all("/wp/v2/posts", status="publish,draft,private", _fields="id,title,categories,featured_media")
    todo = [p for p in posts if not p["featured_media"]]
    print(f"{len(posts)} posts, {len(todo)} without a featured image")
    ai = wp.get("/wp/v2/categories", slug="ai-in-health-research", _fields="id")
    ai_cat = ai[0]["id"] if ai else None
    data = [{"id": p["id"], "title": html.unescape(p["title"]["rendered"]), "theme": theme_of(p["categories"], ai_cat)} for p in todo]
    (ROOT / "tools" / "covers.json").write_text(json.dumps(data, ensure_ascii=False), encoding="utf-8")
    OUT.mkdir(exist_ok=True)

    class QuietHandler(SimpleHTTPRequestHandler):
        def log_message(self, *args):
            pass

    server = ThreadingHTTPServer(("127.0.0.1", PORT), partial(QuietHandler, directory=str(ROOT)))
    threading.Thread(target=server.serve_forever, daemon=True).start()
    tmp = ROOT / "dist" / "chrome-profiles"
    tmp.mkdir(parents=True, exist_ok=True)
    t0 = time.time()
    with ThreadPoolExecutor(4) as pool:
        pngs = list(pool.map(lambda a: render(a[0], a[1], tmp), enumerate(data)))
    server.shutdown()
    missing = [p for p in pngs if not p.exists()]
    print(f"rendered {len(pngs) - len(missing)} covers in {time.time() - t0:.0f}s; missing: {len(missing)}")

    if upload:
        failed = []
        for post, png in zip(data, pngs):
            if not png.exists():
                continue
            jpg = png.with_suffix(".jpg")
            if not jpg.exists():  # JPEG is ~6x smaller than the PNG screenshot
                Image.open(png).convert("RGB").save(jpg, quality=86, optimize=True, progressive=True)
            for attempt in range(3):
                try:
                    media = wp.upload(jpg, title=post["title"], alt=post["title"])
                    wp.post(f"/wp/v2/posts/{post['id']}", {"featured_media": media["id"]})
                    print("set", post["id"], "->", media["id"], flush=True)
                    break
                except Exception as e:  # network hiccups or server timeouts: retry, then move on
                    print("retry", post["id"], attempt + 1, str(e)[:120], flush=True)
                    time.sleep(5)
            else:
                failed.append(post["id"])
        print("failed:", failed, flush=True)


if __name__ == "__main__":
    main("--upload" in sys.argv)
