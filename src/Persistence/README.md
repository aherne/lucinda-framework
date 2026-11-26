# Internal persistence API

Persistence classes define generated SQL operations, validated row builders and
immutable logical records. Project DAOs coordinate these operations and return
application values. Controllers, resolvers and templates must not import these
internal classes or receive their entities.

[Queries](#queries) · [Builders](#builders) · [Entities](#entities) ·
[Connections and transactions](#connections-and-transactions) ·
[Schemas and tests](#schemas-and-tests)

## Queries

Each concrete query is one operation instance. Its constructor captures explicit
runtime inputs, builds clauses through `lucinda/queries`, and stores typed bound
parameters. `getSql()` exposes the generated statement, `getParameters()`
exposes
its bindings, and `isSelection()` identifies read operations.

Operations cover account credentials and identities, OAuth2 approval and state,
resource permissions and roles, MFA enrollment, session payloads and migration
records. Vendor-selecting operations receive a `SqlVendor` enum and delegate to
MySQL, PostgreSQL, or SQLite implementations. PostgreSQL session writes bind
independent streams for the insertion and replacement payload parameters.

SQL identifiers are fixed by operation definitions. Usernames, identity IDs,
provider names, payloads and other runtime values are bound. Query definitions
never open connections or execute statements.

## Builders

A builder's `build(Row $row)` validates the selected projection through typed
row
accessors and returns its named entity. Missing or malformed required columns
raise a validation exception. Session builders materialize strings or streamed
bytes. Migration builders map stored status codes to the migration enum and
reject unknown codes.

The selected column names are part of each builder's input contract; query
projections must keep their corresponding aliases. Builders do not execute SQL.

## Entities

Entities represent internal logical records: credentials, resolved identities,
approval decisions, resource policy, roles, MFA enrollment, serialized session
bytes and migration results. They have private typed fields, explicit accessors,
and final readonly lifetimes.

Entities preserve validated values without accessing storage. DAOs convert them
to documented outward values, including arrays of role names and maps of
migration
statuses. Entities must not escape through nested presentation collections.

## Connections and transactions

Application setup obtains a connection or atomic adapter from the SQL or NoSQL
data-source listener's returned provider facet. It constructs the framework
executor and injects it into each DAO. Executors own execution rather than XML
loading. NoSQL DAOs do not discover storage through a global registry.

Coordinators explicitly begin, commit and roll back transactions on their shared
SQL executor. OAuth2 provisioning rolls back losing or failed account creation.
Single-use authorization state and throttling require immediate persistence,
while counter and conditional-write predicates prevent replay and lost updates.

Session payload storage and locking have separate contracts. SQL's default lock
shares its executor connection with storage. NoSQL leases and protected payloads
share an atomically replaced record; lost owners cannot write, renew or release
a
successor's lease. APCu ownership is local to its shared cache domain.

## Schemas and tests

Root [db-mysql.sql](../../db-mysql.sql),
[db-sqlite.sql](../../db-sqlite.sql), and
[db-postgresql.sql](../../db-postgresql.sql) define fresh schemas. Runtime DAOs
do
not install or upgrade application schemas.

Run these checks after changes:

```sh
php test.php
php tools/check-architecture.php
php tests/Persistence/run.php
php tests/Persistence/schemas.php
```

The persistence runner reports when external database checks are skipped. Native
storage tests require an isolated `framework_dao_test_*` database and unique
NoSQL test namespaces. See the [testing guide](../../tests/README.md) for
settings,
manual initialization, Redis/Valkey and Memcached endpoints, shared-cache APCu
checks and cleanup guarantees.
