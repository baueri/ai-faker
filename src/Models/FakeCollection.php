<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Models;

/**
 * @template T
 * @template-implements \IteratorAggregate<T>
 */
class FakeCollection implements \IteratorAggregate, \Countable
{
    protected array $items = [];

    /**
     * @param list<mixed> $data
     */
    public function __construct(array $data)
    {
        foreach ($data as $item) {
            $this->items[] = $item;
        }
    }


    /**
     * @return \Traversable<int, mixed>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Return items as a plain PHP array.
     *
     * @return list<mixed>
     */
    public function toArray(): array
    {
        return $this->items;
    }
}
