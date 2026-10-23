# abdullahdmc.com redesign

Design mockup for the redesign of https://abdullahdmc.com/ (WordPress, Astra theme).

- `docs/` - static mockup served by GitHub Pages: home, writing (all posts, filter by theme), post page, about/publications.
- Post covers are generated from each post's theme and title (no stock images).
- Post list comes from `docs/assets/posts.js`, built by `tools/build_data.py` from the WordPress REST API. Full post text and the publication list load live from abdullahdmc.com.
