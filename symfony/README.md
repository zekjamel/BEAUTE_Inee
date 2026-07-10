# Beauté INÉE — Symfony migration

This folder contains the Symfony migration of the current PHP website.

## Local start

```bash
cd symfony
composer install
cp .env.local.example .env.local
php -S 127.0.0.1:8001 -t public
```

Then open:

```txt
http://127.0.0.1:8001
```

## Current state

- Symfony 7.4 LTS, compatible with Hostinger PHP 8.3.
- Twig, Doctrine, EasyAdmin and Maker are installed.
- The `/` route currently renders the legacy `../index.php` through `LegacyHomeController`.
- Static assets have been copied into `public/` so the legacy page can render inside Symfony.

## Migration plan

1. Keep the legacy bridge working as a safety net.
2. Move the layout from `header.php` and `footer.php` into Twig.
3. Move `config.php` content into Symfony services/config/translations.
4. Replace the legacy homepage section by section with Twig templates.
5. Add database entities and EasyAdmin once the public page is stable.
