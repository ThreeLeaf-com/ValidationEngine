---
okf_version: "0.1"
---

# Technical Manual

OKF v0.1 knowledge bundle for the ThreeLeaf ValidationEngine Laravel package. See
each subdirectory index for navigation.

This bundle is the technical layer of the four-layer documentation model. The
end-user layer is [../user-guide/README.md](../user-guide/README.md). The
operations layer is [../devops/README.md](../devops/README.md).

# Architecture

- [System Overview](architecture/system-overview.md) - Package structure, the rule/validator/join model, and the request path through the service layer
- [Architecture Decision Records](architecture/decisions/index.md) - Lightweight ADRs for durable choices

# Security

- [Security Overview](security/index.md) - Index of the security concepts below
- [Route Protection Is the Host Application's Duty](security/route-protection.md) - The package registers no authentication or authorization middleware
- [Rule Class Instantiation From Stored Data](security/rule-class-instantiation.md) - `rule_type` names a class that the engine instantiates; the two checks that bound it

# Data

- [Data Model](data/models/index.md) - The three tables and their keys
- [`v_rules`](data/models/rules-table.md) - One validation rule and its JSON parameters
- [`v_validators`](data/models/validators-table.md) - A named, ordered group of rules
- [`v_validator_rules`](data/models/validator-rules-table.md) - Composite-key join between validators and rules

# API

- [HTTP API](api/index.md) - Route file, prefix, and shared conventions
- [Rules Endpoints](api/rules-endpoints.md) - `apiResource` CRUD over `v_rules`
- [Validators Endpoints](api/validators-endpoints.md) - CRUD plus the `validate` action
- [Validator Rules Endpoints](api/validator-rules-endpoints.md) - Two-segment composite-key routes

# Features

See [features/index.md](features/index.md).

- [Rule Types](features/rule-types.md) - The eight concrete rule classes and their constructor parameters
- [Rule Persistence and Instantiation](features/rule-persistence-and-instantiation.md) - How a database row becomes a Laravel `ValidationRule`
- [Composite-Key Models](features/composite-key-models.md) - `HasCompositeKey` and why Eloquent needs it

# Testing

- [Testing Strategy](testing/strategy.md) - Testbench, the in-memory SQLite database, suites, and coverage

# Style

- [Style Conventions](style/conventions.md) - Comment style, PHPDoc, and OpenAPI annotation rules
- [Project Glossary](style/glossary.md) - Shared terms used across this bundle
