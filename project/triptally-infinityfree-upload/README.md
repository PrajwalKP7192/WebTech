# TripTally

TripTally is a travel planner built with PHP, MySQL, HTML, CSS, and JavaScript. It includes registration/login, trip planning, itinerary management, budget tracking, packing checklists, and a contact form.

## Local Run

1. Import `data/schema.sql` into MySQL.
2. Configure `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASS`, or edit `config/database.php`.
3. Start the app:

```bash
php -S 127.0.0.1:8000 -t .
```

4. Open `http://127.0.0.1:8000/index.php`.

## Vercel

This project is adapted for Vercel by using the PHP community runtime rather than an official Vercel PHP runtime.

- `vercel.json` sends all page requests through `api/index.php`
- PHP sessions are stored in MySQL through the `app_sessions` table
- A remote MySQL database is required for Vercel deployments
- Environment variables should be set from `.env.vercel.example`

## Important Limits

- Vercel does not officially support PHP runtimes; this setup relies on `vercel-php`
- Your current local MySQL server cannot be reached from Vercel
- You still need a Vercel account login plus a hosted MySQL database to complete a real deployment
