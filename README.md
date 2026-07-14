# Portfolio

This repository currently contains a clean Laravel 13 application skeleton.

## Repository state

The application code was reset to a fresh `laravel/laravel` 13.x project while preserving
Git history and repository-control documentation.

Historical versions remain available on their release/archive branches for reference only and
must not be copied back into the active application without an explicit issue.

## Current stack

- Laravel 13 / PHP 8.3+
- Blade SSR
- npm and Vite
- PHPUnit
- SQLite by default for local development

## Local setup

Requirements: PHP 8.3+, Composer, Node.js and npm.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
php artisan serve
```

## Quality checks

```bash
php artisan test
npm run build
```

Read before contributing:

- `AGENTS.md`
- `PROJECT_STATUS.md`
- `docs/`
