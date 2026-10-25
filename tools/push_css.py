"""Store wp-theme/extra.css in the amk_css option of the WordPress site in .env (no theme upload needed)."""
from pathlib import Path

from wp import WP

css = (Path(__file__).resolve().parent.parent / "wp-theme" / "extra.css").read_text(encoding="utf-8")
saved = WP().post("/wp/v2/settings", {"amk_css": css})["amk_css"]
print(f"amk_css: {len(saved)} characters saved")
