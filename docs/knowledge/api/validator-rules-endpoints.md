---
type: API Endpoint Group
title: Validator Rules Endpoints
description: Explicit two-segment routes over the composite-key join table, with uniqueness rules that scope order_number and rule_id to one validator.
resource: src/Http/Controllers/Api/ValidatorRuleController.php
tags: [api, rest, join-table, composite-key]
timestamp: 2026-09-04T00:00:00Z
---

# Schema

These routes are declared explicitly rather than with `apiResource`, because the
key is composite. They sit under a `validator-rules` prefix.

| Method   | Path                                        | Controller method | Success response       |
| -------- | ------------------------------------------- | ----------------- | ---------------------- |
| `GET`    | `/validator-rules`                          | `index`           | `200` with all rows    |
| `POST`   | `/validator-rules`                          | `store`           | `201` with the new row |
| `GET`    | `/validator-rules/{validator_id}/{rule_id}` | `show`            | `200` with the row     |
| `PUT`    | `/validator-rules/{validator_id}/{rule_id}` | `update`          | `200` with the row     |
| `DELETE` | `/validator-rules/{validator_id}/{rule_id}` | `destroy`         | `204`, empty body      |

There is no route-model binding. `show`, `update`, and `destroy` receive the two
UUIDs as plain `string` arguments and look the row up themselves, because the
composite key defeats Eloquent's single-key `find()` — see
[Composite-Key Models](/features/composite-key-models.md) and
[`v_validator_rules`](/data/models/validator-rules-table.md). These routes ship
with no middleware; see [Route Protection](/security/route-protection.md).

## Request validation

`store` and `update` take `ValidatorRuleRequest`. The rule set differs by method.

Always applied:

| Field           | Rules                                                                                                                          |
| --------------- | ------------------------------------------------------------------------------------------------------------------------------ |
| `order_number`  | `required`, `integer`, `min:1`, unique on `v_validator_rules` **where** `validator_id` matches, ignoring the current `rule_id` |
| `active_status` | `required`, `string`, must be a valid `ActiveStatus` enum value                                                                |

The `order_number` uniqueness is scoped to a single validator, so two different
validators may each have a rule at position 1.

On `POST`:

- `validator_id` — `required`, `uuid`, must `exist` in `v_validators`
- `rule_id` — `required`, `uuid`, must `exist` in `v_rules`, and must be unique
  within this `validator_id`

On `PUT` or `PATCH`:

- `validator_id` — `required`, `uuid`, must exist, and must equal the
  `validator_id` route segment
- `rule_id` — `required`, `uuid`, must exist, and must equal the `rule_id` route
  segment

The `in([...])` checks on update mean the body cannot re-point a join row at a
different validator or rule. To move a rule, delete the row and create a new one.

Note that `min:1` on `order_number` rejects `0`, while the database column
defaults to `1`. A row written directly through the model can hold `0`; a row
written through this API cannot.

# Examples

```bash
curl -X PUT http://localhost/api/validator-rules/$VALIDATOR_ID/$RULE_ID \
  -H 'Content-Type: application/json' \
  -d "{\"validator_id\":\"$VALIDATOR_ID\",\"rule_id\":\"$RULE_ID\",\"order_number\":2,\"active_status\":\"Active\"}"
```

# Citations

- Verified 2026-09-04 against git HEAD — `routes/api.php` `validator-rules` prefix group and its five routes
- Verified 2026-09-04 against git HEAD — `src/Http/Controllers/Api/ValidatorRuleController.php` takes two string arguments, returns 201/204
- Verified 2026-09-04 against git HEAD — `src/Http/Requests/ValidatorRuleRequest.php` method-dependent rules including `min:1` and the `in()` route-match checks
- `tests/Feature/Http/Controllers/Api/ValidatorRuleControllerTest.php`
