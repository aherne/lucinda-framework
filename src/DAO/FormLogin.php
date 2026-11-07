<?php

namespace Lucinda\Project\DAO;

use Lucinda\Project\Persistence\Builders\CredentialsBuilder;
use Lucinda\Project\Persistence\Queries\Accounts\FindCredentials;
use Lucinda\Project\Persistence\Queries\Accounts\UpdatePasswordHash;
use Lucinda\Project\Persistence\SqlExecutor;
use Lucinda\WebSecurity\DAO\FormLogin as FormLoginContract;

/**
 * Authenticates a submitted username and password against an active account
 *
 * Credential rows are explicitly built into internal entities. Password hashes
 * are upgraded conditionally without replacing a concurrently changed hash.
 */
final class FormLogin implements FormLoginContract
{
    /** Executor retained for credential reads and conditional hash upgrades */
    private SqlExecutor $sql;

    /** Valid bcrypt hash used for timing work when an account does not exist */
    private const DUMMY_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4o'
        . 'KoEa3Ro9llC/.og/at2uheWG/igi';

    /**
     * Captures an injected executor or a lazily configured SQL connection
     *
     * @param SqlExecutor|null $sql Executor sharing the caller's connection
     */
    public function __construct(?SqlExecutor $sql = null)
    {
        $this->sql = $sql ?? new SqlExecutor();
    }

    /**
     * Verifies credentials and returns only the accepted local account ID
     *
     * @param string $username Case-sensitive submitted username
     * @param string $password Submitted plaintext password
     * @return int|string|null Local identifier or null when login is rejected
     * @throws \Throwable If database access or hash upgrading fails
     */
    public function login(string $username, string $password): int|string|null
    {
        $selection = $this->sql->select(new FindCredentials($username));
        $row = $selection->getFirstRow();
        if ($row === null) {
            password_verify($password, self::DUMMY_HASH);
            return null;
        }
        $builder = new CredentialsBuilder($row);
        $credentials = $builder->build();
        if (!password_verify($password, $credentials->getPasswordHash())) {
            return null;
        }
        if (
            password_needs_rehash(
                $credentials->getPasswordHash(),
                PASSWORD_DEFAULT
            )
        ) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $query = new UpdatePasswordHash(
                $credentials->getUserID(),
                $credentials->getPasswordHash(),
                $newHash
            );
            $this->sql->write($query);
        }
        return $credentials->getUserID();
    }
}
