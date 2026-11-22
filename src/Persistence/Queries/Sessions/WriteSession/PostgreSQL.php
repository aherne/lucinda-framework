<?php

namespace Lucinda\Project\Persistence\Queries\Sessions\WriteSession;

use Lucinda\Query\Insert;
use Lucinda\SQL\DataType;
use Lucinda\Query\Vendor\PostgreSQL\Insert as VendorInsert;

/**
 * Supplies PostgreSQL insertion and conflict syntax for this operation
 *
 * @internal
 */
final class PostgreSQL extends AbstractQuery
{
    /**
     * Binds binary payloads as streams for PostgreSQL BYTEA storage
     *
     * @param string $id Session identifier
     * @param string $payload Serialized bytes, including NUL and non-UTF-8 data
     * @param int $expires Absolute expiration in Unix seconds
     * @throws \RuntimeException If a payload stream cannot be prepared
     */
    protected function setParameters(
        string $id,
        string $payload,
        int $expires
    ): void {
        parent::setParameters($id, $payload, $expires);
        foreach ([':payload', ':new_payload'] as $name) {
            $this->parameters->set(
                $name,
                $this->getPayloadStream($payload),
                DataType::STREAMED
            );
        }
    }

    /**
     * Creates an independent stream for one binary parameter binding
     *
     * @param string $payload Serialized session bytes
     * @return resource Stream retained by the query's typed parameters
     * @throws \RuntimeException If stream creation or writing fails
     */
    private function getPayloadStream(string $payload): mixed
    {
        $stream = fopen('php://memory', 'w+b');
        if ($stream === false) {
            throw new \RuntimeException('Cannot open session payload stream');
        }
        if (fwrite($stream, $payload) !== strlen($payload)) {
            fclose($stream);
            throw new \RuntimeException('Cannot write session payload stream');
        }
        rewind($stream);
        return $stream;
    }

    /**
     * Creates the insertion builder for this vendor
     *
     * @return Insert Builder targeting the operation's persistence table
     */
    protected function createInsert(): Insert
    {
        return new VendorInsert('sessions');
    }

    /**
     * Applies the atomic conflict clause without a separate existence check
     *
     * @param array<string, string> $updates Bound update expressions
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    protected function setConflictUpdate(array $updates): void
    {
        $this->statement->onConflictDoUpdate(['id'], $updates);
    }
}
