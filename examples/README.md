# Examples

Two integrations of the shape most of them have: one writes a roster in, one reads a month out.
Both are single files and use the package as published.

```bash
export CLOCKSTER_TOKEN=...   # Settings → API in the web application
```

## `roster_sync.php`

Sync employees from a CSV, and dismiss whoever is no longer in it.

```bash
php examples/roster_sync.php people.csv
```

```csv
external_id,first_name,last_name,email,phone,location_code,location_title
HR-1,Aisulu,Serik,aisulu@example.com,+77010000001,WH-01,Warehouse
HR-2,Bolat,Nurlan,bolat@example.com,+77010000002,WH-01,Warehouse
```

What it shows: writing locations and reading their ids back out of the answer, `external_id` as the
key that makes a second run an update rather than a duplicate, batching at the hundred the endpoint
takes, and dismissing by difference — everybody active here who is not in the file. A column the
file leaves empty is not written at all, so the stored value stays.

Run it twice. The second run writes the same people and dismisses nobody, which is the property a
nightly sync needs.

## `timesheet_export.php`

Export a month of timesheets as CSV, a row per person per day.

```bash
php examples/timesheet_export.php 2026-08 > august.csv
```

What it shows: walking a listing with `listAll()`, asking for the facts with `include`, and the two
things that catch people out — times are seconds, and a day nobody was scheduled for answers a null
`planned` rather than being left out.

## Writing your own

The methods are named after the operations, so the
[API documentation](https://api.clockster.com/openapi/v3.json) reads as the reference for both:
`GET /users` is `$clockster->users->list(...)`, `POST /users/upsert` is
`$clockster->users->upsert(...)`. Every method answers the parsed body and throws on anything the
API refused.
