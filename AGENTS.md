# Ecommerce project instructions

Use these notes when extending this Laravel ecommerce repository. Treat the checked-in code and migrations as the source of truth; inspect the relevant implementation before changing it. This is an existing application, not a blank starter.

## Project overview

- Laravel 12, PHP 8.2+, Blade templates, Eloquent, and MySQL/MariaDB (commonly XAMPP locally).
- Ecommerce features already implemented: storefront, product/category browsing, session cart, checkout and orders, admin login/dashboard, product/category management, and order status management.
- Preserve existing routes, cart and checkout behavior, database records, uploaded image paths, and admin access rules unless the task explicitly asks to change them.
- Do not replace the application with a new scaffold or add a frontend framework/dependency when the existing Blade/CSS setup is sufficient.

## Where to look

- `routes/web.php`: storefront, cart, checkout, and admin routes.
- `app/Http/Controllers/`: storefront, cart, checkout, and admin behavior.
- `app/Models/` and `database/migrations/`: data model, relationships, and schema.
- `resources/views/storefront/`: storefront pages and shared product-card partial.
- `resources/views/admin/`: admin UI.
- `public/css/app.css`: existing storefront and admin styling.
- `app/Services/ImageOptimizationService.php`: product/category upload processing.
- `database/seeders/DatabaseSeeder.php`: repeatable demo categories/products.
- `basic_ecommerce.sql`: phpMyAdmin/XAMPP import that creates the database schema and starter catalog.

Follow the existing shared product-card partial and CSS patterns when changing product listings; check all pages that include the partial, not just the homepage.

## Data and image safety

- Never run destructive database commands such as `php artisan migrate:fresh` or drop/truncate tables unless explicitly requested and confirmed.
- Read migrations before editing schema. Prefer a new migration for schema changes; keep `basic_ecommerce.sql` consistent when a change should also be available to people importing the project in phpMyAdmin.
- Keep credentials and machine-specific `.env` values out of source control and responses. Do not copy the local `.env` into setup instructions.
- Keep image upload validation, optimization, and cleanup in the existing service/controller workflow. Product images should preserve their aspect ratio, use the existing paths, and degrade gracefully if missing.
- Create admin accounts through `php artisan create:admin`; never add a hard-coded admin password or seed a shared privileged account.

## Working and verification

1. Inspect the related routes, controller/model, view, CSS, tests, and current working-tree changes before editing.
2. Make focused changes that follow the current Laravel and Blade patterns. Preserve existing behavior outside the requested change.
3. Run the smallest relevant test first; run the full suite with `php artisan test` when appropriate. For PHP formatting, use `vendor/bin/pint --test`.
4. For frontend changes, check the relevant Blade views and CSS at desktop, tablet, and mobile widths; verify there is no horizontal overflow and existing actions still work.
5. Report changed files, behavior, and validation performed. Call out any checks that could not be run.

Useful local commands:

```text
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan create:admin
php artisan test
npm install
npm run build
```

For a phpMyAdmin setup, start XAMPP MySQL, import `basic_ecommerce.sql`, configure the local `.env` to use the imported database, then install dependencies and create an administrator as needed. Do not run migrations blindly on an existing/imported database; check `php artisan migrate:status` first.
