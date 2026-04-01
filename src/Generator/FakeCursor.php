<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Generator;

use Baueri\AIFaker\Contracts\AIProviderInterface;
use Baueri\AIFaker\Cache\CacheInterface;
use Baueri\AIFaker\Prompt\PromptBuilder;
use Baueri\AIFaker\Parser\ResponseParser;
use Baueri\AIFaker\Models\FakeItem;

class FakeCursor implements \Iterator
{
    protected array $buffer = [];
    protected int $generated = 0;
    protected int $position = 0;
    protected mixed $current = null;

    protected PromptBuilder $builder;
    protected ResponseParser $parser;
    protected ResultAggregator $aggregator;

    public function __construct(
        protected AIProviderInterface $provider,
        protected ?CacheInterface $cache,
        protected array $state,
        protected int $total,
        protected int $batch,
        protected int $maxRetries
    ) {
        $this->builder = new PromptBuilder();
        $this->parser = new ResponseParser();
        $this->aggregator = new ResultAggregator($maxRetries);
    }

    public function fetch(array|callable $extraContext = []): mixed
    {
        if ($this->generated >= $this->total) {
            return null;
        }

        if (is_callable($extraContext)) {
            $extraContext = $extraContext();
        }

        if (!empty($extraContext)) {
            return $this->fetchWithContext($extraContext);
        }

        return $this->fetchFromBuffer();
    }

    protected function fetchFromBuffer(): mixed
    {
        if (empty($this->buffer)) {
            $this->fillBuffer();
        }

        if (empty($this->buffer)) {
            $this->generated = $this->total;
            return null;
        }

        $item = array_shift($this->buffer);
        $this->generated++;

        return is_array($item)
            ? new FakeItem($item)
            : $item;
    }

    protected function fetchWithContext(array $context): mixed
    {
        $this->buffer = [];

        $data = $this->aggregator->collect(1, function () use ($context) {

            $prompt = $this->builder->build([
                ...$this->state,
                'context' => array_merge($this->state['context'], $context),
                'count' => 1,
            ]);

            $key = md5($prompt);

            if ($this->cache && $cached = $this->cache->get($key)) {
                return $cached;
            }

            $raw = $this->provider->generate($prompt);

            try {
                $parsed = $this->parser->parse($raw, $this->state['fields']);

                if ($this->cache) {
                    $this->cache->set($key, $parsed);
                }

                return $parsed;
            } catch (\Exception) {
                return [];
            }
        });

        $item = $data[0] ?? null;

        if ($item === null) {
            return null;
        }

        $this->generated++;

        return is_array($item)
            ? new FakeItem($item)
            : $item;
    }

    protected function fillBuffer(): void
    {
        $remaining = $this->total - $this->generated;

        if ($remaining <= 0) {
            return;
        }

        $count = min($this->batch, $remaining);

        $data = $this->aggregator->collect($count, function ($missing, $existing) {

            $prompt = $this->builder->build([
                ...$this->state,
                'count' => $missing,
                'existing' => $existing,
            ], !empty($existing));

            $key = md5($prompt);

            if ($this->cache && $cached = $this->cache->get($key)) {
                return $cached;
            }

            $raw = $this->provider->generate($prompt);

            try {
                $parsed = $this->parser->parse($raw, $this->state['fields']);

                if ($this->cache) {
                    $this->cache->set($key, $parsed);
                }

                return $parsed;
            } catch (\Exception) {
                return [];
            }
        });

        $this->buffer = $data;
    }

    public function index(): int
    {
        return max(0, $this->generated - 1);
    }

    // =========================
    // Iterator implementation
    // =========================

    public function rewind(): void
    {
        $this->buffer = [];
        $this->generated = 0;
        $this->position = 0;

        $this->current = $this->fetch();
    }

    public function current(): mixed
    {
        return $this->current;
    }

    public function key(): int
    {
        return max(0, $this->generated - 1);
    }

    public function next(): void
    {
        $this->current = $this->fetch();
        $this->position++;
    }

    public function valid(): bool
    {
        return $this->current !== null;
    }
}
