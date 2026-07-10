# Shawn Radam Personal Advisor Website

A PHP/MySQL advisory website for property, loans financing, land lot campaigns, bilingual frontend content, newsletter capture, and a responsive admin CMS.

## What is included

- Frontend pages for home, properties, finance, blog, contact, about, calculators, and landing pages.
- Admin CMS for menus, blogs, about page content, TLS landing pages, header notifications, translations, calculators, ads, profile, feedback, and site settings.
- Responsive admin sidebar and mobile/tablet drawer with profile, logout, Home parent, Dashboard child, and matched menu icons.
- Public notification bell flow with mark-as-read behavior.
- Newsletter subscription form in the footer.
- TLS landing page support with full-image popup and WhatsApp CTAs.
- Mega menu and landing visuals included in `assets/menu` and `assets/landing`.

## Theme previews

![Home mega menu](assets/menu/home-mega-menu.png)
![Properties mega menu](assets/menu/properties-mega-menu.png)
![Loans financing mega menu](assets/menu/loans-financing-mega-menu.png)
![Land lot mega menu](assets/menu/land-lot-mega-menu.png)
![Blog mega menu](assets/menu/blog-mega-menu.png)
![Contact mega menu](assets/menu/contact-mega-menu.png)
![Tanah Lot Selupoh landing image](assets/landing/tanah-lot-selupoh-ads-whatsapp.jpg)

## Setup notes

This repository does not store live database passwords. Configure these environment variables on the server:

- `DB_HOST`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`
- `SITE_URL`

Install PHP dependencies with Composer when deploying from a fresh checkout:

```bash
composer install --no-dev --optimize-autoloader
```

The production web root should point at this repository root.