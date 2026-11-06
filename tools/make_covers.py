"""Render a cover image (1600x900) for WordPress posts and set it as the featured image.

  python tools/make_covers.py                  -> covers/<post id>-v2.png for posts without a featured image
  python tools/make_covers.py --upload         -> also upload each cover and set it as the post's featured image
  python tools/make_covers.py --all --upload   -> redo every post whose cover is not yet in the current style (safe to re-run)
  python tools/make_covers.py --all --dry      -> only write tools/covers.json; view tools/cover.html?sheet=1
  python tools/make_covers.py --ids=12,34 --upload -> make the covers of these posts again
  python tools/make_covers.py --motifs         -> store every post's drawing name in its amk_motif meta (theme 2.0.3+)

Design (tools/cover.html): the theme's forest green, gold and fonts, the post's topic and title, and one
line drawing (tools/cover_icons.js) that matches the post. The drawing comes from tools/cover_motifs.json
(chosen by hand per post), else from keywords in the title and text (RULES), else from the topic.
--all keeps the old images in the media library and lists their ids in dist/old_cover_ids.txt.
The drawing's name is also stored in the post meta amk_motif: the home page's series cards show it.
"""
import html
import json
import re
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
STYLE = "v2"  # change when the design changes, so covers are rendered again
CHROME = r"C:\Program Files\Google\Chrome\Application\chrome.exe"
PORT = 8766

LABELS = {"ai": "AI in Health Research", "podcast": "Podcast", "health": "চিকিৎসা ও গবেষণা", "mind": "মন ও মস্তিষ্ক",
          "science": "বিজ্ঞান ও বিবর্তন", "faith": "বিশ্বাস ও দর্শন", "literature": "সাহিত্য ও রিভিউ", "essays": "ভাবনা ও প্রবন্ধ"}
DEFAULT_MOTIF = {"ai": "chip", "podcast": "headphones", "health": "stethoscope", "mind": "brain", "science": "atom",
                 "faith": "star", "literature": "quill", "essays": "bulb"}
# Keyword -> drawing, for posts without a hand-picked one (daily AI roundups, new philosophy posts).
# The drawing whose words occur most often wins; words in the title count three times. Nearly every
# roundup mentions language models, so those words count a third: a clinical subject wins when there is one.
RULES = [
    ("lungs", r"lung|pulmonar|respirat|asthma|copd|হাঁপানি|ফুসফুস"),
    ("heartbeat", r"cardi|heart|\becg\b|হৃদ"),
    ("brain", r"brain|neuro|stroke|dementia|alzheimer|mental health|psychiatr|depress|মস্তিষ্ক|ব্রেইন|মাইন্ড"),
    ("cell", r"cancer|oncolog|tumou?r|patholog|ক্যান্সার|কোষ"),
    ("helix", r"genom|genetic|\bdna\b|\brna\b|জিন|ডিএনএ"),
    ("eye", r"retina|ophthalm|\beyes?\b|চোখ"),
    ("scan", r"imaging|radiolog|x-ray|\bct\b|\bmri\b|ultrasound|mammogra"),
    ("speech", r"language model|\bllms?\b|chatgpt|\bgpt|chatbot|clinical notes|ambient"),
    ("pill", r"\bdrugs?\b|pharma|medication|prescri|ওষুধ"),
    ("virus", r"virus|infection|sepsis|antimicrob|covid|ভাইরাস"),
    ("shield", r"privacy|cybersecurity|regulat|governance"),
    ("scales", r"\bbias|fairness|equity|ethic"),
    ("chart", r"meta-analys|statistic|regression|randomi[sz]ed|forecast"),
    ("tree", r"বিবর্তন|evolution"),
    ("chain", r"নিমিত্ত|কারণ"),
    ("hourglass", r"সময়|সম্ভাব্যতা"),
    ("cycle", r"পরিবর্তন"),
    ("compass", r"নকশা"),
    ("steps", r"পূর্ণতা"),
    ("cosmos", r"মহাবিশ্ব|cosmolog"),
]


def theme_of(cats, ai_cat=None):
    if ai_cat and ai_cat in cats:
        return "ai"
    for t in THEME_ORDER:
        if THEMES[t] & set(cats):
            return t
    return "essays"


def motif_of(post, theme, chosen):
    if str(post["id"]) in chosen:
        return chosen[str(post["id"])]
    if theme == "podcast":
        return "headphones"
    title = html.unescape(post["title"]["rendered"])
    text = html.unescape(re.sub(r"<[^>]+>", " ", post.get("content", {}).get("rendered", "")))
    scores = {m: (3 * len(re.findall(rx, title, re.I)) + len(re.findall(rx, text, re.I))) / (3 if m == "speech" else 1)
              for m, rx in RULES}
    best = max(scores, key=scores.get)
    return best if scores[best] >= 2 else DEFAULT_MOTIF[theme]


def blank(png):
    """True if the screenshot is all black: Chrome took it before the cover had loaded."""
    return Image.open(png).convert("L").getextrema()[1] < 10


def render(i, post, tmp):
    png = OUT / f"{post['id']}-{STYLE}.png"
    if png.exists() and not blank(png):
        return png
    profile = tmp / f"chrome-{i % 4}"
    for budget in (8000, 20000, 40000):  # a slow start leaves a black screenshot: try again with more time
        png.unlink(missing_ok=True)
        try:
            subprocess.run([CHROME, "--headless=new", "--disable-gpu", "--hide-scrollbars", "--force-device-scale-factor=1",
                            f"--user-data-dir={profile}", "--window-size=1600,900", f"--virtual-time-budget={budget}",
                            f"--screenshot={png}", f"http://localhost:{PORT}/tools/cover.html?i={i}"],
                           capture_output=True, timeout=120)
        except subprocess.TimeoutExpired:
            continue
        if png.exists() and not blank(png):
            return png
    png.unlink(missing_ok=True)  # never upload a blank cover; the next run tries again
    return png


def set_motif(wp, post_id, motif):
    try:  # the theme registers amk_motif from 2.0.3; with an older theme this is skipped
        wp.post(f"/wp/v2/posts/{post_id}", {"meta": {"amk_motif": motif}})
    except Exception as e:
        print("motif not stored for", post_id, str(e)[:120], flush=True)


def main(upload, redo_all=False, dry=False, motifs_only=False, ids=()):
    wp = WP()
    posts = wp.get_all("/wp/v2/posts", status="publish,draft,private", _fields="id,title,content,categories,tags,featured_media,meta")
    if redo_all:  # every post whose featured image is not yet in the current style
        styled = {m["id"] for m in wp.get_all("/wp/v2/media", _fields="id,source_url") if f"-{STYLE}." in m["source_url"]}
        todo = [p for p in posts if p["featured_media"] not in styled]
    elif ids:
        todo = [p for p in posts if p["id"] in ids]
        for p in todo:  # render again, not from the cache
            for f in OUT.glob(f"{p['id']}-{STYLE}.*"):
                f.unlink()
    else:
        todo = [p for p in posts if not p["featured_media"]]
    print(f"{len(posts)} posts, {len(todo)} to do")
    ai = wp.get("/wp/v2/categories", slug="ai-in-health-research", _fields="id")
    ai_cat = ai[0]["id"] if ai else None
    pod = wp.get("/wp/v2/tags", slug="podcast", _fields="id")
    pod_tag = pod[0]["id"] if pod else None
    chosen = json.loads((ROOT / "tools" / "cover_motifs.json").read_text(encoding="utf-8"))
    data = []
    for p in todo:
        theme = "podcast" if pod_tag in p.get("tags", []) else theme_of(p["categories"], ai_cat)
        data.append({"id": p["id"], "title": html.unescape(p["title"]["rendered"]), "label": LABELS[theme],
                     "motif": motif_of(p, theme, chosen), "old": p["featured_media"]})
    if motifs_only:  # every post, not only those whose cover is out of date
        data = []
        for p in posts:
            theme = "podcast" if pod_tag in p.get("tags", []) else theme_of(p["categories"], ai_cat)
            m = motif_of(p, theme, chosen)
            if (p.get("meta") or {}).get("amk_motif") != m:
                data.append((p["id"], m))
        print(len(data), "posts to update")
        for pid, m in data:
            set_motif(wp, pid, m)
        return
    (ROOT / "tools" / "covers.json").write_text(json.dumps(data, ensure_ascii=False), encoding="utf-8")
    if dry:
        print("wrote tools/covers.json")
        return
    OUT.mkdir(exist_ok=True)

    class QuietHandler(SimpleHTTPRequestHandler):
        def log_message(self, *args):
            pass

        def end_headers(self):  # the Chrome profiles are reused: never let them keep an old cover.html
            self.send_header("Cache-Control", "no-store")
            super().end_headers()

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
        failed, old_ids = [], []
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
                    set_motif(wp, post["id"], post["motif"])
                    if post["old"]:
                        old_ids.append(post["old"])
                    break
                except Exception as e:  # network hiccups or server timeouts: retry, then move on
                    print("retry", post["id"], attempt + 1, str(e)[:120], flush=True)
                    time.sleep(5)
            else:
                failed.append(post["id"])
        if old_ids:
            with open(ROOT / "dist" / "old_cover_ids.txt", "a", encoding="utf-8") as f:
                f.write("".join(f"{i}\n" for i in old_ids))
            print(f"{len(old_ids)} replaced images listed in dist/old_cover_ids.txt (still in the media library)")
        print("failed:", failed, flush=True)


if __name__ == "__main__":
    ids = [int(x) for a in sys.argv if a.startswith("--ids=") for x in a[6:].split(",")]
    main("--upload" in sys.argv, "--all" in sys.argv, "--dry" in sys.argv, "--motifs" in sys.argv, ids)
