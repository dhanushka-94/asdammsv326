# ASDA Member Management System (MMS)

Web application for the All Island School Development Association (ASDA) to manage members, events, enrollments, invitations, and reception attendance.

Built with Laravel 12, MySQL, Vite, and Tailwind CSS. Application clocks use **Sri Lanka Standard Time** (`Asia/Colombo`).

## Developers

| Name | Role |
|------|------|
| **Dhanushka Bandara** | Full Stack Developer |
| **Greshan Bandara** | Full Stack Developer |

## Stack

- PHP 8.2+ / Laravel 12
- MySQL
- Vite + Tailwind CSS 4
- QR attendance (`html5-qrcode`)
- Invitation PDF overlays (FPDI / FPDF)

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
# Configure MySQL in .env, then:
php artisan migrate
npm install
npm run build
php artisan serve
```

See `.env.example` for organized environment variables (locale, timezone, database, mail, etc.).

## License

Proprietary — ASDA Member Management System. All rights reserved unless otherwise agreed.
