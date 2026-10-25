"""One-off: posts whose featured image is a large PNG cover get the JPEG version instead.
Reads the in-use PNG media ids from dist/cover_cleanup.json; prints the ids that are no longer used."""
import json
import time
from pathlib import Path

from PIL import Image

from wp import WP

ROOT = Path(__file__).resolve().parent.parent
png_ids = set(json.loads((ROOT / "dist" / "cover_cleanup.json").read_text())["png_used"])
wp = WP()
posts = wp.get_all("/wp/v2/posts", status="publish,draft,private", _fields="id,title,featured_media")
freed = []
for p in posts:
    if p["featured_media"] not in png_ids:
        continue
    png = ROOT / "covers" / f"{p['id']}.png"
    jpg = png.with_suffix(".jpg")
    if not jpg.exists():
        Image.open(png).convert("RGB").save(jpg, quality=86, optimize=True, progressive=True)
    title = p["title"]["rendered"]
    for attempt in range(3):
        try:
            media = wp.upload(jpg, title=title, alt=title)
            wp.post(f"/wp/v2/posts/{p['id']}", {"featured_media": media["id"]})
            freed.append(p["featured_media"])
            print("swapped", p["id"], p["featured_media"], "->", media["id"], flush=True)
            break
        except Exception as e:
            print("retry", p["id"], str(e)[:120], flush=True)
            time.sleep(5)
print("no longer used:", freed, flush=True)
