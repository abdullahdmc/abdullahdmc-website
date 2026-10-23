# abdullahdmc.com redesign

Design mockup for the redesign of https://abdullahdmc.com/ (WordPress, Astra theme), served by GitHub Pages from `docs/`.

## Pages
- `index.html`: home (latest writing, daily AI roundup strip, philosophy series, research highlights, profiles and YouTube).
- `writing.html`: all posts; filter by theme (`?theme=`) or tag (`?tag=`).
- `post.html?id=`: a post. WordPress posts load their full text live from abdullahdmc.com; auto-published posts carry their own text.
- `research.html`: Google Scholar metrics, current projects, funding, publications (first author / co-author), editorial and reviewer roles.
- `about.html`: biography, affiliations, qualifications, awards, memberships, experience, training and conferences.

## Data (docs/assets)
| File | Built by | Source |
|---|---|---|
| `posts.js` | `tools/build_data.py` | WordPress REST export (`tools/posts*.json`) |
| `feeds.js` | `tools/build_feeds.py` | GitHub repos `bangla-facebook-post-creator` (author-reviewed posts, live one week after writing) and `AI-in-healthcare` (daily roundup) |
| `metrics.json` | `tools/update_metrics.py` | Public Google Scholar profile; ResearchGate figures from `tools/researchgate.json` (entered by hand, RG blocks automated access) |
| `research.js` | `tools/build_research.py` | `tools/publications.json` (from the CV via `tools/cv_to_publications.py`) + `metrics.json` |
| `profile.js` | hand-edited | CV, October 2026 |

A local scheduled task runs the metrics, research and feed builds on the 3rd of each month and pushes the result.
