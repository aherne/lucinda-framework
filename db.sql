-- Fresh installation only. MySQL 8+, InnoDB, connection charset utf8mb4.
-- Run this file manually against the intended EMPTY application database.
-- No DROP statements, seeded passwords, default accounts or automatic upgrades.
-- Account identifiers are BIGINT UNSIGNED; PHP requires 64-bit integers.
-- Usernames, provider identifiers and routes compare case-sensitively.

-- DAO: FormLogin, OAuth2Login, OAuth2AutomaticProvisioning,
-- OAuth2ApprovalProvisioning, UserRoles, UserAuthorization, RoleAuthorization,
-- MultiFactorAuthentication. Logout owns no database state.
CREATE TABLE users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    display_name VARCHAR(255) NOT NULL DEFAULT '',
    email VARCHAR(255) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    mfa_required BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
-- Email is deliberately not an identity key: no automatic account merging by email.

-- DAO: FormLogin. Use password_hash(PASSWORD_DEFAULT), never seed plaintext passwords.
CREATE TABLE user_credentials (
    user_id BIGINT UNSIGNED NOT NULL,
    username VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    PRIMARY KEY (user_id),
    UNIQUE KEY (username),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- DAO: OAuth2Login, OAuth2AutomaticProvisioning, OAuth2ApprovalProvisioning.
-- Provider tokens are not stored. The identity tuple alone resolves a local account.
CREATE TABLE oauth2_identities (
    vendor_name VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    remote_id VARBINARY(255) NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (vendor_name, remote_id),
    KEY (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- DAO: OAuth2ApprovalProvisioning. Requesting approval never creates a login identity.
-- To approve, an administrator explicitly creates a user and identity link in one
-- transaction and marks the request approved. A disabled linked account stays disabled.
CREATE TABLE oauth2_approvals (
    vendor_name VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    remote_id VARBINARY(255) NOT NULL,
    display_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    requested_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (vendor_name, remote_id),
    KEY (status, requested_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- DAO: OAuth2States. Only the SHA-256 proof hash is persisted.
CREATE TABLE oauth2_states (
    state VARBINARY(255) NOT NULL,
    browser_proof_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    vendor_name VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    valid_until BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (state),
    KEY (valid_until)
) ENGINE=InnoDB;

-- DAO: PageAuthorization, UserAuthorization, RoleAuthorization.
-- url contains the validated route ID, including placeholders such as users/(id).
CREATE TABLE resources (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    url VARCHAR(255) NOT NULL,
    is_public BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (id),
    UNIQUE KEY (url)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- DAO: UserRoles, RoleAuthorization. SimpleUserRoles needs neither roles table.
CREATE TABLE roles (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(128) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- DAO: UserRoles, RoleAuthorization.
CREATE TABLE users_roles (
    user_id BIGINT UNSIGNED NOT NULL,
    role_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- DAO: UserAuthorization. Use uppercase method names; * grants every method.
CREATE TABLE users_resources (
    user_id BIGINT UNSIGNED NOT NULL,
    resource_id INT UNSIGNED NOT NULL,
    http_method VARCHAR(16) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    PRIMARY KEY (user_id, resource_id, http_method),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- DAO: RoleAuthorization. Alternative to direct user grants; not automatically combined.
CREATE TABLE roles_resources (
    role_id INT UNSIGNED NOT NULL,
    resource_id INT UNSIGNED NOT NULL,
    http_method VARCHAR(16) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    PRIMARY KEY (role_id, resource_id, http_method),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- DAO: SQLLoginThrottler, SQLMultiFactorThrottler. Keys hash scope/subject/canonical IP.
-- Periodic clearExpired() removes abandoned windows. Expirations are Unix seconds.
CREATE TABLE authentication_throttles (
    bucket CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    attempts INT UNSIGNED NOT NULL,
    expires BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (bucket),
    KEY (expires)
) ENGINE=InnoDB;

-- DAO: MultiFactorAuthentication. Secrets are authenticated encrypted ciphertext,
-- not plaintext. Keep MFA_ENCRYPTION_KEY outside the database and source control.
-- Setup lifetime defaults to 300 seconds; last_counter survives successful enable().
CREATE TABLE user_mfa (
    user_id BIGINT UNSIGNED NOT NULL,
    secret VARCHAR(512) CHARACTER SET ascii COLLATE ascii_bin NULL,
    setup_secret VARCHAR(512) CHARACTER SET ascii COLLATE ascii_bin NULL,
    setup_expires BIGINT UNSIGNED NULL,
    last_counter BIGINT UNSIGNED NULL,
    PRIMARY KEY (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- DAO: SQLSessionHandler, LockingSQLSessionHandler.
-- Payload preserves arbitrary serialized session bytes.
-- Connections hold advisory locks through close(); keep them nonpersistent.
CREATE TABLE sessions (
    id VARCHAR(256) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    payload MEDIUMBLOB NOT NULL,
    expires BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    KEY (expires)
) ENGINE=InnoDB;

-- DAO: SQLMigrationCache. Installation must precede running migrations.php.
CREATE TABLE migrations (
    class_name VARCHAR(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    status_code TINYINT UNSIGNED NOT NULL COMMENT '1=pending, 2=failed, 3=passed',
    CHECK (status_code IN (1, 2, 3)),
    recorded_at BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (class_name)
) ENGINE=InnoDB;

-- Redis DAOs: NoSQLSessionHandler, NoSQLLoginThrottler, NoSQLMultiFactorThrottler,
-- NoSQLMigrationCache require no SQL tables. Configure PERSISTENCE_NAMESPACE per app.
