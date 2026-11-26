# Project application API

`Lucinda\Project` contains the application adapters joining the MVC, security,
headers, logging, persistence, and rendering APIs. MVC constructs listeners and
controllers from their typed dependencies. Application code owns configuration
and the lifetime of injected storage; persistence records remain internal.

[Listeners](#listeners) · [Facets](#facets) · [Error handling](#error-handling)
·
[Rendering and caching](#rendering-and-caching) · [Persistence](#persistence) ·
[Verification](#verification)

## Listeners

Listeners receive request facets or explicit XML and publish their result
through
MVC. XML paths are relative to the project root and omit the `.xml` extension in
`XML_PATH` constants. See the [configuration reference](../xml/README.md).

| Listener | Input | Result |
| --- | --- | --- |
| `SQLDataSource` | Optional SQL XML | Lazy SQL provider facet |
| `NoSQLDataSource` | Optional NoSQL XML | Lazy atomic-driver provider facet |
| `FileDB` | Optional file database XML | File database wrapper facet |
| `Logging` | HTTP request and logger XML | Request logger facet |
| `Console\Logging` | Console request and logger XML | Command logger facet |
| `HttpHeaders` | HTTP request, route, XML | Shared headers policy facet |
| `HttpCors` | Request and headers facet | Continuation or OPTIONS response |
| `HttpCaching` | Request, headers, body | Cache headers or termination |
| `Localization` | HTTP request and XML | Registered translation reader |
| `Security` | Request, route, cookies, session | Security facets or redirect |
| `Error` | Validated HTTP route | Matching STDERR response format |
| `Console\Error` | Active STDERR handler | Console error format |

Security requires its separate XML configuration. Session persistence requires
an
already active injected MVC session; token persistence does not. OAuth2 provider
and XML role loading are disabled by the listener's path-selection methods until
an application explicitly enables them. Configured security DAO construction
must be adapted to supply the storage dependencies required by project DAOs;
this documentation and test work does not introduce service-registry discovery.

## Facets

Facets publish request-local application context through typed accessors.

- `Security` exposes the authenticated integer or string account ID, bearer
  token, CSRF token, and optional REST callback. Values initially default to
null.
- `MultiFactor` exposes the selected enrollment or challenge packet, initially
  null. Its setup secret and provisioning URI are sensitive form data.
- `Loggers` and `Headers` share their initialized wrappers with consumers and
  initially expose null.
- `FileDB` requires its listener to set a wrapper before retrieval.

Consumers obtain these values through `get*()` methods. DAO entities are never
facet values or application presentation data.

## Error handling

`STDERR\DisplayErrors` enables diagnostics by default. Its policy must be
adapted
by applications that need public error responses.

`STDERR\ErrorReporter` takes server metadata and two logger XML paths without
extensions. It sends INTERNAL failures and any supplied original exception to
the fatal logger; other loggable classifications use the application logger.
Missing mandatory logging configuration terminates with a fallback message.

`Controllers\Error` receives the display policy, handled exception request and
validated error route. `run()` returns a `View` for HTML, JSON, or console
output.
Diagnostic data retains SQL query context and original template locations when
available. The public-error branches return generic content without diagnostics.

`STDERR\FatalErrorResolver` takes an application XML path without extension and
returns fallback diagnostics from `resolve()`. CLI and non-HTML output use JSON;
HTTP HTML output uses minimal templates with escaped diagnostic values. It does
not invoke the normal templating pipeline. Missing required files terminate.

## Rendering and caching

Every view resolver accepts `Lucinda\MVC\Response\View` and returns a string.

| Resolver | View input | String output |
| --- | --- | --- |
| `Html` | Template name and application data | Compiled HTML |
| `Console` | Template name and application data | ANSI console text |
| `Json` | Application data | JSON with `status` and `body` |

`Json` marks calls originating in errors-mvc with the error status.

`Cacheables\Etag` takes the HTTP request and rendered response bytes. Its entity
tag varies with the server host and body; its Last-Modified timestamp stays
zero.

`Cacheables\Date` uses the original `AbstractCacheable` lifecycle and resolves
timestamps through a representation store. Its original zero-argument
`NoSqlExecutor` construction does not satisfy the executor's current required
storage argument. This limitation remains until a separate integration change
supplies storage; Date should not currently be enabled.

## Persistence

DAOs receive a `SqlExecutor` or `NoSqlExecutor` through construction. Executors
are framework-owned execution objects. Project query classes generate SQL using
`lucinda/queries`; their runtime parameters retain explicit SQL binding types.

| DAO group | Input | Output |
| --- | --- | --- |
| Login and identities | Credentials or provider identity | Account ID or null |
| OAuth2 approvals | Provider identity | Pending or rejected enum |
| OAuth2 states | State, provider, deadline, proof | Acceptance boolean |
| Permissions | Route, resource, account, method | ID or permission boolean |
| Roles | Account ID or null | Array of role names |
| MFA | Account, counters, secrets | Policy, label, secret, acceptance |
| Throttling | Subject, client, policy | Blocked boolean |
| Sessions | Session ID and bytes | Payload or operation result |
| Migrations | Migration class and status | Class-to-status map |

Builders validate selected rows and produce internal entities. Entities are
final
readonly records with private typed fields and explicit accessors. They must not
reach controllers, views or templates, even inside collections.

`SQLSessionHandler` provides payload storage. `LockingSQLSessionHandler` extends
it with a separately injected lock; its default SQL lock needs MySQL. NoSQL
session handlers retain expiring lease ownership through close and reject stale
writes. Native-backend guarantees require the integration tests below.

Fresh schemas are maintained in root `db-mysql.sql`, `db-sqlite.sql`, and
`db-postgresql.sql`. Applying them to application databases is an explicit
installation step. Tests never install them in application databases.

## Verification

Run `php test.php` for isolated application behavior tests and
`php tools/check-architecture.php` for the existing architecture check. The
presentation data checker lives in `tools/DataBoundary.php` and is loaded
explicitly by its tests. Resolvers perform no code verification.

The proposed verification API and `code_checker.php` / `site_checker.php` remain
future work. Code checking will infer configurable analysis tools behind its
public API; site checking will measure performance, caching and related
behavior.

See [the testing guide](../tests/README.md) for assertions, isolation
guarantees,
commands, and external integration settings.
