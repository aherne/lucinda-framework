# Persistence objects and responsibilities

Use imperative PHP throughout this layer. There are no static methods,
closures, first-class callables, mapping callbacks or transaction callbacks.
Ask the user before introducing static behavior. PSR-4 names match paths;
PSR-12 formatting uses four spaces and lines no longer than 80 characters.
SQL expressions stay whole in single-line string literals, even when they
exceed that limit. Do not split expressions merely to satisfy line width.
Class fields are private by default and protected when a child class needs
direct access. Public fields are forbidden, including readonly and promoted
fields. External access uses explicit typed getters. Entities follow their
natural logical structure independently of presentation requirements.

- `Queries/<Domain>/<Operation>` owns one statement generated through
  `lucinda/queries` and its independent bound values. Construct the operation
  instead of invoking a factory method. Clauses have focused assembly methods;
  constructors coordinate them and assign protected statement/parameter state.
  Field aliases use Fields::add(expression, alias). Identifiers come from code;
  runtime
  values are bound. Root `db.sql` remains the manual installation schema.
- `SqlExecutor` owns one connection and explicitly begins, commits and rolls
  back transactions. `select()` returns materialized `Results\Lists`;
  `write()` returns `Results\Scalar` metadata through `getAffectedRows()` and
  `getInsertId()`. It never receives or invokes a builder.
- `Results\Lists` owns its internal `Row` projections. A DAO obtains a row,
  constructs its specific builder and explicitly invokes `build()`.
- `Builders` own the particular row being converted. Required columns are
  validated, and malformed projections fail instead of inventing defaults.
- `Entities` are final readonly logical records with private typed fields
  and explicit accessors. They never
  implement ArrayAccess, access storage or reach controllers and views.
- DAOs implement framework contracts and coordinate complete operations.
  Their public results are explicit application values. Collection element
  types must be documented. Persistence entities remain within the DAO flow.
- `RedisExecutor` owns a Redis connection and executes `Redis/Script` objects.
  It contains no session, migration, throttling or HTTP cache policy.
- `Redis/SessionStore` owns its current lease and session payload lifecycle.
  `Redis/MigrationStore` owns a status ledger. `Redis/ThrottleStore` owns one
  authentication scope's failure policy. `Redis/RepresentationStore` owns
  retained representation timestamps. Each constructs its own atomic scripts.
- `Presentation/DataBoundary` owns one outgoing payload and its graph traversal
  state. Resolvers construct it and call `validate()`. Nested persistence
  objects, private fields and serialized output are checked before rendering.

The installed API packages supply contracts, not concrete database DAOs.
Application implementations must follow these rules; do not patch ignored
vendor copies to bypass them.

# Construction and connection ownership

The security API constructs configured DAOs without constructor arguments.
Their default constructors create an executor that lazily reads
`xml/stdout/sql.xml` or `xml/stdout/nosql.xml`. The executor retains its provider
and connection; it does not consult a static service registry.

For an explicit dependency graph, inject a `SqlExecutor` or `RedisExecutor`
into each DAO. Reuse the same SQL executor for operations that must share a
connection. An injected SQL connection can come from application setup.
For a named configured server, construct `new SqlExecutor(null, 'server')` or
`new RedisExecutor(null, null, 'server')`. The optional data-source listeners
remain available for other framework integrations; the default DAO graph and
migration launcher do not require their registry entries.

Coordinate a transaction with `begin()`, an imperative try/catch block,
`commit()` and `rollback()`. Nesting is rejected. Single-use state, counter
consumption and throttler writes require autocommit without an application
transaction. Session advisory locks require nonpersistent MySQL connections.

# Storage setup and choices

Install `db.sql` manually in a fresh MySQL 8+ database. Each table names its
owning DAOs. There are no default accounts or seeded passwords.

Choose `UserAuthorization` for direct grants or `RoleAuthorization` for grants
inherited through roles. Both check HTTP methods; missing grants deny access.
Choose `UserRoles` for database roles or `SimpleUserRoles` for GUESTS/MEMBERS.
Public resource policy is independent of authenticated user grants.

OAuth2 configuration selects `OAuth2Login`, `OAuth2AutomaticProvisioning` or
`OAuth2ApprovalProvisioning`. Lookup is read-only. Automatic provisioning uses
one explicit transaction for the account and identity link, including rollback
for concurrent duplicate identities. Email never implies account linking.
Approval requests are idempotent; administration must explicitly create the
approved identity transactionally. Provider tokens are not persisted. Logout
lets the security API clear authentication state. Periodically call the state
DAO's `clearExpired()` method.

MFA secrets use Sodium secretbox. Supply `MFA_ENCRYPTION_KEY` as base64 of 32
private random bytes outside source control and back it up separately.
Changing the key requires re-encrypting stored secrets. Set users.mfa_required
deliberately. Setup secrets expire after 300 seconds by default; enabling MFA
preserves the consumed confirmation counter. The existing listener/request MFA
path mismatch remains documented in the XML templates and is outside this
persistence refactor.

SQL and Redis throttlers default to five failures per identity/IP in a fixed
900-second window. IP representations are canonicalized, and form/MFA scopes
are distinct. Schedule SQL window cleanup; Redis expires windows atomically.

NoSQL implementations require Redis or compatible Valkey. Set a distinct
`PERSISTENCE_NAMESPACE` per app/environment; the default is `framework:`.
Sessions use 60-second leases and wait at most ten seconds to acquire a lease.
Writes after lease expiry fail. Close sessions before long-running work.
Migration records are independent Redis hash fields without a TTL.

# Verification

Run `php tools/check-architecture.php` for dependency, imperative style,
documentation and 80-character checks. Query string contents are exempt from
the width check so SQL expressions remain whole. The checker rejects known
dependency and syntax violations; dynamic code still requires review. Do not
suppress a boundary failure merely to pass the check.

Run `php tests/Persistence/run.php` for unit checks. Storage integration also
requires `PERSISTENCE_TEST_DATABASE` naming an isolated `framework_dao_test_*`
database initialized from db.sql. Supply `PERSISTENCE_TEST_USER`,
`PERSISTENCE_TEST_PASSWORD` and optionally `PERSISTENCE_TEST_HOST`.
`PERSISTENCE_TEST_REDIS_PORT` enables Redis checks with a random test namespace.
Never use an application database for these checks.

# SQL session locking

`SQLSessionHandler` supplies payload persistence only. Configure
`Lucinda\Project\DAO\LockingSQLSessionHandler` for concurrent PHP sessions.
It extends the storage handler, implements the `Lock` interface, and delegates
ownership to an injected strategy.
Its default `SqlLock` uses the same executor and connection as session storage.
Alternative lock implementations can be injected without changing payload SQL.
Ownership is retained from the first read/write/delete until `close()`.
