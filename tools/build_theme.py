"""Copy the shared mockup assets into the WordPress child theme and zip it for upload.

  python tools/build_theme.py   ->  dist/abdullahdmc-theme.zip

site.css, site.js and portrait.jpg come from docs/assets so the mockup and the live theme stay identical.
Upload the zip in wp-admin: Appearance -> Themes -> Add New -> Upload Theme.
"""
import re
import shutil
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
THEME = ROOT / "wp-theme" / "abdullahdmc"
DIST = ROOT / "dist"

SHARED = {"style.css": "site.css", "site.js": "site.js", "portrait.jpg": "portrait.jpg"}


def main():
    for src, dst in SHARED.items():
        shutil.copyfile(ROOT / "docs" / "assets" / src, THEME / "assets" / dst)
    version = re.search(r"Version:\s*([\d.]+)", (THEME / "style.css").read_text(encoding="utf-8")).group(1)
    php = (THEME / "functions.php").read_text(encoding="utf-8")
    (THEME / "functions.php").write_text(re.sub(r"define\( 'AMK_VERSION', '[^']*' \);", f"define( 'AMK_VERSION', '{version}' );", php), encoding="utf-8")
    DIST.mkdir(exist_ok=True)
    out = DIST / "abdullahdmc-theme.zip"
    with zipfile.ZipFile(out, "w", zipfile.ZIP_DEFLATED) as z:
        for f in sorted(THEME.rglob("*")):
            if f.is_file():
                z.write(f, Path("abdullahdmc") / f.relative_to(THEME))
                # Also ship css/js as e.g. assets/site-1.0.9.css: the CDN ignores "?ver=", the theme loads these.
                if f.parent.name == "assets" and f.suffix in (".css", ".js"):
                    z.write(f, Path("abdullahdmc") / "assets" / f"{f.stem}-{version}{f.suffix}")
    print(f"{out} ({out.stat().st_size // 1024} KB), version {version}")


if __name__ == "__main__":
    main()
