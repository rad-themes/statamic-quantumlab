# QuantumLab for Statamic

QuantumLab is an AI research and deep tech lab website starter kit by [Rad Themes](https://statamic.com/creators/rad-themes). It uses native Statamic collections, Antlers, and Tailwind CSS 4. All imagery is bundled locally. No Webflow runtime or paid addon is required.

Build a scientific deep tech studio website with research tracks, foundation model cards, publications repository, compute grant tiers, and inquiry forms.

## Included

- Six main pages: home, research, models, grants, publications, and contact.
- Four research tracks, four foundation models, and four research publications, each with a detail page.
- Native navigation, brand/contact globals, managed images, SEO fields, mobile navigation, and a custom 404 page.
- Grant application form with server validation, CSRF protection, honeypot, and submissions stored in the Control Panel.

## Requirements

- Statamic 6.34 or later in the 6.x series.
- PHP 8.3 or newer within 8.x and Composer 2.
- Node.js 22.12 or newer and npm.

## Installation

```sh
composer config repositories.quantumlab path /absolute/path/to/quantumlab
php please starter-kit:install rad-themes/quantumlab --local --without-user --clear-site
php please make:user
php please stache:refresh
npm ci
npm run build
php artisan serve
```

## Checks

```sh
php artisan test --compact tests/Feature/QuantumLabKitTest.php
npm run build
```
