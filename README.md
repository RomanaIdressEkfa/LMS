# Nova LMS

A modern Learning Management System — a **single Laravel 12 app** with a
server-rendered **Blade + Tailwind + Alpine.js** UI, a custom Roles & Permissions
engine, a toggleable Addon/Module system, and toggleable payment gateways.
(It began as a Laravel API + Next.js frontend; the frontend was migrated into
Blade so it runs on plain PHP hosting with no Node at runtime.)

## Stack
- **Laravel 12**, MySQL, spatie/laravel-permission, session-based web auth
- **Blade + Tailwind v4 + Alpine.js**, built with Vite (assets → `public/build`)

The repo root **is** the Laravel app (no more `backend/` subfolder).

## Run it (one terminal)

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

Then open **http://localhost:8000** and log in. Use the `localhost` host, not
`127.0.0.1` — session cookies are scoped to `SESSION_DOMAIN=localhost`.

Building assets after a UI change: `npm run dev` (watch) or `npm run build`.

## Demo accounts (password: `password`)
| Role        | Email                   | Sees |
|-------------|-------------------------|------|
| Super Admin | super@novalms.test      | Everything |
| Admin       | admin@novalms.test      | Users, roles (view), modules, gateways, settings |
| Teacher     | teacher@novalms.test    | Courses, live, quizzes |
| Student     | student@novalms.test    | Dashboard, courses, live, quizzes (view) |

## Reset / reseed the database
```bash
php artisan migrate:fresh --seed
```

## Phase status
- ✅ **Phase 1** — Auth, Roles & Permissions builder, Modules (addon) toggle,
  Payment gateway toggle, dashboard shell, bold design system.
- ✅ **Phase 2** — Courses: catalog (filters/search), course detail with
  lesson-locking + free preview, free enrollment, lesson viewer with progress
  tracking, teacher course editor + curriculum builder, category-colored cards.
  (Paid enrollment is intentionally blocked pending Phase 3 checkout.)
- ✅ **Phase 3** — Paid checkout via a pluggable payment-driver system. Orders
  table, `PaymentManager` + drivers (Test sandbox, Bank offline, real Stripe),
  checkout modal that lists enabled gateways, My Purchases history, success/
  cancel pages. The **Test (Sandbox)** gateway ships enabled so the buy→pay→
  enroll loop works out of the box; enable Stripe by pasting keys in the admin.
- ✅ **Phase 4** — Live Classes (teacher scheduler; join link revealed only when
  a session is live) + Quizzes (teacher builder with per-question correct-answer
  picker; student taker with server-side auto-grading — answers never sent to
  the client). Also added a `module:<key>` middleware so **disabling a module
  in the admin makes its whole API surface return 404** (proven for quizzes).
- ✅ **Phase 5** — Users management (searchable table, role filter, add user,
  edit roles, suspend/activate, delete, and **impersonate** with a "return to
  admin" banner) + Settings admin (grouped site config: name, tagline, currency,
  support email, allow-registration, course-approval toggles).
- ✅ **Phase 6** — Multi-tenant **reselling control plane**. Plans (package
  modules into priced tiers) + Tenants (customer academies, each with a plan,
  per-tenant module toggles, price override, branding colour, status). The
  `/api/bootstrap` endpoint is **tenant-aware**: send an `X-Tenant: <slug>`
  header and it returns only the modules that tenant's plan enables, plus their
  branding — so one install serves many resold academies.
  - Next increment (not yet built): per-row **data isolation** (`tenant_id`
    scoping on courses/users/etc.) so each academy's data is fully separate.

### Multi-tenant reselling (Phase 6)
- **Plans** (`/dashboard/platform/plans`) — the packages you sell.
- **Tenants** (`/dashboard/platform/tenants`) — your customers. "Manage" a
  tenant to assign a plan, override its price, toggle individual modules, or
  change status (trial/active/suspended). All super-admin only (`tenants.*`).
- A tenant's app is served by sending `X-Tenant: their-slug` on API calls; the
  frontend would set this per subdomain in a production deploy.

### Addon system (how "enable only what you use" works end-to-end)
Each feature route group is wrapped in `module:live_classes` / `module:quizzes`.
Toggling the module off in Admin → Modules makes those endpoints 404 and hides
the sidebar links — the feature disappears platform-wide, code untouched.

### Enabling a real gateway (e.g. Stripe)
1. Admin → Payment Gateways → toggle Stripe on.
2. Save its `secret_key` into the gateway's `credentials` (the update endpoint
   `PUT /api/gateways/{id}` accepts `credentials`).
3. Checkout will redirect buyers to Stripe Checkout; on return, `/checkout/
   {ref}/confirm` verifies the session and creates the enrollment.
Add new gateways by writing one driver class implementing
`PaymentGatewayContract` and registering it in `PaymentManager`.

### Course URLs (note on slugs)
Slugs are auto-generated from titles via Laravel `Str::slug`, so
"UI/UX Design Fundamentals" becomes `uiux-design-fundamentals`. Always link
using the `slug` returned by the API, never a hand-built one.

## Notes
- The UI is server-rendered Blade under `resources/views`; interactivity is
  Alpine.js, and forms POST to CSRF-protected web routes (`routes/web.php`).
  There is no separate JSON API or Node runtime.
- Bilingual (en/bn) text is toggled client-side via a persisted Alpine store;
  the UI dictionary lives in `lang/dict.php` with admin overrides.
