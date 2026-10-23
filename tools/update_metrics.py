"""Fetch the public Google Scholar profile and write docs/assets/metrics.json.

Run monthly. If Scholar refuses (captcha / non-200), the old metrics.json is kept and the script exits 1.
ResearchGate blocks automated access, so its numbers come from tools/researchgate.json (edit by hand).
"""
import html
import json
import re
import sys
import time
import urllib.request
from datetime import date
from pathlib import Path

SCHOLAR_USER = "dj7mFi8AAAAJ"
HERE = Path(__file__).parent
OUT = HERE.parent / "docs" / "assets" / "metrics.json"
UA = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36"


def get(url):
    req = urllib.request.Request(url, headers={"User-Agent": UA, "Accept-Language": "en"})
    with urllib.request.urlopen(req, timeout=40) as r:
        return r.read().decode("utf-8", "replace")


def text(s):
    return html.unescape(re.sub(r"<[^>]+>", "", s)).strip()


def main():
    base = f"https://scholar.google.com/citations?user={SCHOLAR_USER}&hl=en&sortby=pubdate&pagesize=100"
    papers, first = [], None
    for start in range(0, 1000, 100):
        page = get(f"{base}&cstart={start}")
        if "gsc_rsb_std" not in page and start == 0:
            sys.exit("Scholar did not return the profile (possibly a captcha). metrics.json left unchanged.")
        if first is None:
            first = page
        rows = re.findall(r'<tr class="gsc_a_tr">(.*?)</tr>', page, re.S)
        for r in rows:
            a = re.search(r'href="([^"]+)" class="gsc_a_at">(.*?)</a>', r)
            grays = re.findall(r'<div class="gs_gray">(.*?)</div>', r)
            c = re.search(r'class="gsc_a_ac gs_ibl">(\d*)', r)
            y = re.search(r'gsc_a_h gsc_a_hc gs_ibl">(\d*)', r)
            papers.append({
                "title": text(a.group(2)),
                "authors": text(grays[0]) if grays else "",
                "venue": text(grays[1]) if len(grays) > 1 else "",
                "cites": int(c.group(1)) if c and c.group(1) else 0,
                "year": int(y.group(1)) if y and y.group(1) else None,
                "scholar": "https://scholar.google.com" + html.unescape(a.group(1)),
            })
        if len(rows) < 100:
            break
        time.sleep(4)

    std = [int(x) for x in re.findall(r'gsc_rsb_std">(\d+)', first)]
    years = re.findall(r'gsc_g_t"[^>]*>(\d+)', first)
    counts = re.findall(r'gsc_g_al">(\d+)', first)
    since = re.search(r"Since (\d{4})", first)
    rg_file = HERE / "researchgate.json"
    data = {
        "updated": date.today().isoformat(),
        "scholar": {
            "url": f"https://scholar.google.com/citations?user={SCHOLAR_USER}",
            "citations": std[0], "citations_recent": std[1],
            "h_index": std[2], "h_index_recent": std[3],
            "i10_index": std[4], "i10_index_recent": std[5],
            "since": int(since.group(1)) if since else None,
            "per_year": dict(zip(years[-len(counts):], map(int, counts))),
        },
        "researchgate": json.loads(rg_file.read_text(encoding="utf-8")) if rg_file.exists() else None,
        "papers": papers,
    }
    OUT.write_text(json.dumps(data, ensure_ascii=False, indent=1), encoding="utf-8")
    print(f"Scholar: {std[0]} citations, h={std[2]}, i10={std[4]}, {len(papers)} papers -> {OUT}")


if __name__ == "__main__":
    main()
