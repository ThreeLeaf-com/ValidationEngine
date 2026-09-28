---
type: Style Guide
title: Style Conventions
description: Comment style, PHPDoc requirements, migration column comments, and embedded OpenAPI annotation rules as practiced in this package.
resource: .cursorrules
tags: [style, phpdoc, openapi, conventions, documentation]
timestamp: 2026-09-28T00:00:00Z
---

# Schema

The authoritative rule set is [`.cursorrules`](../../../.cursorrules) at the
repository root. This concept records the conventions that the package's own
source actually follows. Terms used across the bundle are defined in the
[glossary](/style/glossary.md).

## Comments

Inline explanatory comments use the block form, not `//`:

```php
/* Enable foreign key constraints for SQLite */
DB::statement('PRAGMA foreign_keys=ON;');
```

Comments state intent. They do not restate the statement below them.

## PHPDoc

- Every public method carries a docblock with `@param` and `@return`.
- Every model documents its columns with `@property` lines that give the type
  and a description.
- Relations are documented as `@property-read` with the relation's generic type,
  for example `@property-read BelongsTo<Validator> $validator`.
- Models add `@mixin Builder` plus `@method static` lines for `create`, `find`,
  and `query`, so static calls resolve in an IDE.
- `{@link ClassName}` is used to cross-reference types in prose.
- Thrown exceptions are declared with `@throws`.

## Migrations

Every table and every column carries a comment. The column comment mirrors the
model's `@property` description for the same field.

```php
$table->comment('Stores individual validation rules with their configurations.');
$table->uuid('rule_id')->primary()->comment('The unique identifier for the Rule.');
```

Timestamps use the standard pair:

```php
$table->timestamp(Model::CREATED_AT)->useCurrent()->comment('...');
$table->timestamp(Model::UPDATED_AT)->useCurrent()->useCurrentOnUpdate()->comment('...');
```

`up()` and `down()` each get a single-line docblock.

## OpenAPI annotations

The API specification is not a separate file. It lives in `@OA\` annotations
inside model and controller docblocks, and is extracted by
`util/generate-open-api.php`.

- Models declare `@OA\Schema` with a `schema` name matching the class name.
- Every property declares `type`, `description`, and an `example`.
- UUID properties add `format="uuid"`.
- A property that refers to another schema uses `ref="#/components/schemas/..."`
  rather than repeating the definition.
- `required={...}` lists the non-nullable fields.

An annotation syntax error breaks the Composer `post-install-cmd` hook, because
that hook runs the generator. Keep annotations valid.

## Naming

- Tables are prefixed `v_` through `ValidatorEngineConstants::TABLE_PREFIX`.
  Do not write the prefix as a literal.
- Models expose `TABLE_NAME` and `PRIMARY_KEY` constants, and reference those
  constants rather than repeating strings in requests and relations.
- Rule classes end in `Rule` and live in `src/Rules`.

## Markdown

Markdown under `docs/` is formatted with Prettier before commit. The repository
has no `package.json` and no Prettier configuration, so run it through `npx`
with Prettier's defaults rather than expecting a local dependency:

```bash
npx prettier --write "docs/**/*.md"
```

Prettier reformats tables to align their columns. A `|` inside inline code — a
Laravel rule string such as `required\|string\|max:255` — must be escaped, or
Prettier reads it as a cell separator and splits the row.

## Permanent documentation stands on its own: no tickets, PRs, local links, or personal PII

Code comments, docblocks, `docs/knowledge/` concepts, User Guides, DevOps runbooks, and any other committed documentation must make sense to a reader who has nothing but the repository. Do not put any of these in them:

- **Issue IDs or issue tracker links:** GitHub issue numbers (`#123`), Jira keys (`TB-###`, `TL-123`), or direct links to issues.
- **Pull request numbers or links:** GitHub PR numbers (`#456`), PR URLs, or review threads.
- **Commit hashes:** Short or full SHAs (`abc1234`). Cite the source file and line range the claim rests on instead.
- **Links to ephemeral or local-only material:** gitignored folders such as `work-items/`, scratchpad or temp paths, CI run URLs that expire, session identifiers. Anything that will not resolve for a future reader on a clean checkout.
- **Personal PII (Personally Identifiable Information):** Individual people's real names, personal email addresses, phone numbers, Slack IDs, or @-handles. Name the role or actor instead ("the team lead", "the assignee", "the reporter", "the customer"). Conventional placeholders (`John Doe`, `Jane Smith`, `user@example.com`) are allowed for illustrative examples.

Links to public external documentation, such as a framework's, language's, or vendor's official docs (e.g., Laravel, PHP, Python, Swift, MDN, RFCs), are allowed and encouraged: they resolve for every reader and outlive any single ticket.

State the fact itself, and explain _why_ in full. If a reader needs history, `git log` and `git blame` on the line already carry the commit and its ticket key, and the ticket and pull request are where discussion belongs. Cite evidence as source paths and line ranges. A reference that only makes sense to someone who remembers the ticket is noise to everyone else.

### Live routing exception: scheduled TODO and FIXME

A `TODO` or `FIXME` for work that is already scheduled may name the ticket that will do it, because there the key is live routing information rather than history. Write it in exactly this form, so every exception can be found with one search:
- GitHub issues: `// TODO(#123): problem + brief plan` or `FIXME(#123): …`
- Jira issues: `// TODO(TB-123): problem + brief plan` or `FIXME(TB-123): …`
- Test impasse (`test-diagnosis` skill): `FIXME(test-diagnosis): symptom + plan` (ticket lives in `TEST_PUNCH_LIST.md` / work-item, not in the comment).

Every `TODO`/`FIXME` must carry an explanation in addition to the ticket reference. A bare ticket ID or an unticketed `TODO` both fail review. An existing `TODO` or `FIXME` in another form is corrected when its line is next edited.

### Where tracking and attribution belong

Commit messages, pull request titles/descriptions, issue tracker tickets, and gitignored `work-items/` folders are where ticket keys and author discussions belong; this rule does not apply to them.

Ownership metadata files whose explicit purpose requires identity (`CODEOWNERS`, root `README.md` attribution, `agents/.agent-config.json` machine config, package lockfiles) are exempt for PII. Placeholder keys used to illustrate a format (such as `#${TICKET}` or `[TB-123] fix: …` in commit conventions) are not references.

### Fix as you go

Existing documentation predates this rule and is not swept. Whenever you edit a line that carries one of these references or personal PII, remove it from that line and reword so the line still reads correctly. Leave lines you did not otherwise touch alone.

# Examples

A model property block, as written in `src/Models/ValidatorRule.php`:

```php
/**
 * @property string       $validator_id  The unique ID of the {@link Validator}
 * @property int          $order_number  The order in which the rule should be applied
 * @property ActiveStatus $active_status Whether the rule is currently active
 */
```

# Citations

- Verified 2026-09-04 against git HEAD — block-comment style in `tests/Feature/TestCase.php` and `src/Services/RuleService.php`
- Verified 2026-09-04 against git HEAD — `@property`, `@property-read`, `@mixin`, and `@method static` usage in `src/Models/`
- Verified 2026-09-04 against git HEAD — table and column comments in `database/migrations/2024_10_10_000000_create_validation_engine_tables.php`
- Verified 2026-09-04 against git HEAD — `@OA\Schema` blocks in `src/Models/Rule.php` and `src/Models/ValidatorRule.php`
- Verified 2026-09-04 against git HEAD — `composer.json` `post-install-cmd` runs `util/generate-open-api.php`
- `.cursorrules`
