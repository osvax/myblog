# PHP MVC migration

The project uses a lightweight PHP MVC entry point in `public/index.php`; OSPanel serves `public` as the document root. Request handlers are defined by MVC controllers and routes; standalone legacy PHP endpoint scripts have been removed.

- `public/css`, `public/js`, `public/admin/css`, and `public/admin/js` contain the public/admin browser assets. Shared image assets are in `public/img`.
- User uploads are stored in `public/uploads`. Direct access is denied except for validated public blog/portfolio images, which are served through an MVC route; private files remain available through the authenticated file manager API.

- `public/index.php` is the front controller for `/api/*`, `/health`, and non-file URLs.
- `app/Core` contains the router, request/response abstractions, view renderer, and shared PDO connection.
- `app/Controllers/Admin` and `app/Models/AdminUser.php` implement admin authentication.
- `app/Controllers/Api` and `app/Models/Consultant.php` connect the consultant API to the MVC entry point and shared database layer.
- Admin upload, private file, and template operations are handled by `MediaController`, `FileManagerController`, and `TemplateController`; all modifying requests require an authenticated session and CSRF token.
- The public site has separate `HomeController`, `BlogController`, `PortfolioController`, `ContactsController`, and `OrderController` classes under `app/Controllers/Frontend`.
- `app/Views/frontend` and `app/Views/admin` are separate template areas. New page templates can be added under either area and rendered by `App\\Core\\View`.
- Frontend page templates use shared `partials` for headings and decorative waves, and reusable `components` for article/project cards. Article and project listing metadata is kept in `config/public-content.php`; controllers pass it into the views.
- `routes/web.php` and `routes/api.php` define web and API routes separately.
- The homepage, blog archive, articles, portfolio, contact and order pages render through the shared frontend layout. Existing public `.html` URLs remain routed through MVC.
- Admin pages render through the shared admin layout and require an authenticated session. Login and error templates use a standalone admin layout; login remains public.

Current MVC routes:

- `POST /api/auth/login`
- `GET /api/auth/csrf`
- `GET /api/auth/check`
- `POST /api/auth/logout` (requires the `X-CSRF-Token` header)
- `POST /api/consultant`
- `GET|POST /api/admin/files` (authenticated file listing, transfer, upload, and deletion)
- `POST /api/admin/media` and `POST /api/admin/avatar` (authenticated uploads)
- `GET|HEAD /api/admin/avatar/{filename}` (authenticated avatar delivery)
- `GET|POST /api/admin/templates` (authenticated allowlisted PHP view editor)
- `GET|HEAD /uploads/{scope}/{id}/{filename}` (validated public media)
- `GET /health`
- `GET /`, `/index.html`, `/blog.html`, `/portfolio.html`
- `GET /contacts.html`, `/order.html`
- `GET /messages.html`, `/orders.html`, `/settings.html` (legacy portfolio content)
- `GET /post-*.html` and `/project-*.html`
- `GET /admin/*.html` (authentication required except `/admin/login.html`)
- `GET /mvc` (default user-facing template)
- `GET /mvc/admin` (admin-only template)

The top-level `/messages.html`, `/orders.html`, and `/settings.html` URLs retain their existing portfolio-page content. Public forms and admin CRUD endpoints use the MVC API.

The active frontend and admin template names are configured separately in `config/views.php`. Add alternate templates under `app/Views/frontend/templates/<name>` or `app/Views/admin/templates/<name>`, then change the matching config value.
# myblog
