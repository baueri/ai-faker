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

    public function __construct(array $data)
    {
        foreach ($data as $item) {
            $this->items[] = $item;
        }
    }


    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function toArray(): array
    {
        return array_map(fn(array $i) => $i, $this->items);
    }
}
