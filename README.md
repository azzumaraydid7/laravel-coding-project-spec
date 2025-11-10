<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Laravel 12 CSV Upload Project

### Description

This project enables CSV ingestion with:
- Drag-and-drop or file select upload
- Background processing via queued jobs (Horizon or queue:work)
- Recent uploads list with real-time status
- Idempotent upsert based on `UNIQUE_KEY`
- Alpine.js + SweetAlert2 for interactivity

### Requirements
- PHP >= 8.2
- Composer
- Node.js + npm
- Redis (for queues/Horizon)
- A database (MySQL recommended)

If `php` is not available on macOS:
- Install via Homebrew: `brew install php`
- Install Composer: `brew install composer` (or download from getcomposer.org)

### Installation
1) Clone the repository
   - `git clone <your-repo-url>`
   - `cd <project-folder>`

2) Install PHP dependencies
   - `composer install`

3) Install Node.js dependencies & build assets
   - `npm install`
   - For development: `npm run dev` (Vite)
   - For production build: `npm run build`

4) Copy .env and generate app key
   - `cp .env.example .env`
   - `php artisan key:generate`

5) Configure database in `.env`
   - `DB_CONNECTION=mysql`
   - `DB_HOST=127.0.0.1`
   - `DB_PORT=3306`
   - `DB_DATABASE=your_database`
   - `DB_USERNAME=your_username`
   - `DB_PASSWORD=your_password`

6) Run migrations
   - `php artisan migrate`

7) (Optional) Install Horizon for queue monitoring
   - `composer require predis/predis`
   - `composer require laravel/horizon`
   - `php artisan vendor:publish --provider="Laravel\Horizon\HorizonServiceProvider"`

### PHP Settings for Large CSV Uploads
We recommend increasing upload and memory limits when using `php artisan serve`:
- Temporary (development):
  - `php -d upload_max_filesize=50M -d post_max_size=50M -d memory_limit=1024M artisan serve`
- Permanent (CLI `php.ini`):
  - Find config: `php --ini`
  - Set values:
    - `upload_max_filesize = 50M`
    - `post_max_size = 50M`
    - `memory_limit = 1024M`
  - Restart the server after saving.

### Running the Project
1) Start Redis
   - `redis-server`

2) Start a queue worker (choose one):
   - Horizon: `php artisan horizon`
   - Basic worker: `php artisan queue:work`

3) Start Laravel dev server with increased limits
   - `php -d upload_max_filesize=50M -d post_max_size=50M -d memory_limit=1024M artisan serve`

4) Open in browser
   - `http://127.0.0.1:8000`

### Features
- Drag & drop CSV upload area
- File select button
- Real-time recent uploads list (Alpine.js polling)
- SweetAlert2 notifications for upload and processing status
- Background job processing (Horizon or queue:work)

### Memory-Efficient CSV Handling
- `SplFileObject` streaming
- Batched `Product::upsert()`
- UTF-8 character cleanup
- Idempotent UPSERT using `UNIQUE_KEY`

### Optional Notes
- For very large CSVs, adjust `batchSize` in `ProcessCsvFile` (default: 500)
- Frontend uses Alpine.js + SweetAlert2 — no Livewire required
- Keep `memory_limit` high if processing very large CSVs (>50 MB)

### Troubleshooting
- `php: command not found` → install PHP (e.g., `brew install php`) and Composer
- Upload stuck at "pending" → ensure Redis is running and start Horizon or `queue:work`
- Port 8000 in use → stop the other process or run `php artisan serve --port=8001`