# Expense

Costs to Expect: Expense — a lightweight Laravel app for recording expenses
for the kids against the [Costs to Expect API](https://github.com/costs-to-expect/api)'s
`allocated-expense` item type.

Two authenticated users only (no registration), one-off and split expenses,
and locally-scheduled recurring expenses.

## Local development

```bash
cp .env.example .env
docker compose up -d
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

The app expects a running Costs to Expect API instance (see `API_URL` in
`.env`) and a public, `allocated-expense` resource type id (`API_RESOURCE_TYPE_ID`).
