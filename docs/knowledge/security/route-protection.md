---
type: Security Control
title: Route Protection Is the Host Application's Duty
description: The service provider registers no routes and no middleware, so authentication and authorization for the CRUD API must come from the host application.
resource: routes/api.php
tags: [security, authorization, middleware, api]
timestamp: 2026-09-04T00:00:00Z
---

# Schema

`ValidationServiceProvider::boot()` calls `loadMigrationsFrom()` and nothing
else. It does not call `loadRoutesFrom()`.

The consequence: `routes/api.php` is inert until the host application includes
it, and the host chooses the prefix and the middleware stack when it does so.

## The exposure

The route file declares full CRUD over all three tables with **no** middleware
of its own. If a host registers it without an auth middleware, any caller can:

- create a `Rule` with an arbitrary `rule_type` and `parameters`
- change the `order_number` or `active_status` of any join row
- delete any validator, which cascades and removes its join rows
- call `POST /validators/validate` for any validator

Because validation logic lives in data, write access to these tables is
equivalent to changing the host application's validation behavior without a
deployment. Treat these routes as an administrative surface.

## Required control

The host application must register the routes behind authentication and an
authorization policy. For example:

```php
Route::middleware(['api', 'auth:sanctum', 'can:manage-validation'])
    ->prefix('api')
    ->group(base_path('vendor/threeleaf/validation-engine/routes/api.php'));
```

The package's controllers extend a base `Controller` that uses Laravel's
`AuthorizesRequests` trait, so a host may also add `$this->authorize(...)` calls
through its own subclasses. No controller in this package calls `authorize()`
itself.

## Read paths are equally open

`index` on all three controllers returns `->all()` with no filter and no
pagination. A single `GET` returns the whole table.

# Examples

The test suite registers the routes with the `api` middleware group only:

```php
Route::middleware('api')
    ->prefix('api')
    ->group(__DIR__ . '/../../routes/api.php');
```

That is correct for tests and is **not** a template for production.

# Citations

- Verified 2026-09-04 against git HEAD — `src/Providers/ValidationServiceProvider.php` `boot()` calls only `loadMigrationsFrom`
- Verified 2026-09-04 against git HEAD — `routes/api.php` declares no middleware
- Verified 2026-09-04 against git HEAD — no controller in `src/Http/Controllers/` calls `authorize()`
- Verified 2026-09-04 against git HEAD — `index` methods return `Model::all()` unfiltered
- `tests/Feature/TestCase.php` (`setUpRoutes`)
