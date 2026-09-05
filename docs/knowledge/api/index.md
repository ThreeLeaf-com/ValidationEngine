# HTTP API

All routes are declared in [`routes/api.php`](../../../routes/api.php).

**The package does not register this file.** `ValidationServiceProvider` loads
migrations only. The host application must include the route file itself, and
chooses the prefix and middleware when it does so. The test base class registers
the routes under the `api` middleware group with an `api` prefix, which is the
arrangement the endpoint concepts below assume.

Because the host chooses the middleware, the package ships no authentication and
no authorization. See
[Route Protection Is the Host Application's Duty](/security/route-protection.md).

| Group                                           | Routes                                      |
| ----------------------------------------------- | ------------------------------------------- |
| [Rules](rules-endpoints.md)                     | `apiResource('rules')`                      |
| [Validators](validators-endpoints.md)           | `apiResource('validators')` plus `validate` |
| [Validator Rules](validator-rules-endpoints.md) | Explicit two-segment composite-key routes   |

Every controller returns `JsonResponse`. Controllers extend the package's own
`Http\Controllers\Controller`, which adds Laravel's `AuthorizesRequests` and
`ValidatesRequests` traits.

Request bodies are validated by form request classes in `src/Http/Requests`,
so a malformed body produces Laravel's standard `422` response before the
controller runs.

OpenAPI annotations are embedded in the model and controller docblocks. See
[Style Conventions](/style/conventions.md) for the annotation rules and
[the DevOps guide](../../devops/README.md) for the generator command.
