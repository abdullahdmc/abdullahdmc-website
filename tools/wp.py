"""Small WordPress REST client for the redesign. Reads WP_URL / WP_USER / WP_APP_PASSWORD from ../.env.

Usage from other scripts:  from wp import WP;  wp = WP();  wp.get("/wp/v2/posts", per_page=5)
CLI (GET only):            python tools/wp.py /wp/v2/settings
"""
import base64
import json
import mimetypes
import sys
import urllib.error
import urllib.parse
import urllib.request
from pathlib import Path

ENV = Path(__file__).resolve().parent.parent / ".env"


def load_env():
    env = {}
    for line in ENV.read_text(encoding="utf-8-sig").splitlines():
        line = line.strip()
        if line and not line.startswith("#") and "=" in line:
            k, v = line.split("=", 1)
            env[k.strip()] = v.strip().strip('"').strip("'")
    return env


class WP:
    def __init__(self):
        env = load_env()
        self.base = env["WP_URL"].rstrip("/") + "/wp-json"
        token = base64.b64encode(f"{env['WP_USER']}:{env['WP_APP_PASSWORD']}".encode()).decode()
        self.headers = {"Authorization": "Basic " + token, "User-Agent": "abdullahdmc-redesign"}

    def _req(self, method, path, params=None, data=None, headers=None, raw=None):
        url = self.base + path + ("?" + urllib.parse.urlencode(params, doseq=True) if params else "")
        body = raw if raw is not None else (json.dumps(data).encode() if data is not None else None)
        h = dict(self.headers, **(headers or {}))
        if data is not None:
            h["Content-Type"] = "application/json"
        req = urllib.request.Request(url, data=body, method=method, headers=h)
        try:
            with urllib.request.urlopen(req, timeout=120) as r:
                text = r.read().decode("utf-8", "replace")
                return json.loads(text) if text else None, dict(r.headers)
        except urllib.error.HTTPError as e:
            raise RuntimeError(f"{method} {path} -> {e.code}: {e.read().decode('utf-8', 'replace')[:400]}") from None

    def get(self, path, **params):
        return self._req("GET", path, params)[0]

    def get_all(self, path, **params):
        out, page = [], 1
        while True:
            data, h = self._req("GET", path, dict(params, per_page=100, page=page))
            out += data
            if page >= int(h.get("X-WP-TotalPages") or h.get("x-wp-totalpages") or 1):
                return out
            page += 1

    def post(self, path, data):
        return self._req("POST", path, data=data)[0]

    def upload(self, file_path, title=None, alt=""):
        p = Path(file_path)
        ctype = mimetypes.guess_type(p.name)[0] or "application/octet-stream"
        media = self._req("POST", "/wp/v2/media", raw=p.read_bytes(),
                          headers={"Content-Type": ctype, "Content-Disposition": f'attachment; filename="{p.name}"'})[0]
        if title or alt:
            media = self.post(f"/wp/v2/media/{media['id']}", {"title": title or p.stem, "alt_text": alt})
        return media


if __name__ == "__main__":
    print(json.dumps(WP().get(sys.argv[1]), ensure_ascii=False, indent=1)[:4000])
