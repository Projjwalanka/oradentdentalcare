# Deploying to Hostinger

One-time setup, about 20 minutes. You need your hPanel login and your GitHub account.

---

## 1. Create the database

1. hPanel → **Databases → MySQL Databases**.
2. Create a database and a user, e.g. name `oradent`, user `oradent`.
   Hostinger prefixes both with your account id, so the real names come out like
   `u123456789_oradent`. **Copy the exact names and the password** — you need them in step 3.
3. Open **phpMyAdmin** for that database → **Import** → choose `sql/schema.sql`
   from this repository → **Go**.

You should end up with five tables: `appointments`, `blocked_slots`,
`admin_users`, `login_attempts`, `contact_messages`.

---

## 2. Connect the repository

1. hPanel → **Advanced → Git**.
2. **Create a new repository**:
   - Repository address: `https://github.com/Projjwalanka/oradentdentalcare.git`
   - Branch: `main`
   - Install path: leave **blank** (this deploys into `public_html`).
3. Click **Create**. Hostinger clones the repository.
4. Note the **Auto Deployment** webhook URL on that page. Add it in GitHub under
   *Settings → Webhooks → Add webhook*, content type `application/json`. After that
   every push to `main` goes live automatically.

> If `public_html` already contains the old site, back it up and empty it first —
> Git will not clone into a non-empty directory.

---

## 3. Add the database credentials

The `.env` file holds your database password, so it is deliberately **not** in the
repository. Create it on the server once:

1. hPanel → **Files → File Manager** → open `public_html`.
2. New file named `.env` (note the leading dot).
3. Paste this and replace the values with the real ones from step 1:

```
DB_HOST=localhost
DB_NAME=u123456789_oradent
DB_USER=u123456789_oradent
DB_PASS=your-real-database-password
DB_PORT=3306

SITE_URL=https://oradentdentalcare.com
APP_DEBUG=false
```

`.env` survives future `git pull` deployments, so you only do this once.

---

## 4. Create the staff account

1. Visit **https://oradentdentalcare.com/admin/setup.php**
2. Enter a name, a username and a password of at least 12 characters.
3. Submit. The page then **permanently disables itself** — it refuses to run again
   while an account exists, so nobody else can use it to create an admin.

Sign in afterwards at **/admin/login.php**.

> Choose the password in your password manager rather than reusing an existing one.
> There is no "forgot password" flow; if it is lost, delete the row from
> `admin_users` in phpMyAdmin and run `/admin/setup.php` again.

---

## 5. Point the domain and force HTTPS

1. hPanel → **Websites → your domain → SSL** — install the free Let's Encrypt
   certificate if it is not already active.
2. `.htaccess` already forces HTTPS and strips `www.`, so nothing else is needed.

---

## 6. Check it works

- [ ] `https://oradentdentalcare.com` loads and shows the clinic photos
- [ ] `http://` redirects to `https://`, and `www.` redirects to the bare domain
- [ ] `/services/root-canal-treatment` loads (confirms the rewrite rules work)
- [ ] `/book` shows the date strip, and picking a date lists times
- [ ] Make a test booking and confirm you get a reference number
- [ ] The booking appears in `/admin/`
- [ ] Cancel the test booking, then confirm that slot is offered again on `/book`
- [ ] `/sitemap.xml` and `/robots.txt` load

Delete the test booking row in phpMyAdmin when you are done.

---

## 7. Search-engine setup

1. **Google Search Console** → add the property → verify → submit
   `https://oradentdentalcare.com/sitemap.xml`.
2. **Rich Results Test** (`search.google.com/test/rich-results`) → paste the home
   page URL. You should see *Local Business* and *FAQ* detected.
3. **Google Business Profile** — see the warning below.

### ⚠ Your Google listing and this site still disagree on the street address

Your Business Profile is pinned at
**48, Maharani Indira Devi Rd, Parnasree Palli, Behala, Kolkata 700060**,
with hours of **9:30 AM–2 PM and 5:30–9 PM, all seven days**.

**Hours and area now match Google** — the site is set to those hours, every day,
and every page says Parnasree, Behala.

**The street line and postcode do not yet match.** The site still carries the
address from the clinic signage: **Alap Apartment, 105 Maharani Indira Devy Road,
Rabindra Nagar Auto Stand … 700103**. Note that 700103 is the Sonarpur PIN, while
Parnasree Palli is 700060 — so the current combination cannot be right.

Google weighs name/address/phone consistency heavily in local ranking, and a
mismatch also sends patients to the wrong place. **Pick whichever is correct and
make both match.**

- If the signage is right → update the address on your Google Business Profile
  and re-drop the map pin at the real location.
- If Google is right → change `street` and `postal` in `CLINIC` in
  `config/config.php`, commit, and push. Every page, the schema markup and the
  sitemap update together.

Also worth doing while you are there: set the primary category to **Dental clinic**,
add the secondary categories *Dentist*, *Dental implants periodontist*, *Oral surgeon*,
upload the clinic photos, and add the website URL.

---

## Routine maintenance

**Changing opening hours, prices or clinic details** — edit `config/config.php`
(or `includes/data.php` for treatment copy), commit and push. Hostinger redeploys
automatically if you set up the webhook.

**Taking a day off** — `/admin/blocks.php`, choose the date, block the whole day.
Patients then cannot book it. Existing bookings on that day are *not* cancelled;
the panel tells you how many there are so you can call those patients.

**Backups** — hPanel → Files → Backups. Take a database backup before any big
change. The appointment table is the only data that cannot be regenerated from
this repository.

**Adding a second staff login** — there is no UI for this by design. Add the row
directly in phpMyAdmin with a hash generated by
`php -r "echo password_hash('the-password', PASSWORD_DEFAULT);"`.
