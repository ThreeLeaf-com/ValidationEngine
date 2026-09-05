# Data Model

Three tables, all created by a single migration and all prefixed `v_`. The
prefix is `ValidatorEngineConstants::TABLE_PREFIX`.

| Table                                           | Primary key                | Purpose                     |
| ----------------------------------------------- | -------------------------- | --------------------------- |
| [`v_rules`](rules-table.md)                     | `rule_id` (UUID)           | One validation check        |
| [`v_validators`](validators-table.md)           | `validator_id` (UUID)      | A named group of rules      |
| [`v_validator_rules`](validator-rules-table.md) | `validator_id` + `rule_id` | Join, with order and status |

Both UUID tables use Eloquent's `HasUuids` trait, so the application generates
the key rather than the database. The join table uses the project's own
[`HasCompositeKey`](/features/composite-key-models.md) trait.

Every table carries `created_at` and `updated_at` timestamps with database
defaults (`useCurrent()`, and `useCurrentOnUpdate()` for the update column).

The join table declares both foreign keys with `onDelete('cascade')`. Deleting a
validator or a rule therefore removes its join rows. SQLite does not enforce
foreign keys unless they are switched on, which the test base class does — see
[Testing Strategy](/testing/strategy.md).

See [System Overview](/architecture/system-overview.md) for how these tables are
read at validation time.
