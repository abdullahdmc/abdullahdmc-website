# Go-live checklist: abdullahdmc.com

The redesign is built and checked on staging (`https://abdullahdmc.com/staging`). Going live repeats the same scripted steps on the live site, rather than copying staging over it. That way live posts, comments and media stay as they are, and nothing from staging (test images, the "discourage search engines" setting) comes along.

## Before (you)
- [ ] **Back up the live site**: UpdraftPlus → **Backup Now** (database + files), or cPanel → Backup Wizard.
- [ ] **Create an Application Password on the live site**: Users → Profile → Application Passwords → name it `Claude`.
- [ ] **Point the tools at the live site**: in `.env`, set `WP_URL=https://abdullahdmc.com` and paste the **live** application password. Keep the staging values in a note if you want to keep using staging.

## Theme (you)
- [ ] Appearance → Themes → Add New Theme → Upload Theme → `dist/abdullahdmc-theme.zip` → **Install Now** → **Activate**.
- [ ] If the site shows an error: cPanel → File Manager → `public_html/wp-content/themes/` → rename the `abdullahdmc` folder. WordPress falls back to Astra straight away.

## Content and settings (Claude)
- [ ] `python tools/setup_wp.py`: creates Writing / Research / Biography / Consultation, sets Home as the front page and Writing as the posts page, loads profile and research data.
- [ ] Site title `Md Abdullah Saeed Khan`, 12 posts per page.
- [ ] `python tools/make_covers.py --upload`: cover images for every post (about 30 seconds each on this server, run in the background).
- [ ] `python tools/publish_feeds.py`: AI roundups and reviewed philosophy posts.
- [ ] Install MailPoet.

## Settings in wp-admin (you)
- [ ] **Yoast SEO → Settings → Site basics**: Website name `Md Abdullah Saeed Khan`.
- [ ] **Pages → Home → Yoast SEO box**: SEO title `Md Abdullah Saeed Khan` (or empty).
- [ ] **Settings → Reading**: make sure **"Discourage search engines from indexing this site" is NOT ticked**.
- [ ] **MailPoet**: finish the setup wizard (sender name and email), create a sign-up form, then tell Claude the form number for the home-page box.
- [ ] **LiteSpeed Cache**: keep it active; purge all after the switch.

## Checks (Claude, with screenshots on laptop and phone)
- [ ] Home, Writing (filters, search, page numbers), a Bangla post, an AI roundup, Research, Biography, Consultation.
- [ ] Old addresses still work (posts keep their links; old Publications and Short biography pages still open).
- [ ] Browser-tab titles and Yoast data show "Md Abdullah Saeed Khan".
- [ ] No errors in the browser console; nothing wider than a phone screen.

## After
- [ ] Google Search Console: resubmit the sitemap (`/sitemap_index.xml` from Yoast).
- [ ] Daily and monthly tasks now use the live site (they follow `.env`).
- [ ] Optional: remove unused plugins (Elementor and add-ons, ElementsKit, Social Warfare, WP Headers And Footers) once you're sure you don't need them.
- [ ] Optional: delete the staging copy (Softaculous → Installations → staging → Remove) when you no longer need it.
