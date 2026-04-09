<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Generator;

use Baueri\AIFaker\Contracts\AIProviderInterface;
use Baueri\AIFaker\Cache\CacheInterface;
use Baueri\AIFaker\Prompt\PromptBuilder;
use Baueri\AIFaker\Parser\ResponseParser;

class FakeCursor implements \Iterator
{
    protected array $buffer = [];
    protected int $generated = 0;
    protected int $position = 0;
    protected null|array|string $current = null;

    protected PromptBuilder $builder;
    protected ResponseParser $parser;
    protected ResultAggregator $aggregator;

    /**
     * @param AIProviderInterface  $provider
     * @param CacheInterface|null $cache
     * @param array<string, mixed> $state
     * @param int $total Total number of items to emit.
     * @param int $batch Batch size when filling the internal buffer.
     * @param int $maxRetries Maximum attempts used by retry/aggregation logic.
     */
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

    /**
     * Fetch the next generated item.
     *
     * If $extraContext is provided, it is merged into the cursor's base context
     * for this single fetch (and generated immediately, without using the buffer).
     *
     * @param array<string, mixed>|callable():array<string, mixed> $extraContext
     * @return null|array|string
     */
    public function fetch(array|callable $extraContext = []): null|array|string
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

    protected function fetchFromBuffer(): null|array|string
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

        return $item;
    }

    protected function fetchWithContext(array $context): null|array|string
    {
        $this->buffer = [];

        $data = $this->aggregator->collect(1, function () use ($context) {

            $prompt = $this->builder->build([
                ...$this->state,
                'context' => array_merge($this->state['context'], $context),
                'count' => 1,
            ]);

            $key = md5($prompt);

            if ($this->cache) {
                $cached = $this->cache->get($key);
                if ($cached !== null) {
                    return $cached;
                }
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

        return $item;
    }

    protected function fillBuffer(): void
    {
        $remaining = $this->total - $this->generated;

        if ($remaining <= 0) {
            return;
        }

        $count = min($this->batch, $remaining);
        $offset = $this->generated;

        $data = $this->aggregator->collect($count, function ($missing, $existing) use ($offset) {

            $prompt = $this->builder->build([
                ...$this->state,
                'count' => $missing,
                'offset' => $offset,
                'existing' => $existing,
            ], !empty($existing));

            $key = md5($prompt);

            if ($this->cache) {
                $cached = $this->cache->get($key);
                if ($cached !== null) {
                    return $cached;
                }
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

    /**
     * Zero-based index of the most recently generated item.
     */
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

    /**
     * @return null|array|string
     */
    public function current(): null|array|string
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
