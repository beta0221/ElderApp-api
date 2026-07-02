# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## ElderApp API — Laravel 5.8 Backend

### Development Commands

```bash
# Backend
php artisan serve          # Local API at http://localhost:8000
php artisan migrate
php artisan db:seed
php artisan cache:clear

# Run all tests
./vendor/bin/phpunit
# Run a single test file
./vendor/bin/phpunit tests/Feature/ExampleTest.php

# Frontend (Vue.js admin panel)
npm run dev        # one-time build
npm run watch      # rebuild on change
npm run production # minified build
```

### Architecture

**Two separate apps in one repo:**
- `routes/api.php` — JSON API consumed by iOS/Android mobile clients
- `routes/web.php` + `resources/js/` — Vue.js admin panel (SPA) served from the same Laravel app

**Authentication layers:**
- Mobile API: `middleware: ['JWT']` (tymon/jwt-auth) — token parsed in `app/Http/Middleware/JWT.php`; token sent as `Authorization: Bearer <token>`
- Admin web panel: `middleware: ['webAuth']` — session-based; login at `/web_admin_login_page`
- `middleware: ['JWT','admin']` guards admin-only API endpoints; `admin` resolves to `CheckAdmin` middleware which accepts roles: `admin`, `employee`, `accountant`, `local_admin`
- `role:accountant` (via `RoleMiddleware`) gates specific endpoints to a single role
- `FirmAndAdmin` middleware additionally allows `firm` role

**Member hierarchy (`user_group` table):**
Users belong to groups with 5 levels stored as columns `lv_1`…`lv_5` on the `user_group` row:
- `lv_1` 主人, `lv_2` 小天使, `lv_3` 大天使, `lv_4` 守護天使, `lv_5` 領航天使

`User::groupLevel()` reads the user's level from `user_group`. Group logic (joining, promoting, moving members) lives in `app/User.php`.

**Routing convention:** Many models use `slug` as the route key (`getRouteKeyName()` returns `'slug'`). Events and Products are identified by slug in URLs. Users are identified by `id_code`.

**Dual API versions:** Several Event and Product endpoints exist in two versions. Old `v1` routes are marked with comments `//舊的 版本更新後移除` (old, remove after version update). Prefer the `v2` routes (e.g. `event/eventList`, `event/eventDetail/{slug}`) for new work.

**Frontend structure (`resources/js/`):**
- `app.js` — admin panel entry; `supplierApp.js` — supplier/location panel entry
- `Helpers/User.js` — manages login, JWT storage in `localStorage`, role checking
- `Helpers/AppStorage.js` — wraps `localStorage` for token/role/username
- `router/router.js` — Vue Router config
- Components are organized by domain under `components/` (Member, Event, Product, Order, Clinic, Location, Insurance, Dashboard)

**Excel exports:** `app/Exports/` contains Maatwebsite Excel export classes for orders, members, clinic bills, insurance, and inventory.

**Image uploads:** Handled by `ImageController` + `app/Helpers/ImageResizer.php`; uploaded images are stored and tracked in `UploadEventBodyImageLog` / `UploadProductInfoImageLog` models.

**LINE integration:** LINE Bot webhook at `POST /api/line/webhook` (`LineBotController`). LINE Login via `AuthController@line_login`. LINE account binding at `/api/auth/bind_lineAccount`.

### Key Files

| File | Purpose |
|------|---------|
| `routes/api.php` | All mobile API routes |
| `routes/web.php` | Admin web panel routes |
| `app/User.php` | User model + group hierarchy logic |
| `app/Http/Middleware/CheckAdmin.php` | Defines which roles count as "admin" |
| `app/Http/Controllers/MemberController.php` | Largest controller — member CRUD, group management, Excel export |
| `app/Http/Controllers/EventController.php` | Event lifecycle, certificate issuance, manager assignment |
| `app/Http/Controllers/ProductController.php` | Products, inventory, purchasing, packages |
| `app/Helpers/ControllerTrait.php` | `resizeHtmlImg()` — used in controllers that store CKEditor HTML |
