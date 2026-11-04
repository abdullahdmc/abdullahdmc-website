"""Zip the WordPress child theme for upload.

  python tools/build_theme.py   ->  dist/abdullahdmc-theme.zip

The theme source is wp-theme/abdullahdmc (design 2.x). Every .css/.js in assets/ is also shipped
under a versioned name (base-2.0.1.css ...), which the theme loads: Namecheap's Supersonic CDN
ignores "?ver=", so a new file name is what makes visitors get the new file after an update.
Upload the zip in wp-admin: Appearance -> Themes -> Add New -> Upload Theme.
Older theme versions are kept in archive/.
"""
import re
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
THEME = ROOT / "wp-theme" / "abdullahdmc"
DIST = ROOT / "dist"


def main():
    version = re.search(r"Version:\s*([\d.]+)", (THEME / "style.css").read_text(encoding="utf-8")).group(1)
    php = (THEME / "functions.php").read_text(encoding="utf-8")
    (THEME / "functions.php").write_text(re.sub(r"define\( 'AMK_VERSION', '[^']*' \);", f"define( 'AMK_VERSION', '{version}' );", php), encoding="utf-8")
    DIST.mkdir(exist_ok=True)
    out = DIST / "abdullahdmc-theme.zip"
    with zipfile.ZipFile(out, "w", zipfile.ZIP_DEFLATED) as z:
        for f in sorted(THEME.rglob("*")):
            if not f.is_file():
                continue
            z.write(f, Path("abdullahdmc") / f.relative_to(THEME))
            if f.parent == THEME / "assets" and f.suffix in (".css", ".js"):
                z.write(f, Path("abdullahdmc") / "assets" / f"{f.stem}-{version}{f.suffix}")
    print(f"{out} ({out.stat().st_size // 1024} KB), version {version}")


if __name__ == "__main__":
    main()
