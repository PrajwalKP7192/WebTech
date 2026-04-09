# VS Code Database Access

Use the SQLTools extension inside VS Code to browse the TripTally MySQL database.

## Install

Install these VS Code extensions:

- `SQLTools`
- `SQLTools MySQL/MariaDB Driver`

They are already recommended in `.vscode/extensions.json`.

## Connection Details

- Server: `127.0.0.1`
- Port: `3306`
- Database: `triptally`
- Username: `triptally_app`
- Password: `Z6px2NdQNV3CGGsm7UgXQ0sO`

You can copy the sample profile from `.vscode/sqltools-connections.example.json`.

## How To Connect

1. Open the project in VS Code.
2. Install the recommended SQLTools extensions.
3. Open the Command Palette.
4. Run `SQLTools: Add New Connection`.
5. Choose `MySQL/MariaDB`.
6. Enter the connection details above.
7. Save the connection.

## Useful Queries

```sql
SHOW TABLES;
SELECT * FROM users;
SELECT * FROM trips;
SELECT * FROM itinerary_items;
SELECT * FROM budget_entries;
SELECT * FROM packing_items;
SELECT * FROM contact_messages;
```
