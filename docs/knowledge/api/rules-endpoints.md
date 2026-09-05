---
type: API Endpoint Group
title: Rules Endpoints
description: apiResource CRUD over the v_rules table, with route-model binding on the rule UUID.
resource: src/Http/Controllers/Api/RuleController.php
tags: [api, rest, rules]
timestamp: 2026-09-04T00:00:00Z
---

# Schema

Declared as `Route::apiResource('rules', RuleController::class)`, which produces
the five standard REST routes. Paths below omit the host application's own
prefix.

| Method   | Path            | Controller method | Success response         |
| -------- | --------------- | ----------------- | ------------------------ |
| `GET`    | `/rules`        | `index`           | `200` with all rules     |
| `POST`   | `/rules`        | `store`           | `201` with the new rule  |
| `GET`    | `/rules/{rule}` | `show`            | `200` with the rule      |
| `PUT`    | `/rules/{rule}` | `update`          | `200` with the rule      |
| `DELETE` | `/rules/{rule}` | `destroy`         | `204` with an empty body |

`index` returns `Rule::all()` with no pagination and no filter.

`{rule}` is resolved by Laravel route-model binding on the `rule_id` primary key.
An unknown UUID therefore produces `404` before the controller method runs.

## Request validation

`store` and `update` both take `RuleRequest`:

| Field        | Rules                       |
| ------------ | --------------------------- |
| `attribute`  | `required\|string\|max:255` |
| `rule_type`  | `required\|string\|max:255` |
| `parameters` | `nullable\|string`          |

`parameters` is validated as a **string** here, while the model casts the column
to JSON. A client therefore sends a JSON-encoded string in the request body.

`rule_type` is checked only for length at this layer. The class-name check
happens in the `ClassCast` cast when the model is saved — see
[Rule Class Instantiation From Stored Data](/security/rule-class-instantiation.md).

# Examples

```bash
curl -X POST http://localhost/api/rules \
  -H 'Content-Type: application/json' \
  -d '{
        "attribute": "active_status",
        "rule_type": "ThreeLeaf\\ValidationEngine\\Rules\\EnumRule",
        "parameters": "{\"enumClass\":\"ThreeLeaf\\\\ValidationEngine\\\\Enums\\\\ActiveStatus\",\"allowedValues\":[\"Active\"]}"
      }'
```

# Citations

- Verified 2026-09-04 against git HEAD — `routes/api.php` declares `apiResource('rules', ...)`
- Verified 2026-09-04 against git HEAD — `src/Http/Controllers/Api/RuleController.php` returns 201 on store and 204 on destroy
- Verified 2026-09-04 against git HEAD — `src/Http/Requests/RuleRequest.php` field rules
- `tests/Feature/Http/Controllers/Api/RuleControllerTest.php`
