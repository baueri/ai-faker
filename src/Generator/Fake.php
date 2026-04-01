<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Generator;

use Baueri\AIFaker\Contracts\AIProviderInterface;
use Baueri\AIFaker\Cache\CacheInterface;

class Fake
{
    public function __construct(
        protected AIProviderInterface $provider,
        protected ?CacheInterface $cache = null,
        protected string $domain = '',
        protected array $fields = [],
        protected array $context = [],
        protected ?string $language = null,
        protected ?string $tone = null,
        protected int $count = 1,
        protected int $batch = 0,
        protected int $maxRetries = 3,
    ) {}

    protected function with(array $overrides): self
    {
        return new self(
            provider: $overrides['provider'] ?? $this->provider,
            cache: $overrides['cache'] ?? $this->cache,
            domain: $overrides['domain'] ?? $this->domain,
            fields: $overrides['fields'] ?? $this->fields,
            context: $overrides['context'] ?? $this->context,
            language: $overrides['language'] ?? $this->language,
            tone: $overrides['tone'] ?? $this->tone,
            count: $overrides['count'] ?? $this->count,
            batch: $overrides['batch'] ?? $this->batch,
            maxRetries: $overrides['maxRetries'] ?? $this->maxRetries,
        );
    }

    public function for(string $domain): self
    {
        return $this->with(['domain' => $domain]);
    }

    public function fields(array $fields): self
    {
        return $this->with(['fields' => $fields]);
    }

    public function context(array $context): self
    {
        return $this->with(['context' => $context]);
    }

    public function language(string $language): self
    {
        return $this->with(['language' => $language]);
    }

    public function tone(string $tone): self
    {
        return $this->with(['tone' => $tone]);
    }

    public function count(int $count): self
    {
        if ($count < 1) {
            throw new \InvalidArgumentException('Count must be >= 1');
        }

        return $this->with(['count' => $count]);
    }

    public function batch(int $batch): self
    {
        if ($batch < 1) {
            throw new \InvalidArgumentException('Batch must be >= 1');
        }

        return $this->with(['batch' => $batch]);
    }

    public function maxRetries(int $retries): self
    {
        return $this->with(['maxRetries' => $retries]);
    }

    public function cursor(): FakeCursor
    {
        if ($this->count < 1) {
            throw new \Exception('Count must be set');
        }

        return new FakeCursor(
            provider: $this->provider,
            cache: $this->cache,
            state: $this->buildState(),
            total: $this->count,
            batch: $this->batch ?: $this->count,
            maxRetries: $this->maxRetries
        );
    }

    public function generate(array|callable $context = []): \Baueri\AIFaker\Models\FakeCollection
    {
        $cursor = $this->cursor();

        $items = [];

        while ($item = $cursor->fetch($context)) {
            $items[] = $item;
        }

        return new \Baueri\AIFaker\Models\FakeCollection($items);
    }

    public function one(array|callable $context = []): null|array|string
    {
        $cursor = $this
            ->count(1)
            ->batch(1)
            ->cursor();

        return $cursor->fetch($context);
    }

    protected function buildState(): array
    {
        return [
            'domain' => $this->domain,
            'fields' => $this->fields,
            'context' => $this->context,
            'language' => $this->language,
            'tone' => $this->tone,
        ];
    }
}
