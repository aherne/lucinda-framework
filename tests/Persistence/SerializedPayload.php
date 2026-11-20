<?php

namespace Test\Lucinda\Project\Persistence;

/** Payload fixture whose serialization can expose internal persistence data. */
final class SerializedPayload implements \JsonSerializable
{
    /**
     * Captures the nested record returned during serialization
     *
     * @param object $entity Internal record used by the boundary test
     */
    public function __construct(private object $entity)
    {
    }

    /** @return mixed Nested record returned to the serializer */
    public function jsonSerialize(): mixed
    {
        return $this->entity;
    }
}
