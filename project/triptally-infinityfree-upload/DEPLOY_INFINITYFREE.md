# InfinityFree Deployment

TripTally can be deployed to InfinityFree as a standard PHP + MySQL website.

## What To Upload

Upload the contents of the prepared deployment package to your InfinityFree account's `htdocs` folder.

Do not upload:

- `config/database.local.php`
- `.venv`
- `.vscode`
- local macOS files such as `.DS_Store`

## Database Setup

1. Create a MySQL database from the InfinityFree control panel.
2. Open phpMyAdmin from the InfinityFree panel.
3. Import `data/schema.sql`.
4. Edit `config/database.php` on the hosted copy with the database values from InfinityFree.

Example values to replace:

- `YOUR_DB_HOST`
- `YOUR_DB_NAME`
- `YOUR_DB_USER`
- `YOUR_DB_PASSWORD`

## Website Root

Deploy the app directly inside `htdocs` so `index.php` is at the website root.

## Recommended Upload Method

Use FTP rather than browser file uploads when possible.

## After Upload

Open your InfinityFree domain and test:

- registration/login
- trip creation
- budget entries
- packing checklist
- contact form
