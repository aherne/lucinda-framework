-- Fresh installation only. SQLite 3.33+; PHP requires 64-bit integers.
-- Run manually against the intended EMPTY application database file.
-- No DROP statements, seeded accounts or automatic upgrades.
-- Enable foreign keys on EVERY connection, not only during installation.
-- Text identifiers use BINARY collation; serialized payloads retain bytes.
-- Named session advisory locks are unsupported: use SQLSessionHandler
-- with an external lock strategy, or use NoSQL session storage.

PRAGMA foreign_keys = ON;

-- DAO: FormLogin, OAuth2Login, OAuth2AutomaticProvisioning,
-- OAuth2ApprovalProvisioning, UserRoles, UserAuthorization, RoleAuthorization,
-- MultiFactorAuthentication. Logout owns no database state.
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT CHECK (id >= 0),
    display_name TEXT COLLATE BINARY NOT NULL DEFAULT '' CHECK (length(display_name) <= 255),
    email TEXT COLLATE BINARY NULL CHECK (length(email) <= 255),
    is_active INTEGER NOT NULL DEFAULT 1 CHECK (is_active IN (0, 1)),
    mfa_required INTEGER NOT NULL DEFAULT 0 CHECK (mfa_required IN (0, 1)),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX users_email_idx ON users (email);
-- Email is deliberately not an identity key: no automatic account merging by email.

-- DAO: FormLogin. Use password_hash(PASSWORD_DEFAULT), never seed plaintext passwords.
CREATE TABLE user_credentials (
    user_id INTEGER NOT NULL CHECK (user_id >= 0),
    username TEXT COLLATE BINARY NOT NULL CHECK (length(username) <= 255),
    password_hash TEXT COLLATE BINARY NOT NULL CHECK (length(password_hash) <= 255),
    PRIMARY KEY (user_id),
    UNIQUE (username),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) WITHOUT ROWID;

-- DAO: OAuth2Login, OAuth2AutomaticProvisioning, OAuth2ApprovalProvisioning.
-- Provider tokens are not stored. The identity tuple alone resolves a local account.
CREATE TABLE oauth2_identities (
    vendor_name TEXT COLLATE BINARY NOT NULL CHECK (length(vendor_name) <= 64),
    remote_id TEXT COLLATE BINARY NOT NULL CHECK (length(CAST(remote_id AS BLOB)) <= 255),
    user_id INTEGER NOT NULL CHECK (user_id >= 0),
    PRIMARY KEY (vendor_name, remote_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) WITHOUT ROWID;
CREATE INDEX oauth2_identities_user_id_idx ON oauth2_identities (user_id);

-- DAO: OAuth2ApprovalProvisioning. Requesting approval never creates a login identity.
-- To approve, an administrator explicitly creates a user and identity link in one
-- transaction and marks the request approved. A disabled linked account stays disabled.
CREATE TABLE oauth2_approvals (
    vendor_name TEXT COLLATE BINARY NOT NULL CHECK (length(vendor_name) <= 64),
    remote_id TEXT COLLATE BINARY NOT NULL CHECK (length(CAST(remote_id AS BLOB)) <= 255),
    display_name TEXT COLLATE BINARY NOT NULL CHECK (length(display_name) <= 255),
    email TEXT COLLATE BINARY NULL CHECK (length(email) <= 255),
    status TEXT COLLATE BINARY NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'approved', 'rejected')),
    requested_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (vendor_name, remote_id)
) WITHOUT ROWID;
CREATE INDEX oauth2_approvals_status_requested_at_idx ON oauth2_approvals (status, requested_at);

-- DAO: OAuth2States. Only the SHA-256 proof hash is persisted.
CREATE TABLE oauth2_states (
    state TEXT COLLATE BINARY NOT NULL CHECK (length(CAST(state AS BLOB)) <= 255),
    browser_proof_hash TEXT COLLATE BINARY NOT NULL CHECK (length(browser_proof_hash) <= 64),
    vendor_name TEXT COLLATE BINARY NOT NULL CHECK (length(vendor_name) <= 64),
    valid_until INTEGER NOT NULL CHECK (valid_until >= 0),
    PRIMARY KEY (state)
) WITHOUT ROWID;
CREATE INDEX oauth2_states_valid_until_idx ON oauth2_states (valid_until);

-- DAO: PageAuthorization, UserAuthorization, RoleAuthorization.
-- url contains the validated route ID, including placeholders such as users/(id).
CREATE TABLE resources (
    id INTEGER PRIMARY KEY AUTOINCREMENT CHECK (id >= 0 AND id <= 4294967295),
    url TEXT COLLATE BINARY NOT NULL CHECK (length(url) <= 255),
    is_public INTEGER NOT NULL DEFAULT 0 CHECK (is_public IN (0, 1)),
    UNIQUE (url)
);

-- DAO: UserRoles, RoleAuthorization. SimpleUserRoles needs neither roles table.
CREATE TABLE roles (
    id INTEGER PRIMARY KEY AUTOINCREMENT CHECK (id >= 0 AND id <= 4294967295),
    name TEXT COLLATE BINARY NOT NULL CHECK (length(name) <= 128),
    UNIQUE (name)
);

-- DAO: UserRoles, RoleAuthorization.
CREATE TABLE users_roles (
    user_id INTEGER NOT NULL CHECK (user_id >= 0),
    role_id INTEGER NOT NULL CHECK (role_id >= 0 AND role_id <= 4294967295),
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) WITHOUT ROWID;
CREATE INDEX users_roles_role_id_idx ON users_roles (role_id);

-- DAO: UserAuthorization. Use uppercase method names; * grants every method.
CREATE TABLE users_resources (
    user_id INTEGER NOT NULL CHECK (user_id >= 0),
    resource_id INTEGER NOT NULL CHECK (resource_id >= 0 AND resource_id <= 4294967295),
    http_method TEXT COLLATE BINARY NOT NULL CHECK (length(http_method) <= 16),
    PRIMARY KEY (user_id, resource_id, http_method),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE
) WITHOUT ROWID;
CREATE INDEX users_resources_resource_id_idx ON users_resources (resource_id);

-- DAO: RoleAuthorization. Alternative to direct user grants; not automatically combined.
CREATE TABLE roles_resources (
    role_id INTEGER NOT NULL CHECK (role_id >= 0 AND role_id <= 4294967295),
    resource_id INTEGER NOT NULL CHECK (resource_id >= 0 AND resource_id <= 4294967295),
    http_method TEXT COLLATE BINARY NOT NULL CHECK (length(http_method) <= 16),
    PRIMARY KEY (role_id, resource_id, http_method),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE
) WITHOUT ROWID;
CREATE INDEX roles_resources_resource_id_idx ON roles_resources (resource_id);

-- DAO: SQLLoginThrottler, SQLMultiFactorThrottler. Keys hash scope/subject/canonical IP.
-- Periodic clearExpired() removes abandoned windows. Expirations are Unix seconds.
CREATE TABLE authentication_throttles (
    bucket TEXT COLLATE BINARY NOT NULL CHECK (length(bucket) <= 64),
    attempts INTEGER NOT NULL CHECK (attempts >= 0 AND attempts <= 4294967295),
    expires INTEGER NOT NULL CHECK (expires >= 0),
    PRIMARY KEY (bucket)
) WITHOUT ROWID;
CREATE INDEX authentication_throttles_expires_idx ON authentication_throttles (expires);

-- DAO: MultiFactorAuthentication. Secrets are authenticated encrypted ciphertext,
-- not plaintext. Keep MFA_ENCRYPTION_KEY outside the database and source control.
-- Setup lifetime defaults to 300 seconds; last_counter survives successful enable().
CREATE TABLE user_mfa (
    user_id INTEGER NOT NULL CHECK (user_id >= 0),
    secret TEXT COLLATE BINARY NULL CHECK (length(secret) <= 512),
    setup_secret TEXT COLLATE BINARY NULL CHECK (length(setup_secret) <= 512),
    setup_expires INTEGER NULL CHECK (setup_expires >= 0),
    last_counter INTEGER NULL CHECK (last_counter >= 0),
    PRIMARY KEY (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) WITHOUT ROWID;

-- DAO: SQLSessionHandler, LockingSQLSessionHandler.
-- Payload preserves arbitrary serialized session bytes.
-- Use an external lock when concurrent session requests require ownership.
CREATE TABLE sessions (
    id TEXT COLLATE BINARY NOT NULL CHECK (length(id) <= 256),
    payload BLOB NOT NULL CHECK (length(CAST(payload AS BLOB)) <= 16777215),
    expires INTEGER NOT NULL CHECK (expires >= 0),
    PRIMARY KEY (id)
) WITHOUT ROWID;
CREATE INDEX sessions_expires_idx ON sessions (expires);

-- DAO: SQLMigrationCache. Installation must precede running migrations.php.
CREATE TABLE migrations (
    class_name TEXT COLLATE BINARY NOT NULL CHECK (length(class_name) <= 255),
    status_code INTEGER NOT NULL CHECK (status_code >= 0 AND status_code <= 255),
    recorded_at INTEGER NOT NULL CHECK (recorded_at >= 0),
    PRIMARY KEY (class_name),
    CHECK (status_code IN (1, 2, 3))
) WITHOUT ROWID;

-- NoSQL DAOs: NoSQLSessionHandler, NoSQLLoginThrottler, NoSQLMultiFactorThrottler,
-- NoSQLMigrationCache require no SQL tables. Configure PERSISTENCE_NAMESPACE per app.
