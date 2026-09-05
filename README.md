# ORADENT Dental Care — website

Source for **https://oradentdentalcare.com** — the website of ORADENT Dental Care,
Dr. Nabanita Chaklader BDS (WBUHS), Sonarpur, Kolkata.

Plain PHP 8 + MySQL, no framework and no build step, so Hostinger can deploy it
straight from this repository.

---

## What's in it

| Area | Detail |
|---|---|
| **Pages** | Home, About the doctor, Services index + 8 individual treatment pages, Clinic gallery, FAQ, Contact, Book, Privacy, Terms, 404 |
| **Booking** | 15-minute slots, Mon–Sat 10:00–13:00 and 17:00–21:00, bookable 30 days ahead, **instant confirmation**, database-level protection against double booking |
| **Admin** | Password-protected dashboard: today/tomorrow lists, searchable appointment log, status changes, CSV export, day/slot blocking, patient messages |
| **SEO** | `Dentist` + `Person` + `WebSite` + `BreadcrumbList` + `FAQPage` + `MedicalProcedure` JSON-LD, per-page titles and meta, canonicals, Open Graph, XML sitemap, robots.txt |
| **Performance** | One CSS file, ~4 KB of JS, no web fonts, WebP with JPEG fallback, immutable asset caching, gzip |
| **Mobile** | Mobile-first CSS, sticky Call / WhatsApp / Book bar within thumb reach |

---

## Repository layout

```
├── index.php  about.php  services.php  service.php      Public pages
├── gallery.php  faq.php  contact.php  book.php
├── booking-confirmed.php  privacy.php  terms.php  404.php
├── sitemap.php  robots.txt  site.webmanifest  .htaccess
│
├── config/config.php        All clinic facts + settings (single source of truth)
├── includes/
│   ├── db.php               PDO connection
│   ├── functions.php        Helpers, CSRF, opening hours, slot engine
│   ├── booking.php          Booking validation + persistence (shared)
│   ├── data.php             Service catalogue and FAQ copy
│   ├── seo.php              Page meta + schema.org graph
│   ├── icons.php            Inline SVG icon set
│   └── header.php / footer.php
│
├── api/
│   ├── slots.php            GET  ?date=YYYY-MM-DD → availability JSON
│   └── book.php             POST → creates an appointment
│
├── admin/
│   ├── setup.php            One-time creation of the first staff account
│   ├── login.php  logout.php
│   ├── index.php            Dashboard
│   ├── appointments.php     Log, filters, status changes, CSV export
│   ├── blocks.php           Close a day or a single slot
│   ├── messages.php         Contact-form enquiries
│   └── includes/            auth.php, layout.php
│
├── assets/css  assets/js  assets/img
├── sql/schema.sql           Database schema
└── tools/                   build-images.py, router.php (dev only)
```

---

## Deployment

See **[DEPLOYMENT.md](DEPLOYMENT.md)** for the full Hostinger walkthrough.
Short version:

1. Create a MySQL database in hPanel and import `sql/schema.sql`.
2. Connect this repository under **hPanel → Advanced → Git**, deploying to `public_html`.
3. Create `.env` in `public_html` from `.env.example` with the real database credentials.
4. Visit `/admin/setup.php` once to create the staff account.

---

## Local development

```bash
cp .env.example .env          # point it at a local MySQL
mysql -u root -e "CREATE DATABASE oradent CHARACTER SET utf8mb4"
mysql -u root oradent < sql/schema.sql

php -S localhost:8080 -t . tools/router.php
```

`tools/router.php` reproduces the `.htaccess` rewrites so local URLs match production.

To regenerate the responsive images from new source photos:

```bash
python3 tools/build-images.py /path/to/photos
```

---

## Changing clinic details

Everything about the clinic lives in **`config/config.php`** — name, address,
phone, WhatsApp number, coordinates, opening hours, slot length and booking
window. Change it there and the pages, the schema.org markup, the sitemap and
the booking engine all update together, which is what keeps NAP data consistent
for local SEO.

Opening hours are defined once in `OPENING_HOURS`; the slot engine and the
`openingHoursSpecification` in the structured data both read from it, so they
can never drift apart.

Treatment copy and FAQs live in **`includes/data.php`**.

---

## Notes

- No payment is taken online, so no payment credentials exist anywhere in this codebase.
- Booking data is stored in your own MySQL database; nothing is sent to a third party.
- The only external request a visitor's browser makes is the Google Maps iframe on
  the home and contact pages.
