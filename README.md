# Rotary School Uran — Website Rebuild

**Phase 1 of 4 — Frontend Only** (HTML5 / CSS3 / vanilla JavaScript, no frameworks)

This phase delivers a complete, responsive, static frontend for the new Rotary School Uran
website. No backend, database, or admin panel is included yet — see "What's next" below.

---

## 1. What's inside

```
rotary/
├── index.html              Home
├── about.html               About
├── academics.html            Academics
├── admissions.html           Admissions (with enquiry form)
├── facilities.html           Facilities & Technology
├── labs-library.html         Labs & Library
├── directors.html            Directors / Board / President
├── gallery.html               Filterable photo gallery + lightbox
├── events.html                 Activities & Events
├── news.html                    News
├── login.html                   Login / Register (parent & student portal preview)
├── contact.html                Contact (form + map)
├── donation.html                Donation (amount selector)
├── enquiry.html                  General enquiry form
├── assets/
│   ├── css/style.css     All design tokens, layout, components, animations
│   ├── js/main.js           Nav, mobile menu, reveal animations, counters,
│   │                            FAQ accordion, gallery filter/lightbox,
│   │                            testimonial slider, donation selector, login/register tabs
│   ├── images/
│   │   ├── logo.jpg              School logo (uploaded by you)
│   │   ├── directors/            Board photos (7 real + Rtn. Subhash Deshpande, uploaded by you)
│   │   ├── courses, hero, labs, testimonials, results, events/
│   │   │                            EMPTY until you run build/download_images.py — see below
│   │   └── READ_ME_FIRST.txt
│   └── docs/
│       ├── admission-form.pdf              Placeholder downloadable admission form
│       └── RULES AND REGULATIONS.pdf       Add your real file here (see §3)
└── build/                   Python scripts used to generate the HTML, plus
                                       download_images.py (dev-only, not needed to
                                       view the site, but download_images.py IS
                                       needed once — see §3 below)
```

## 2. How to view it

No install or build step is required — it's plain static HTML.

1. Unzip the folder.
2. Double-click `index.html` to open it in a browser, **or**
3. For the most accurate experience (some browsers restrict local file access),
   serve it with any static server, e.g.:
   ```bash
   cd rotary
   python3 -m http.server 8080
   # then visit http://localhost:8080
   ```

To publish it, upload the whole `rotary/` folder (except `build/`) to any static
web host or to the `public_html` folder of your existing hosting plan.

## 3. Latest updates (this revision)

- **⚠️ Action required — download the site's photos.** Every page used to
  hotlink images straight from the old live site. That's now fixed in the
  code — every `<img>` tag points at a local path like
  `assets/images/events/annualday-1.jpg` — but the build environment that
  generated this site **cannot reach rotaryschooluran.com** (it's outside its
  network sandbox), so it couldn't download the actual files for you. **Run
  this once, from the project root:**
  ```bash
  cd rotary
  python3 build/download_images.py
  ```
  It has no dependencies beyond Python itself, fetches every photo from the
  old site, and saves each one at the exact local path the HTML already
  expects. Takes a few seconds. Until you run it, the image boxes on the
  site will appear empty. The full list of source URLs &rarr; local paths
  lives in `build/generate.py` (`IMG_REMOTE` / `IMG_LOCAL`) if you'd rather
  fetch a few manually.
- **Removed the blank gap below the navbar** on every inner page (About,
  Academics, Admissions, Facilities, Labs &amp; Library, Directors, Gallery,
  Events, News, Contact, Donation, Enquiry, Login) — same root cause as the
  Home page fix from before: the hero band was adding its own top padding on
  top of space the sticky navbar already reserves.
- **Marquee now shows real toppers**: top 3 SSC and top 3 HSC results by
  percentage, pulled from the topper tables you shared (Mst. Shravan S. Bane
  90.92% SSC 2007&ndash;08, down to Miss. Gunal Jaywant Dungikar 85.53% HSC
  2018&ndash;19, etc).
- **Added Rtn. Subhash Deshpande** to the Directors page as an 8th board
  member (Trustee), using the portrait you uploaded, with an "in loving
  memory" note and his dates (30/12/1938 &ndash; 11/12/2019).

- **Gallery is now complete** — every single photo from your live site's
  Activities &amp; Events archive (`aph.html`) is now in the Gallery page:
  all 10 categories, 74 photos total (Student's Excellence Performance,
  Annual Day Gathering, Sports Meet &amp; Yoga Day, Science Exhibition,
  Shivaji Jayanti, Dahi Kala, Tree Plantation, Republic Day, Children's Day,
  and Farewell Day), plus the 8 Labs &amp; Library photos — 82 images in
  total, all filterable by category. Nothing was left out this time.
  `build/download_images.py` now fetches all 100 images the site uses
  (run it if you haven't already — see the section below).

## 4. Public "Recent Updates" cards

- **Nav renamed**: "Events / News" is now just **"Updates"** in the navbar
  (still a dropdown to Events and News underneath).
- **Home page** and **News page** now each show a public **Recent Updates**
  card — same visual style as the admin one, but simplified for visitors: no
  admin name or Draft/Updated badges, just published items (icon, title,
  short description, thumbnail, and a relative date like "2 days ago"),
  each linking through to the relevant page. Home shows the latest 6, News
  shows the latest 8.
- This runs on its own small mock dataset in `assets/js/public-updates.js`
  (separate from the admin one, since the admin feed can show drafts and
  this one never should). Once the backend exists, both should read from the
  same `admin_updates` table — the admin feed with `?status=all`, the public
  one with `?status=published` — which is exactly what makes "publish once,
  it shows up everywhere" work without manual edits.

## 5. Admin Dashboard preview

Two new pages, not linked from the public site's nav (find them at
`admin-dashboard.html` and `admin-updates.html`):

- **`admin-dashboard.html`** — an admin shell (sidebar + topbar + stat cards)
  built around the **Recent Updates card** you asked for: icon per update
  type (Gallery/News/Notice/Event/Homepage/Announcement/Banner), title,
  1&ndash;2 line description, thumbnail when available, relative
  time + full timestamp on hover, admin name & avatar, and a
  Published/Draft/Updated status badge. Shows the latest 10, newest first,
  with a "No recent updates available" empty state, and a **View All
  Updates** button.
- **`admin-updates.html`** — the full history behind that button, with a
  type filter bar.
- Try the **"Demo controls"** panel at the bottom of the dashboard — each
  button simulates a real admin action (e.g. "Simulate gallery upload") and
  the new entry appears instantly at the top of Recent Updates with a gold
  highlight, exactly how it'll behave once wired to the real backend.

**Important — this is a Phase 1 UI preview, not live yet.** Everything in the
feed currently comes from mock data in `assets/js/admin.js` (see the big
comment at the top of that file), because there's no database to read from
yet — that's Phase 2 (PHP backend) and Phase 3 (MySQL) work, which we agreed
to build after this frontend phase. The component is built so that plugging
in the real thing later is small: swap `fetchRecentUpdates()` for a real
`fetch('/api/admin/recent_updates.php?limit=10')` call, and call
`renderRecentUpdates()` inside each admin form's save-success handler. The
same applies to **"the public site should auto-display new content"** — the
public pages (Gallery, News, Events, etc.) are currently static HTML, so true
auto-publishing needs those Phase 2/3 pieces in place too; happy to start on
that whenever you're ready to move past Phase 1.

## 6. Design notes

- **Palette:** deep indigo/navy + Rotary gold, with a teal accent for
  donation/growth moments — drawn from the school's own emblem.
- **Type:** Fraunces (headings) + Plus Jakarta Sans (body/UI), loaded from Google Fonts.
- **Signature motif:** a gear/compass badge echoing the Rotary wheel on the logo,
  used in the preloader, hero, and floating badges.
- **Navigation** groups the many pages from the old site into a cleaner structure:
  *Facilities* (Facilities & Technology + Labs & Library) and *Media*
  (Gallery + Events + News) are dropdowns; every page from your original site is
  still one click away.
- Fully responsive: desktop, laptop, tablet, mobile, and landscape.
- Includes: sticky navbar, animated hamburger + dark overlay mobile menu, smooth
  scroll, back-to-top, preloader, scroll-reveal animations, animated counters,
  timeline, testimonial slider, FAQ accordions, filterable gallery with lightbox,
  newsletter block, and a donation amount selector.

## 7. About the placeholder images

Per your brief, the images currently used are **hotlinked directly from your live
site** (`rotaryschouluran.com/...`) as temporary placeholders, so nothing had to be
guessed or invented. Before this goes live, please:

1. Replace these `<img src="https://www.rotaryschooluran.com/...">` references with
   your own final photography, saved locally into `assets/images/`, **or**
2. Confirm you're happy keeping them hotlinked (not recommended long-term — if the
   old site ever goes offline or changes hosting, these images will break here too).

The school logo you uploaded is already saved locally at `assets/images/logo.jpg`
and used throughout the header, footer, and favicon.

## 8. Forms right now

All forms (admission enquiry, contact, donation, newsletter) are wired to a small
JS demo handler that shows a confirmation message — nothing is actually sent or
saved yet. Real submission, storage in MySQL, and admin visibility arrive in
Phases 2–3.

