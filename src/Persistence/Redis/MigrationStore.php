<?php

namespace Lucinda\Project\Persistence\Redis;

use Lucinda\Migration\Status;
use Lucinda\Project\Persistence\Redis\Scripts\DeleteHashField;
use Lucinda\Project\Persistence\Redis\Scripts\Exists;
use Lucinda\Project\Persistence\Redis\Scripts\InitializeHash;
use Lucinda\Project\Persistence\Redis\Scripts\ReadHashFields;
use Lucinda\Project\Persistence\Redis\Scripts\WriteHashField;
use Lucinda\Project\Persistence\RedisExecutor;

/**
 * Owns the migration status ledger stored in one application-scoped Redis hash
 *
 * Each migration has its own field, so concurrent writes cannot replace other
 * migrations. Initialization preserves all existing records.
 *
 * @internal
 */
final class MigrationStore
{
    /** Executor used for this ledger's atomic hash operations */
    private RedisExecutor $executor;

    /** Redis hash key owned by this migration ledger */
    private string $key;

    /**
     * Captures the executor and resolves this application's ledger key
     *
     * @param RedisExecutor $executor Connection and namespace for the ledger
     */
    public function __construct(RedisExecutor $executor)
    {
        $this->executor = $executor;
        $this->key = $executor->getKeyspace()->getKey('migrations', 'records');
    }

    /**
     * Checks whether the ledger exists in Redis
     *
     * @return bool True when initialization or a recorded status exists
     * @throws \Throwable If Redis cannot inspect the ledger
     */
    public function exists(): bool
    {
        $result = $this->executor->execute(new Exists($this->key));
        return $result->getInteger() > 0;
    }

    /**
     * Initializes the ledger without changing existing migration statuses
     *
     * @throws \Throwable If Redis cannot initialize the ledger
     */
    public function create(): void
    {
        $this->executor->execute(new InitializeHash($this->key));
    }

    /**
     * Records the status of one migration independently from other records
     *
     * @param string $className Fully qualified migration class name
     * @param Status $status Backed migration execution status
     * @throws \Throwable If Redis cannot persist the status
     */
    public function save(string $className, Status $status): void
    {
        $script = new WriteHashField(
            $this->key,
            $className,
            (string) $status->value
        );
        $this->executor->execute($script);
    }

    /**
     * Reads and validates every recorded migration status
     *
     * @return array<string, Status> Statuses indexed by migration class name
     * @throws \UnexpectedValueException If stored fields or statuses are
     *     corrupt
     * @throws \Throwable If Redis cannot read the ledger
     */
    public function read(): array
    {
        $result = $this->executor->execute(new ReadHashFields($this->key));
        $values = $result->getValues();
        if (count($values) % 2 !== 0) {
            throw new \UnexpectedValueException('Invalid migration ledger');
        }
        $statuses = [];
        for ($index = 0; $index < count($values); $index += 2) {
            $name = $values[$index];
            $value = $values[$index + 1];
            if (!is_string($name) || !is_string($value)) {
                throw new \UnexpectedValueException('Invalid ledger field');
            }
            if ($name === '@initialized') {
                continue;
            }
            switch ($value) {
                case '1':
                    $statuses[$name] = Status::PENDING;
                    break;
                case '2':
                    $statuses[$name] = Status::FAILED;
                    break;
                case '3':
                    $statuses[$name] = Status::PASSED;
                    break;
                default:
                    throw new \UnexpectedValueException(
                        'Invalid migration status'
                    );
            }
        }
        return $statuses;
    }

    /**
     * Deletes one recorded migration without touching other ledger fields
     *
     * @param string $className Fully qualified migration class name
     * @throws \Throwable If Redis cannot remove the field
     */
    public function remove(string $className): void
    {
        $this->executor->execute(new DeleteHashField($this->key, $className));
    }
}
