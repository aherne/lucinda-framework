<?php

namespace Lucinda\Project\Presentation;

/**
 * Validates one presentation payload before it enters a view resolver
 *
 * This validator owns the payload and traversal state for one validation.
 * Internal persistence objects are rejected even when nested in arrays,
 * inherited private properties or serialized object output.
 */
final class DataBoundary
{
    /** Payload whose object graph belongs to this validation */
    private mixed $data;

    /** Maximum supported nesting depth for this payload */
    private int $maximumDepth;

    /** Objects already inspected during the current validation */
    private \SplObjectStorage $seen;

    /**
     * Captures the payload and its permitted traversal depth
     *
     * @param mixed $data Application values prepared for presentation
     * @param int $maximumDepth Maximum permitted array and object nesting
     * @throws \InvalidArgumentException If the depth limit is not positive
     */
    public function __construct(mixed $data, int $maximumDepth = 64)
    {
        if ($maximumDepth < 1) {
            throw new \InvalidArgumentException('Invalid payload depth limit');
        }
        $this->data = $data;
        $this->maximumDepth = $maximumDepth;
        $this->seen = new \SplObjectStorage();
    }

    /**
     * Validates every reachable value in this presentation payload
     *
     * @throws \LogicException If an internal object reaches presentation
     * @throws \UnexpectedValueException If the payload exceeds its depth limit
     */
    public function validate(): void
    {
        $this->seen = new \SplObjectStorage();
        $this->inspect($this->data, 0);
    }

    /**
     * Inspects one value while retaining cycle detection within this payload
     *
     * @param mixed $value Value reached during traversal
     * @param int $depth Current nesting depth
     * @throws \LogicException If a persistence object or lazy collection
     *     appears
     * @throws \UnexpectedValueException If traversal exceeds its limit
     */
    private function inspect(mixed $value, int $depth): void
    {
        if ($depth > $this->maximumDepth) {
            throw new \UnexpectedValueException('Payload nesting is too deep');
        }
        if (is_array($value)) {
            foreach ($value as $item) {
                $this->inspect($item, $depth + 1);
            }
            return;
        }
        if (!is_object($value)) {
            return;
        }
        $className = get_class($value);
        if (str_starts_with($className, 'Lucinda\\Project\\Persistence\\')) {
            throw new \LogicException(
                'Persistence objects must not enter presentation data'
            );
        }
        if ($this->seen->contains($value)) {
            return;
        }
        $this->seen->attach($value);
        if ($value instanceof \Traversable) {
            throw new \LogicException(
                'Materialize presentation collections into explicit arrays'
            );
        }
        $reflection = new \ReflectionObject($value);
        do {
            foreach ($reflection->getProperties() as $property) {
                if (
                    !$property->isStatic()
                    && $property->isInitialized($value)
                ) {
                    $this->inspect($property->getValue($value), $depth + 1);
                }
            }
            $reflection = $reflection->getParentClass();
        } while ($reflection !== false);
        if ($value instanceof \JsonSerializable) {
            $this->inspect($value->jsonSerialize(), $depth + 1);
        }
    }
}
