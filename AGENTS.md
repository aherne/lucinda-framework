# Persistence architecture

These rules apply to all maintained DAO implementations, including
preimplemented ones.

- Root `db.sql` owns the fresh MySQL schema. Comment each table with the classes
  using it. Do not duplicate DDL in DAO comments or automatically modify
  application databases.
- Generate every runtime SQL statement with `lucinda/queries` under
  `src/Persistence/Queries`, including simple statements. Bind all runtime
  values. No raw SQL fallback or interpolation of user-controlled identifiers.
- Only executors access database clients/drivers. SQLDataSource and
  NoSQLDataSource own XML mapping and register connection providers.
  Zero-argument API construction may use the approved ServiceRegistry::get()
  lookup in SqlExecutor and NoSqlExecutor. Executors must never load XML.
  Framework AtomicDriver adapters own native NoSQL operations and versioning.
  Project persistence must contain no vendor imports or native cache commands.
  Do not restore SQL/NoSQL helpers or expose clients through replacements.
- DAOs implement framework contracts and coordinate operations. Query
  definitions construct queries; builders validate/convert rows; entities
  represent internal logical persistence records. Keep these responsibilities
  separate.
- Entities must be final readonly typed classes, must not implement ArrayAccess,
  and must never reach controllers or views, including nested inside
  collections. DAO public contracts return explicit application values with
  documented collection element types.
- Builders do not execute queries. Entities do not access storage. Controllers,
  resolvers and templates must not depend on persistence classes. Preserve the
  view data boundary check.
- Use atomic storage operations for single-use state, TOTP counters and
  throttling. Do not replace them with read/modify/write sequences. Preserve
  session locking, transaction rollback, and idempotent writes.
- NoSQL persistence supports Redis/Valkey, Memcached and APCu through framework
  adapters in vendor/lucinda/framework-engine. The nosql-data-access package
  remains unchanged. Conditional replacement must remain atomic; never emulate
  it with unguarded get/set operations. APCu shares state only within its
  cache domain. Unsupported backends must fail explicitly.

Run `php tools/check-architecture.php` and `php tests/Persistence/run.php` after
changes. The latter reports when database integration checks are skipped. For
storage changes, also run integration checks against an isolated database named
`framework_dao_test_*`, initialized from `db.sql`, and a unique Redis test
namespace, plus isolated Memcached and shared-cache APCu checks when enabled.
Never use an application database for tests. See
`src/Persistence/README.md` for settings and commands.


# Object design and code style

- Never declare public class fields, including readonly fields and constructor
  promoted fields. Use private fields by default and protected fields when a
  child class needs direct access. External access must use explicit typed
  getters, never magic property access. Entities model natural logical concepts
  independently of controller or view requirements.
- Use imperative PHP. Do not introduce closures, arrow functions, callbacks,
  first-class callable expressions, or callback-based transaction/mapping APIs.
- Use instance objects with explicit ownership of their data, dependencies and
  lifecycle. Do not use static methods, static properties or static locals to
  group unrelated behavior. A final class without a constructor does not make
  its methods static. Ask the user before introducing any use of static.
- Keep executors concerned with connection ownership and execution only.
  Session leases, migration ledgers, throttling windows and representation
  retention each belong to a separate cohesive store or operation object.
- Construct each SQL query as an operation instance. Builders own the row they
  convert. Map rows and coordinate begin/commit/rollback through explicit
  imperative statements, never callable parameters.
- Use PSR-4 namespace/file mapping and PSR-12 formatting with four-space
  indentation. Break code and documentation lines at 80 characters or fewer.
  Keep SQL expressions whole in a single string literal on one line, even
  when that line exceeds 80 characters. Do not concatenate expression
  fragments solely to satisfy the line width limit.
- Document classes, owned properties, constructors and methods in the style of
  src/EventListeners: explain their purpose, lifecycle and invariants, and add
  meaningful @param, @return and @throws documentation where applicable.
- Apply these rules to new code and refactored persistence code, and update
  dependent callers and tests together. Existing framework constants and enum
  cases are constants, not approval for introducing static methods.
- The same object, documentation and imperative-style rules apply to the
  maintained atomic integration under framework-engine/src/NoSQL.


# Query construction, entities and locking

- Never embed a field alias as SQL text such as `expression AS alias`.
  Obtain the fields clause and use `$fields->add($expression, $alias)`.
- Concrete SQL operations assign `$this->statement` and `$this->parameters`
  directly. Keep constructors limited to capturing inputs and coordinating
  construction. Split fields, joins, conditions, values, changes, limits and
  duplicate-key updates into focused methods as applicable. Do not pass whole
  statements and parameter arrays through a base query constructor.
- Persistence entities have private fields with explicit typed accessors.
  Keep them final and readonly; do not introduce public fields or magic access.
- Locking is a separate interface. Keep SQLSessionHandler focused on payload
  storage and add locking through its LockingSQLSessionHandler extension,
  which implements the Lock interface.
  The default SQL lock and session storage must share one executor/connection.
  Retain ownership from read through close and configure the locking extension
  for concurrent session lifecycles.

- NoSQL locks are expiring leases implemented through AtomicDriver operations
  owned by the executor. Keep lease metadata and protected session payloads in
  the same atomically replaced record. Reject stale writes and renewal after
  ownership loss; stale release must never delete a successor's lease.
  Renewal must preserve payload expiration. APCu locks remain local to their
  cache domain and do not coordinate different application servers.
- NoSQL DAOs and NoSqlExecutor require constructor-injected storage. Data-source
  listeners publish provider facets; do not restore registry discovery in
  NoSQL persistence.
