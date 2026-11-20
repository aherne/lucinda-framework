<?php

namespace Test\Lucinda\Project\Persistence;

use Lucinda\Migration\Status;
use Lucinda\Project\DAO;
use Lucinda\Project\Persistence\Locking\NoSqlLock;
use RuntimeException;
use Throwable;

/**
 * Owns one group of workers sharing an isolated backend namespace
 */
final class NoSqlConcurrency
{
    /** Backend selected for this worker group */
    private string $backend;

    /** Isolated namespace shared by this worker group */
    private string $prefix;

    /**
     * Captures the backend and namespace inherited by competing workers
     *
     * @param string $backend Selected backend name
     * @param string $prefix Unique integration-test namespace
     */
    public function __construct(string $backend, string $prefix)
    {
        $this->backend = $backend;
        $this->prefix = $prefix;
    }

    /**
     * Runs competing requests in independent processes sharing the same backend
     *
     * APCu requires forks of the initialized cache rather than fresh CLI runtimes.
     * Redis and Memcached workers open their own connections after forking.
     *
     * @param string $mode Domain operation performed by each worker
     * @param string $identity Shared domain identity
     * @param int $number Number of competing workers
     * @throws RuntimeException If a worker cannot run or fails its operation
     */
    public function run(
        string $mode,
        string $identity,
        int $number
    ): void {
        $backend = $this->backend;
        $prefix = $this->prefix;
        $children = [];
        for ($index = 0; $index < $number; $index++) {
            $pid = pcntl_fork();
            if ($pid === -1) {
                throw new RuntimeException('Cannot fork NoSQL test worker');
            }
            if ($pid === 0) {
                try {
                    if ($mode === 'atomic-counter') {
                        $driver = persistenceTestAtomicDriver($backend);
                        $driver->increment($identity);
                        exit(0);
                    }
                    $executor = persistenceTestNoSql($backend, $prefix);
                    switch ($mode) {
                        case 'throttle':
                            $throttle = new DAO\NoSQLLoginThrottler($executor);
                            $throttle->penalize($identity, '127.0.0.1');
                            break;
                        case 'session':
                            $session = new DAO\NoSQLSessionHandler($executor);
                            $before = (int) $session->read($identity);
                            usleep(15000);
                            $session->write($identity, (string) ($before + 1));
                            $session->close();
                            break;
                        case 'migration':
                            $cache = new DAO\NoSQLMigrationCache($executor);
                            $cache->add('Concurrent' . $index, Status::PASSED);
                            break;
                        case 'lock':
                            $lock = new NoSqlLock($executor);
                            $lock->acquire($identity);
                            $snapshot = $lock->read();
                            $values = $snapshot->getValue();
                            $before = $values['value'] ?? 0;
                            usleep(15000);
                            if (
                                !$lock->replace($snapshot, [
                                'value' => $before + 1,
                                ], 60)
                            ) {
                                throw new RuntimeException('Lease lost');
                            }
                            $lock->release();
                            break;
                        default:
                            throw new RuntimeException('Unknown worker mode');
                    }
                    exit(0);
                } catch (Throwable $error) {
                    fwrite(STDERR, $error->getMessage() . PHP_EOL);
                    exit(1);
                }
            }
            $children[] = $pid;
        }
        $failed = false;
        foreach ($children as $pid) {
            pcntl_waitpid($pid, $status);
            if (!pcntl_wifexited($status) || pcntl_wexitstatus($status) !== 0) {
                $failed = true;
            }
        }
        if ($failed) {
            throw new RuntimeException('NoSQL concurrency worker failed');
        }
    }
}
