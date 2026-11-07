# XML configuration templates

The project ships 20 `.xml.dist` templates covering the configuration read by `index.php`, `src/EventListeners`, and `src/ViewResolvers`, including the referenced MVC sections. Copy a template to the same path without `.dist` to activate it. Existing runtime `.xml` files are intentionally left in place; compare and merge them rather than overwriting local configuration.

Start with `stdout.xml.dist` and `stderr.xml.dist` in the project root. Materialize their application, routes, resolvers, and templating references, plus both STDERR logger files. Reference paths omit `.xml` and are relative to the root document. The other consumers load fixed paths ending in `.xml`; none falls back to `.xml.dist` automatically.

| Template | Consumer / when to use |
| --- | --- |
| `../stdout.xml.dist` | HTTP and CLI front controllers in `index.php` |
| `../stderr.xml.dist` | Error front controller in `index.php` |
| `stdout/application.xml.dist` | Application defaults and MVC template locations |
| `stdout/routes.xml.dist` | HTTP routes, parameter validation, CLI commands, optional route roles |
| `stderr/application.xml.dist` | Error defaults and fatal error format selection |
| `stderr/routes.xml.dist` | Exception classification, HTTP statuses and CLI exit codes |
| `resolvers.xml.dist` | Shared HTML, JSON and console response resolvers |
| `templating.xml.dist` | HTML template compiler |
| `templating-console.xml.dist` | Console template compiler |
| `stderr/loggers.xml.dist` | Required ordinary error reporting |
| `stderr/loggers-fatal.xml.dist` | Required emergency error reporting |
| `stdout/loggers.xml.dist` | Optional HTTP / console logging listeners |
| `stdout/sql.xml.dist` | SQL data source listener and SQL-backed DAOs |
| `stdout/nosql.xml.dist` | NoSQL data source listener and cache-backed services |
| `stdout/headers.xml.dist` | HTTP cache / CORS policies and cacheable implementation |
| `stdout/internationalization.xml.dist` | Locale detection and translation dictionaries |
| `stdout/security.xml.dist` | Authentication, authorization, persistence and optional MFA |
| `stdout/oauth2.xml.dist` | Optional provider clients; also enable the path in Security |
| `stdout/session.xml.dist` | Optional session reference in `stdout.xml` |
| `stdout/cookies.xml.dist` | Optional cookie reference in `stdout.xml` |

Each file documents required attributes, defaults, alternatives and prerequisites. Commented XML examples are disabled: enable only the alternatives you need, respecting mutually exclusive sections. `App\*` classes are illustrative placeholders, not shipped implementations; replace them with autoloadable classes implementing the documented interfaces. Configure credentials and secrets in private deployment copies. These parsers do not interpolate environment variables or select nested environment sections. `ENVIRONMENT` remains required by `index.php`.

Register optional listeners through `EventScheduler` in PHP. Load SQL / NoSQL before their dependent services; start the shared session before session authentication, or enable MVC `auto_start`. Register `HttpHeaders` before CORS and cache listeners. Create writable compilation/log directories and translation dictionaries before enabling their consumers. Paths in service settings normally resolve from the process working directory; launch from the project root.

The templates cover configuration, not unfinished PHP integration. Two existing boundaries require alignment before their features can run end to end:

- MVC `ViewDetector` produces template paths with extensions, while the HTML and console resolvers pass them directly to a compiler expecting names without extensions.
- MFA matches setup/challenge request URIs verbatim, while the project's security listener expects context-prefixed callback paths and `RequestBinder` supplies relative route names.

Security templates now reference the supplied DAO implementations. Install `../db.sql` and configure dependencies/secrets before enabling them. See `../src/Persistence/README.md` for implementation choices, Redis requirements and architecture checks.
