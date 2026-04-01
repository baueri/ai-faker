<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Models;

class FakeCollection implements \IteratorAggregate, \Countable
{
    protected array $items = [];

    public function __construct(array $data)
    {
        foreach ($data as $item) {
            $this->items[] = new FakeItem($item);
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
        return array_map(fn(FakeItem $i) => $i->data, $this->items);
    }
}
