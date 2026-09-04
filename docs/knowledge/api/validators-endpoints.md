---
type: API Endpoint Group
title: Validators Endpoints
description: apiResource CRUD over v_validators plus the POST /validators/validate action that runs a validator against a data payload.
resource: src/Http/Controllers/Api/ValidatorController.php
tags: [api, rest, validators, validation]
timestamp: 2026-09-04T00:00:00Z
---

# Schema

| Method   | Path                      | Controller method | Success response             |
| -------- | ------------------------- | ----------------- | ---------------------------- |
| `GET`    | `/validators`             | `index`           | `200` with all validators    |
| `POST`   | `/validators`             | `store`           | `201` with the new validator |
| `GET`    | `/validators/{validator}` | `show`            | `200` with the validator     |
| `PUT`    | `/validators/{validator}` | `update`          | `200` with the validator     |
| `DELETE` | `/validators/{validator}` | `destroy`         | `204` with an empty body     |
| `POST`   | `/validators/validate`    | `doValidation`    | `200` or `422`, see below    |

`ValidatorController` takes a `ValidatorService` through its constructor.

## Route order

`Route::post('/validators/validate', ...)` is declared **after**
`Route::apiResource('validators', ...)` in `routes/api.php`. The resource route
`POST /validators` does not collide with `POST /validators/validate`, because the
two paths differ in segment count.

## The validate action

`doValidation` reads `validator_id` from the request body, then passes the
**whole request body** as the data to validate. The `validator_id` field is
therefore also present in the validated data.

`runValidatorById` matches the value against either `validator_id` or `name`,
and requires `active_status` to be `Active`.

| Outcome                                    | Status | Body                                                   |
| ------------------------------------------ | ------ | ------------------------------------------------------ |
| Validator found and all rules pass         | `200`  | `{"success": true}`                                    |
| Rules fail, or no active validator matches | `422`  | `{"success": false}`                                   |
| Any exception is thrown                    | `500`  | `{"success": false, "error": "Internal Server Error"}` |

A `422` therefore does not distinguish "the data is invalid" from "no such
validator". The exception path logs the message with `Log::error` and returns a
generic string rather than the exception text.

`doValidation` uses the base `Request` class, so it applies no form-request
validation of its own. A request with no `validator_id` reaches the service as
`null`.

## Request validation for CRUD

`store` and `update` take `ValidatorRequest`:

| Field         | Rules                                                     |
| ------------- | --------------------------------------------------------- |
| `name`        | `required`, `string`, `max:255`, unique on `v_validators` |
| `description` | `nullable\|string\|max:1000`                              |

On `PUT` or `PATCH` the uniqueness rule is rebuilt with `->ignore($validatorId)`,
taken from the `validator` route parameter, so a validator can keep its own name
on update.

# Examples

```bash
curl -X POST http://localhost/api/validators/validate \
  -H 'Content-Type: application/json' \
  -d '{"validator_id": "StateAndTimeValidator", "active_status": "Active"}'
```

# Citations

- Verified 2026-09-04 against git HEAD — `src/Http/Controllers/Api/ValidatorController.php` `doValidation` status codes 200/422/500
- Verified 2026-09-04 against git HEAD — `doValidation` passes `$request->all()` as the data payload
- Verified 2026-09-04 against git HEAD — `src/Http/Requests/ValidatorRequest.php` rebuilds the unique rule with `ignore()` on PUT/PATCH
- Verified 2026-09-04 against git HEAD — `routes/api.php` declares `validate` after the resource routes
- `tests/Feature/Http/Controllers/Api/ValidatorControllerTest.php`
