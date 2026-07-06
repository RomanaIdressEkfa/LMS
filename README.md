# Nova LMS

A modern Learning Management System — **Laravel 12 API** + **Next.js 16 frontend**,
with a custom Roles & Permissions engine, a toggleable Addon/Module system, and
toggleable payment gateways.

## Stack
- **backend/** — Laravel 12, MySQL, Sanctum tokens, spatie/laravel-permission
- **frontend/** — Next.js 16 (App Router, TypeScript, Tailwind v4), Sora bold font

## Run it (two terminals)

**1. Backend API** (http://127.0.0.1:8000)
```bash
cd backend
php artisan serve --host=127.0.0.1 --port=8000
```

**2. Frontend** (http://localhost:3000)
```bash
cd frontend
npm run dev
```

Then open http://localhost:3000 and log in.

## Demo accounts (password: `password`)
| Role        | Email                   | Sees |
|-------------|-------------------------|------|
| Super Admin | super@novalms.test      | Everything |
| Admin       | admin@novalms.test      | Users, roles (view), modules, gateways, settings |
| Teacher     | teacher@novalms.test    | Courses, live, quizzes |
| Student     | student@novalms.test    | Dashboard, courses, live, quizzes (view) |

## Reset / reseed the database
```bash
cd backend
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
- Brand name lives in `frontend/src/lib/brand.ts` — change it in one place.
- The Coca-Cola "TCCC-Unity" font is proprietary and cannot be shipped; Nova LMS
  uses **Sora** (free, heavy) for the bold look.
