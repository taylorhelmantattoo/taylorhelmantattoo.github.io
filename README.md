# Taylor Helman Tattoo — Website

**Live site:** [taylorhelmantattoo.com](https://taylorhelmantattoo.com)  
**Hosted via:** GitHub Pages (custom domain via `CNAME`)

---

## Purpose

This site is the link-in-bio destination for Taylor Helman's tattoo Instagram account. Every visitor arrives from a mobile device inside Instagram's in-app browser. The entire site exists to convert that Instagram traffic into booked clients and saved contacts.

---

## Development Priorities

### 1. Mobile-first, always
The overwhelming majority of visitors tap through from Instagram on their phone. Every layout decision, font size, tap target, and interaction must be designed for a 390px–430px screen first. Desktop is secondary.

- Minimum tap target size: **44×44px**
- Body text minimum: **14px**
- Line height minimum: **1.6** for readability on small screens
- No hover-only interactions — everything must work with touch
- Use `env(safe-area-inset-bottom)` for sticky elements so they clear the iPhone home indicator

### 2. Conversion-focused
The goal of every page is to drive one of three actions:

| Priority | Action | Link |
|----------|--------|------|
| 1 | Start a tattoo request | inkde.sk booking form |
| 2 | Book a touch-up evaluation | inkde.sk booking form |
| 3 | Save Taylor's email / contact card | `.vcf` file |

The sticky bottom bar keeps all three CTAs visible at all times. Do not remove or deprioritize it.

### 3. Reduce friction at every step
- Keep copy short and direct — users are on Instagram, not reading a blog
- Inline the most important information (email address visible, not hidden behind a click)
- Provide fallback paths for every action (e.g. if VCF fails, offer Copy Email)

---

## Known Issue: Instagram In-App Browser (IAB) Blocks VCF Downloads

### Problem
When a user clicks "Save Taylor's Email" from inside Instagram, the link opens in Instagram's built-in browser (WebView). This browser does **not** handle `.vcf` (vCard) file downloads — the tap either does nothing or fails silently.

This affects the most important user action on the page.

### Root cause
Instagram's IAB is a stripped-down WebView that intentionally blocks file downloads to keep users inside the app. It does not invoke the native file-save or Contacts flow that Safari and Chrome trigger when a `.vcf` is served.

### Implemented workarounds (layered approach)

**Layer 1 — `download` attribute on all VCF links**  
`<a href="/taylor-helman-tattoo.vcf" download="taylor-helman-tattoo.vcf">`  
This signals download intent to compliant browsers. Has no effect in the IG IAB but is correct practice everywhere else.

**Layer 2 — Detect Instagram's UA and show an "Open in Browser" banner**  
Instagram injects `Instagram` into the User Agent string. On detection, a dismissible banner appears at the top of the page that says:
- iPhone: "Tap ··· → Open in Safari"
- Android: "Tap ··· → Open in Chrome"

This is the most reliable path to a successful VCF save — once the user is in their native browser, the contact card works normally.

**Layer 3 — "Copy Email Address" clipboard button**  
A fallback that works inside the IAB. Uses `navigator.clipboard.writeText()` with a `document.execCommand('copy')` fallback. The button label changes to "Copied!" on success so the user gets clear confirmation.

**Layer 4 — QR code (scan to add contact)**  
A QR code on the page encodes the full vCard data. On iPhone the native camera app (accessible from the Control Center without leaving Instagram) can scan it and trigger an "Add to Contacts" prompt — no browser switch needed. The QR card is hidden if the image fails to load.

**Layer 5 — `mailto:` link (already present)**  
`mailto:taylorhelmantattoo@inkde.sk` opens the device's native mail app directly from within the IG IAB. This is a reliable last resort for users who cannot or will not switch browser.

### What doesn't work
- JavaScript `Blob` + `URL.createObjectURL()` downloads are also blocked by the IG IAB
- `window.location` redirects to the `.vcf` are also blocked
- There is no known way to make a VCF save work *natively inside* Instagram's browser — the open-in-browser path is the only reliable solution

---

## Pages

| File | Purpose |
|------|---------|
| `index.html` | Main landing page — availability summary, booking CTAs, email save, contact details |
| `release-form.html` | Release/consent form entry page — links to `release.taylorhelmantattoo.com` |
| `availability.html` | Full availability calendar — live data from Cloudflare Worker, Wed–Sat 11 AM–7 PM ET |
| `portfolio.html` | Portfolio gallery — Instagram feed via Behold widget, filterable by style |
| `policies.html` | Policies & FAQ |
| `taylor-helman-tattoo.vcf` | vCard contact card (VERSION 3.0) |
| `CNAME` | Custom domain config for GitHub Pages (`taylorhelmantattoo.com`) |
| `cloudflare-worker/worker.js` | Cloudflare Worker — queries Google Calendar free/busy API |
| `cloudflare-worker/wrangler.toml` | Wrangler deployment config (non-sensitive vars only) |

---

## Availability System (Cloudflare Worker)

Real-time availability is powered by a **Cloudflare Worker** (`taylorhelmantattoo-availability`) that sits between the site and Google Calendar.

### How it works

1. `availability.html` (and a summary widget on `index.html`) fetches JSON from the worker endpoint:  
   `https://taylorhelmantattoo-availability.taylorhelmantattoo.workers.dev/api/availability`
2. The worker authenticates to Google Calendar using a **service account** (RS256 JWT — no third-party libraries) and calls the [free/busy API](https://developers.google.com/calendar/api/v3/reference/freebusy/query).
3. It computes per-day availability statuses (`available`, `limited`, `booked`, `closed`, `flash-day`, `travel`, `convention`) and morning/afternoon window labels.
4. The response is cached for **15 minutes** via Cloudflare's Cache API.

### Privacy guarantee
The free/busy API returns **only opaque time blocks** — no event titles, client names, descriptions, or attendees. This is a hard Google API contract, not a server-side filter.

### Schedule configuration
All schedule settings live in the `CONFIG` object at the top of `worker.js`. After editing, deploy with:

```bash
npx wrangler deploy
```

| Setting | Value |
|---------|-------|
| Working days | Wed–Sat (3–6) |
| Working hours | 11 AM – 7 PM ET |
| Booking horizon | 60 days |
| Lead time | 48 hours |
| Cache TTL | 15 minutes |

### Special day overrides
Add entries to `CONFIG.specialDays` in `worker.js` to mark specific dates (e.g. conventions, flash days, travel):

```js
specialDays: {
  "2026-04-12": { status: "closed",    label: "Convention" },
  "2026-05-17": { status: "flash-day", label: "Flash Day"  },
}
```

### Secrets
`GOOGLE_SERVICE_ACCOUNT_JSON` is stored in **Cloudflare encrypted secrets only** — never committed to Git. Set it with:

```bash
npx wrangler secret put GOOGLE_SERVICE_ACCOUNT_JSON
```

---

## Portfolio Page

`portfolio.html` embeds an **Instagram feed via [Behold](https://behold.so/)** (`<behold-widget feed-id="...">`), loaded dynamically from `https://w.behold.so/widget.js`.

Filter tabs (All Work, Micro Realism, Fine-Line Florals, Pet Portraits, Flash) are in place but only the **All Work** tab is live — filtered category feeds are marked as coming soon.

---

## Release Form (release.taylorhelmantattoo.com)

The tattoo consent/release form is hosted on a separate subdomain (`release.taylorhelmantattoo.com`) running **Stabpad** — a PHP-based tattooist release-form system — on **InfinityFree** hosting.

### Setup summary

| Component | Detail |
|-----------|--------|
| Subdomain | `release.taylorhelmantattoo.com` |
| Server IP | `185.27.134.139` |
| DNS record | `A release → 185.27.134.139` (GoDaddy) |
| Hosting | InfinityFree (free tier) |
| Language | PHP (plain, no framework) |
| Local files | `C:\TaylorHelmanTattoo\release\` |

### How it works

1. Client taps "Open Release Form" on `release-form.html` → goes to `https://release.taylorhelmantattoo.com`
2. Client fills out the consent form (name, DOB, health disclosures, signature checkboxes)
3. On submit, `functions.inc.php` renders the completed form as a PDF using **DOMPDF**
4. The PDF is emailed to `taylorhelmantattoo@inkde.sk` (+ CC to the client's email) via **PHPMailer + Gmail SMTP**

### PHP mailer configuration

The mailer is configured in `C:\TaylorHelmanTattoo\release\smtp_config.php`.

**Before uploading to InfinityFree, fill in the Gmail App Password:**

1. Open `C:\TaylorHelmanTattoo\release\smtp_config.php`
2. Replace `YOUR_APP_PASSWORD_HERE` with the 16-character Gmail App Password
3. Do **not** commit this file to Git — it contains a credential

### Deploying to InfinityFree

Upload the entire contents of `C:\TaylorHelmanTattoo\release\` to the `htdocs/` directory of the InfinityFree account via the File Manager (or FTP).

Key files:

| File | Purpose |
|------|---------|
| `configs/tattoo.php` | Form configuration — fields, provisions, email settings, artist name |
| `functions.inc.php` | Core logic — PDF rendering (DOMPDF) + email (PHPMailer) |
| `smtp_config.php` | Gmail SMTP credentials — **keep out of Git** |
| `phpmailer/` | PHPMailer v6 library files |
| `dompdf/` | DOMPDF library for PDF generation |
| `index.php` | Stabpad entry point |

### Editing the form provisions

Open `configs/tattoo.php`. Each entry in `$settings['provisions']` is one checkbox/question on the form. Fields:
- `title` — heading text
- `text` — full description shown to the client
- `required` — `true` or `false`
- `type` — `checkbox`, `yn` (yes/no), or `text`

After editing, re-upload `configs/tattoo.php` to InfinityFree.

---

## Deployment

This site is deployed automatically via **GitHub Pages** from the `main` branch. Pushing to `main` deploys within ~60 seconds.

```bash
git add .
git commit -m "describe your change"
git push origin main
```

There is no build step — everything is plain HTML/CSS/JS.

---

## Editing Guidelines

- **No frameworks, no build tools.** The site is intentionally plain HTML/CSS/JavaScript. Keep it that way — zero dependencies means zero breakage and instant load times on mobile.
- **Inline styles/scripts are acceptable** given the single-page nature of each file.
- **Test in Chrome mobile emulation AND in an actual Instagram browser** (paste the URL in an IG DM to yourself and tap it) before pushing changes to the VCF/save-email flow.
- **Color palette is intentional** — the muted rose/terracotta tones match Taylor's brand. Do not introduce new colors without a design reason.
- **Accessibility:** maintain sufficient contrast, use semantic heading hierarchy (`h1` → `h2` → `h3`), and never remove `alt` attributes from images.

---

## Testing the Instagram IAB

To test a change in the Instagram in-app browser:
1. Open Instagram DMs and send yourself a message with the site URL
2. Tap the link — it opens in the IG IAB
3. Verify the "Open in Safari/Chrome" banner appears and dismisses correctly
4. Verify the "Copy Email" button works and shows the confirmation state
5. Verify `mailto:` opens the native mail app

Alternatively use the **Kiwi Browser** on Android (allows installing Chrome extensions, easier to spoof UAs for testing).
