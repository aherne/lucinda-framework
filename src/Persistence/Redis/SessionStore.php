<?php

namespace Lucinda\Project\Persistence\Redis;

use Lucinda\Project\Persistence\Redis\Scripts\AcquireLease;
use Lucinda\Project\Persistence\Redis\Scripts\DeleteSession;
use Lucinda\Project\Persistence\Redis\Scripts\ReadSession;
use Lucinda\Project\Persistence\Redis\Scripts\ReleaseLease;
use Lucinda\Project\Persistence\Redis\Scripts\WriteSession;
use Lucinda\Project\Persistence\RedisExecutor;

/**
 * Owns one active Redis session lease and its payload operations
 *
 * The lease remains held between reading and closing the session. Every write
 * verifies ownership atomically, so a request whose lease expired cannot
 * overwrite a later request's data.
 *
 * @internal
 */
final class SessionStore
{
    /** Executor used for this session's lease and payload operations */
    private RedisExecutor $executor;

    /** Identifier whose lease is currently held by this store */
    private ?string $id = null;

    /** Unique token proving this store's current lease ownership */
    private ?string $token = null;

    /** Maximum lifetime of an owned lease in seconds */
    private int $leaseLifetime;

    /** Maximum time spent waiting for another owner in seconds */
    private int $waitTimeout;

    /**
     * Captures the connection and session lease policy
     *
     * @param RedisExecutor $executor Connection and namespace for this store
     * @param int $leaseLifetime Positive lease lifetime in seconds
     * @param int $waitTimeout Positive lock acquisition timeout in seconds
     * @throws \InvalidArgumentException If either lifetime is invalid
     */
    public function __construct(
        RedisExecutor $executor,
        int $leaseLifetime = 60,
        int $waitTimeout = 10
    ) {
        if ($leaseLifetime < 1 || $waitTimeout < 1) {
            throw new \InvalidArgumentException('Invalid session lease policy');
        }
        $this->executor = $executor;
        $this->leaseLifetime = $leaseLifetime;
        $this->waitTimeout = $waitTimeout;
    }

    /**
     * Resolves the lock key belonging to one session identifier
     *
     * @param string $id Session identifier supplied by PHP
     * @return string Namespaced Redis lease key
     */
    private function getLockKey(string $id): string
    {
        return $this->executor->getKeyspace()->getKey('session-lock', $id);
    }

    /**
     * Resolves the data key belonging to one session identifier
     *
     * @param string $id Session identifier supplied by PHP
     * @return string Namespaced Redis payload key
     */
    private function getDataKey(string $id): string
    {
        return $this->executor->getKeyspace()->getKey('session', $id);
    }

    /**
     * Acquires a lease or retains the lease already held for this session
     *
     * @param string $id Session identifier supplied by PHP
     * @return string Unique token proving this store's ownership
     * @throws \RuntimeException If another owner retains the lease too long
     * @throws \Throwable If Redis cannot execute a lease operation
     */
    private function acquire(string $id): string
    {
        if ($this->id === $id && $this->token !== null) {
            return $this->token;
        }
        $this->close();
        $token = bin2hex(random_bytes(24));
        $deadline = microtime(true) + $this->waitTimeout;
        do {
            $script = new AcquireLease(
                $this->getLockKey($id),
                $token,
                $this->leaseLifetime
            );
            $result = $this->executor->execute($script);
            if ($result->getInteger() === 1) {
                $this->id = $id;
                $this->token = $token;
                return $token;
            }
            usleep(20000);
        } while (microtime(true) < $deadline);
        throw new \RuntimeException('Redis session lock timed out');
    }

    /**
     * Reads an existing payload while retaining its lease through close
     *
     * @param string $id Session identifier supplied by PHP
     * @return string Stored bytes or an empty string for a new session
     * @throws \Throwable If the lease expires or Redis cannot read the payload
     */
    public function read(string $id): string
    {
        $token = $this->acquire($id);
        $script = new ReadSession(
            $this->getLockKey($id),
            $this->getDataKey($id),
            $token
        );
        $result = $this->executor->execute($script);
        return $result->getString();
    }

    /**
     * Persists session bytes only while this store owns the current lease
     *
     * @param string $id Session identifier supplied by PHP
     * @param string $payload Serialized session bytes
     * @param int $lifetime Positive payload lifetime in seconds
     * @throws \InvalidArgumentException If the payload lifetime is invalid
     * @throws \Throwable If lease ownership is lost or persistence fails
     */
    public function write(string $id, string $payload, int $lifetime): void
    {
        if ($lifetime < 1) {
            throw new \InvalidArgumentException('Invalid session lifetime');
        }
        $token = $this->acquire($id);
        $script = new WriteSession(
            $this->getLockKey($id),
            $this->getDataKey($id),
            $token,
            $payload,
            $lifetime
        );
        $this->executor->execute($script);
    }

    /**
     * Deletes the current payload without requiring it to exist
     *
     * @param string $id Session identifier supplied by PHP
     * @throws \Throwable If the lease expires or deletion fails
     */
    public function destroy(string $id): void
    {
        $token = $this->acquire($id);
        $script = new DeleteSession(
            $this->getLockKey($id),
            $this->getDataKey($id),
            $token
        );
        $this->executor->execute($script);
    }

    /**
     * Releases the lease only if this store remains its current owner
     *
     * @throws \Throwable If Redis cannot attempt release
     */
    public function close(): void
    {
        if ($this->id !== null && $this->token !== null) {
            $script = new ReleaseLease(
                $this->getLockKey($this->id),
                $this->token
            );
            $this->executor->execute($script);
        }
        $this->id = null;
        $this->token = null;
    }

    /**
     * Attempts to release an owned lease when the store leaves scope
     */
    public function __destruct()
    {
        try {
            $this->close();
        } catch (\Throwable) {
            // The finite lease remains the recovery path during shutdown.
        }
    }
}
