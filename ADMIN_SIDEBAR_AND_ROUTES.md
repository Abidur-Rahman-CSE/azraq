# Admin Sidebar And Routes

## Recommended Sidebar
- Dashboard
- Catalog
- Personalization
- Inventory
- Orders
- Customers
- Marketing
- Content
- Operations
- Reports
- Settings
- Admins & Roles
- Activity Logs

## Route Grouping Direction
- `/admin/dashboard`
- `/admin/catalog/categories`
- `/admin/catalog/collections`
- `/admin/catalog/tags`
- `/admin/catalog/products`
- `/admin/catalog/bundles`
- `/admin/catalog/services`
- `/admin/personalization/templates`
- `/admin/personalization/fonts`
- `/admin/inventory/*`
- `/admin/orders/*`
- `/admin/content/*`
- `/admin/settings/*`

## Controller Organization
- Keep storefront and admin controllers separate.
- Favor resource controllers for CRUD-heavy areas.
- Move pricing, personalization, and stock calculations into services or actions instead of controllers.
