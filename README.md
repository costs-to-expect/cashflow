# Costs to Expect: Cashflow

A lightweight Laravel app for recording allocated expenses against the
[Costs to Expect API](https://github.com/costs-to-expect/api)'s
`allocated-expense` item type.

The app tracks expenses for one or more **resources** under a single, fixed
API resource type — a resource can be anything you're tracking allocated
costs for (children, a project, a side hustle, a small business, whatever
fits). What resources are called in the UI ("Child"/"Children" by default) is
configurable per deployment, from Settings.

Currently in alpha, built for a single family's private use, with a
hard-coded two-user allow-list and no public registration.

## Features

- **Percentage-based splitting** — record an expense once and share it across
  as many resources as needed by percentage, rather than duplicating it per
  resource.
- **Recurring expenses** — set a monthly cost up once; a daily scheduled
  command posts it automatically on its due date, split the same way every
  time. Recurring templates live in this app's own database — the API has no
  recurring concept.
- **Reporting periods** — define recurring day/month-boundary windows (e.g.
  "6 April → 5 April") and see running totals for the current instance of
  each one, per resource and combined across all resources.
- **Default split, categories, and resource naming** — configurable from
  Settings, all stored locally and layered on top of the API's own data.

## Tech stack

- Laravel 12, PHP 8.3+
- Tailwind CSS v4 (compiled via the standalone CLI, no Node/npm — see
  `bin/css`)
- Plain vanilla JavaScript for form interactivity, no build step
- MySQL for local dev and production, SQLite for the automated test suite
- A custom API-backed auth guard — there's no local `users` table; every
  request re-resolves the signed-in user from the API via a bearer token
  cookie

## Requirements

- Docker (app + MySQL run as containers alongside a running Costs to Expect
  API instance)
- A public, `allocated-expense` resource type already created on that API

## Local development

```bash
cp .env.example .env
docker compose up -d
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

Or, if you have PHP available locally rather than through Docker:

```bash
composer setup
```

### Key environment variables

| Variable | Purpose |
|---|---|
| `API_URL` | Base URL of the Costs to Expect API instance |
| `API_RESOURCE_TYPE_ID` | The fixed resource type this app operates under |
| `API_ITEM_TYPE_ID` / `API_ITEM_SUBTYPE_ID` | The `allocated-expense` item (sub)type ids |
| `API_SERVICE_TOKEN` | Long-lived token used by the recurring-expense scheduler, which runs outside any signed-in user's session |
| `APP_ALLOWED_EMAILS` | Comma-separated list of emails allowed to sign in |
| `APP_RESOURCE_TERM` / `APP_RESOURCE_TERM_PLURAL` | Default singular/plural naming for a resource in the UI (overridable per-deployment from Settings) |

See `.env.example` for the full list.

## Frontend assets

CSS is compiled with the standalone Tailwind CLI:

```bash
bin/css          # one-off build
bin/css --watch  # rebuild on change
```

JavaScript has no build step — files are edited directly under
`public/js/{version}/`. Both asset paths are versioned via
`config/app/version.php`, referenced in views through the shared `$version`
variable.

## Testing

```bash
composer test
```

Runs against SQLite and doesn't need a live API connection — it covers pure
local logic such as recurring-expense date math and reporting-period window
calculation.

## Recurring expenses

`php artisan expense:process-recurring` finds active recurring templates due
today (or overdue) and posts them to the API, guarding against double-posting
if the scheduler fires more than once for the same date. It's scheduled to
run daily via `routes/console.php`.
