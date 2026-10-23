# DevNest

A marketplace where independent developers sell web, desktop and mobile software. Buyers browse, try and buy apps with a one-time payment, a lifetime licence and an instant download. Sellers get a dashboard to manage their apps, track sales and revenue. Admins get a full back office.

## Features

### For buyers
- Browse the catalogue by platform (Web, Android, Windows, iOS, macOS, Linux), tag or search term
- Sort by newest, most popular, price or name
- View app details: description, screenshots, feature list, version, requirements, demo link
- One-click checkout with PayPal (sandbox in development)
- Free apps settle instantly; paid apps redirect to PayPal and back
- Lifetime licence key issued instantly on payment
- Download purchased apps from a protected, rate-limited endpoint
- View and manage all purchases and licence keys
- Terms of service and privacy policy pages

### For sellers (developers)
- Sign up, verify email, and start publishing apps
- Create and edit apps: name, slug, tagline, description, platform, price, version, requirements, demo/download/repository URLs, cover image, screenshots, features, tags
- Draft, publish and archive apps (archived apps keep their sales history)
- Restore archived apps
- Dashboard with stats: published apps, sales, revenue, views
- Sales list with filters by status, order detail with buyer info
- Per-app view counter deduplicated per visitor per day

### For administrators
- Dedicated admin layout and dashboard with site-wide stats (users, apps, orders, revenue)
- User management: search, filter (admin/verified/suspended), promote/demote, verify/unverify email, send password reset link, reset two factor, suspend/unsuspend, archive or delete
- App moderation: list all apps (including archived), archive or restore
- Order oversight: list every order with status
- Site settings: app name, support email, registration toggle, full SEO (title, description, keywords, robots, canonical URL, Open Graph, Twitter cards)
- Page management: create/edit/delete pages with a WYSIWYG or Markdown editor, choose placement (header, footer, both, hidden), publish toggle
- Audit log of every admin action
- Sitemap (`/sitemap.xml`) and `robots.txt` generation

### Platform features
- PayPal Sandbox checkout (no SDK dependency — direct REST API via Laravel HTTP client)
- Webhook verification with signature check and order lookup fallback
- Idempotent order completion (a repeated callback never issues a second licence)
- Refund and reversal handling (revokes licence, adjusts sales counter)
- Soft deletes with history-safe archiving (buyers keep purchases even when an app is removed)
- Account anonymisation for accounts with sales history
- Named rate limiters per route group
- Trusted proxy support for running behind a load balancer or tunnel
- Inertia error page (404/403/500)
- Scheduled pruning of stale pending orders
- SQLite for local development, MySQL ready for production
- Full SEO meta tags and JSON-LD structured data on app pages

## Tech stack

- **Backend:** Laravel 13, PHP 8.5, Fortify (auth, 2FA, passkeys), Inertia v3
- **Frontend:** Vue 3, TypeScript, Tailwind CSS v4, Vite (vite-plus)
- **Database:** MySQL (production) / SQLite (local dev & tests)
- **Payments:** PayPal Orders v2 REST API (no SDK)
- **Editor:** TipTap (WYSIWYG) + Markdown (league/commonmark / marked)
- **Tests:** Pest (111 tests)

## Getting started

### Requirements
- PHP 8.3+
- Node.js 22+
- Composer 2
- A MySQL server (or use SQLite for local hacking)

### Setup
```bash
composer setup          # installs deps, copies .env, generates key, migrates, builds assets
php artisan db:seed     # optional: demo data + admin account
composer dev            # runs the dev server, Vite, queue worker and Pail
```

### Demo accounts (password: `password`)
- `admin@example.com` — administrator
- `test@example.com` — developer (Test User)
- `ada@example.com` — developer (Ada Sterling)
- `sam@example.com` — buyer (Sam Rivera)

### PayPal
Set `PAYPAL_MODE`, `PAYPAL_CLIENT_ID`, `PAYPAL_CLIENT_SECRET` and `PAYPAL_WEBHOOK_ID` in `.env`. Leave them empty to keep checkout closed. Webhook URL: `POST /checkout/paypal/webhook`.

### Production checklist
- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://your-domain`
- Configure a real mailer (verification and password reset emails)
- Flip `PAYPAL_MODE=live` with live credentials
- `php artisan config:cache && php artisan route:cache && php artisan view:cache`
- `php artisan storage:link` (or use S3 for the public disk)
- Set `TRUSTED_PROXIES` when running behind a proxy

## License

MIT
