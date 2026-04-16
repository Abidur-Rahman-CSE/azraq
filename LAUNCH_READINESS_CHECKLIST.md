# Azraq Bridal Launch Readiness Checklist

## Performance
- Run `php artisan config:cache`, `php artisan route:cache`, and `php artisan view:cache` in production.
- Run `npm run build` and serve only versioned Vite assets from `public/build`.
- Use a persistent cache/session backend in production instead of local file defaults where possible.
- Recompress large catalog images before upload and prefer consistent aspect ratios for product cards.

## SEO
- Verify `APP_URL` is set to the production domain so canonical tags and sitemap URLs are correct.
- Submit `/sitemap.xml` to Google Search Console and Bing Webmaster Tools.
- Review `meta_title` and `meta_description` values for products, categories, collections, and CMS pages.
- Replace placeholder social URLs in [config/brand.php](/Users/abid/Development/My_personal/Azraq/config/brand.php:1) with real brand profiles.

## Accessibility
- Keyboard-test primary storefront flows: browsing, PDP, cart, checkout, booking, and admin CRUD.
- Confirm image alt text is present for uploaded catalog media.
- Review color contrast after any future brand-token changes.

## Security
- Set `APP_ENV=production` and `APP_DEBUG=false`.
- Enable HTTPS and secure cookies at the web-server / proxy layer.
- Move mail, payment, and queue credentials to production secrets management.
- Restrict `/admin` behind authentication before launch.

## Operations
- Set up a queue worker for checkout emails, proof generation, and future background jobs.
- Configure backups for the database and uploaded media.
- Add uptime monitoring for the storefront, checkout, bookings, and sitemap endpoints.

## QA
- Re-run `php artisan test`.
- Smoke-test home, shop, category, collection, standard PDP, personalized PDP, service booking, cart, checkout, order tracking, FAQ, and admin modules on mobile and desktop.
- Validate seeded/demo content is replaced with real launch content before go-live.
