# OAuth2 state storage

The project OAuth2 state adapter binds each provider login attempt to its initiating browser without using PHP sessions. A SQL DAO stores the attempt and consumes it once. The same implementation supports browser applications and REST applications with browser-based OAuth2 login.

[Setup](#setup) · [Inputs and outputs](#inputs-and-outputs) · [Browser flow](#browser-flow) · [Maintenance](#maintenance)

## Setup

Create this table on the default SQL connection:

~~~sql
CREATE TABLE oauth2_states (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    state VARCHAR(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    browser_proof_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    vendor_name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
    valid_until INT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE (state),
    KEY (valid_until)
) ENGINE=InnoDB;
~~~

Run the SQL data-source listener before the security listener and configure the OAuth2 XML path in the security listener. State persistence uses the default SQL connection through the project SQL helper. The connection must use autocommit, and state operations must run outside application transactions: rolling back a consumed state would make it usable again.

The security listener constructs the adapter with DAO\OAuth2States and the MVC Cookies facet. The vendor security API is unchanged. A session listener is not required for OAuth2 state; other configured security persistence mechanisms may still require a session.

## Inputs and outputs

| Operation | Inputs | Output |
| --- | --- | --- |
| OAuth2State::__construct() | SQL DAO and incoming MVC cookies | Adapter implementing Lucinda\WebSecurity\OAuth2State |
| OAuth2State::save() | Random state, configured provider name, absolute Unix expiration | Inserts an attempt and sends a proof cookie; returns nothing |
| OAuth2State::consume() | Callback state and configured provider name | True exactly once for a live attempt with its browser proof; otherwise false |
| DAO\OAuth2States::save() | State, provider, expiration, hexadecimal SHA-256 browser proof hash | Inserts one row; duplicate state raises a SQL exception |
| DAO\OAuth2States::consume() | State, provider, browser proof hash | Atomically deletes a live match and reports whether one row was affected |
| DAO\OAuth2States::clearExpired() | None | Deletes attempts whose expiration timestamp has been reached |

Storage and cookie-writing failures propagate as exceptions. Saving an already expired attempt raises InvalidArgumentException. No raw browser proof is stored in SQL.

## Browser flow

The adapter generates a separate 32-byte random proof for each state. It stores the proof in a cookie named __Host-lucinda_oauth2_ followed by the SHA-256 hash of the state. Cookies are host-only, use path /, and have Secure, HttpOnly, SameSite=Lax, and the attempt's expiration. Distinct names preserve concurrent attempts.

HTTPS is required. The initial request and callback must use the same host, and the browser must accept and return the proof cookie. For REST clients, initial requests made through browser fetch must include cookies as appropriate. The provider callback must arrive as top-level GET navigation; cross-site POST callbacks need a different SameSite strategy.

On callback, the adapter rejects missing or malformed proof cookies. The DAO validates state, provider, proof hash, and expiration in a single conditional DELETE. Only a successful deletion is accepted, then the adapter expires that attempt's cookie using the same path and attributes. Live attempts with a mismatched provider or proof are preserved.

Cookie headers are written natively because the current MVC cookie facade does not expose SameSite or cookie-write results. The facade reads incoming cookies; outgoing writes do not alter that request snapshot. Callbacks must therefore occur on a subsequent browser request.

If cookie creation fails after insertion, the adapter throws and the unusable row remains until expiry cleanup. If cookie deletion fails after consumption, the adapter throws; the deleted SQL row still prevents replay.

## Maintenance

Call DAO\OAuth2States::clearExpired() periodically from application maintenance to remove abandoned attempts. Rejected consumption also removes an expired row when the state and browser proof match.
