<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Generator;

use Baueri\AIFaker\Contracts\AIProviderInterface;
use Baueri\AIFaker\Cache\CacheInterface;

class Fake
{
    /**
     * @param AIProviderInterface   $provider AI provider used to execute prompts.
     * @param CacheInterface|null  $cache    Optional cache for parsed JSON results.
     */
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
            cache: array_key_exists('cache', $overrides) ? $overrides['cache'] : $this->cache,
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

    /**
     * Set the generation "domain" (what to generate).
     *
     * Examples: "book title", "school names", "movie", "product".
     */
    public function for(string $domain): self
    {
        return $this->with(['domain' => $domain]);
    }

    /**
     * Request structured objects (instead of strings) with required fields.
     *
     * If omitted, items are generated as strings.
     *
     * @param list<string> $fields
     */
    public function fields(array $fields): self
    {
        return $this->with(['fields' => $fields]);
    }

    /**
     * Provide persistent context that is included in every prompt.
     *
     * @param array<string, mixed> $context
     */
    public function context(array $context): self
    {
        return $this->with(['context' => $context]);
    }

    /**
     * Set output language hint (e.g. "en", "fr", "hu").
     */
    public function language(string $language): self
    {
        return $this->with(['language' => $language]);
    }

    /**
     * Set output tone hint (e.g. "friendly", "formal").
     */
    public function tone(string $tone): self
    {
        return $this->with(['tone' => $tone]);
    }

    /**
     * Total number of items to generate.
     *
     * @throws \InvalidArgumentException
     */
    public function count(int $count): self
    {
        if ($count < 1) {
            throw new \InvalidArgumentException('Count must be >= 1');
        }

        return $this->with(['count' => $count]);
    }

    /**
     * Batch size used by the cursor when generating many items.
     *
     * Example: count(20)->batch(5) will generate 4 chunks of 5 items.
     *
     * @throws \InvalidArgumentException
     */
    public function batch(int $batch): self
    {
        if ($batch < 1) {
            throw new \InvalidArgumentException('Batch must be >= 1');
        }

        return $this->with(['batch' => $batch]);
    }

    /**
     * Maximum number of generation attempts (including retries for missing items).
     */
    public function maxRetries(int $retries): self
    {
        return $this->with(['maxRetries' => $retries]);
    }

    /**
     * Disable cache reads/writes for this immutable chain.
     *
     * This does not mutate the original instance; it returns a new instance
     * whose cursor/generate/one calls will bypass caching.
     */
    public function withoutCache(): self
    {
        return $this->with(['cache' => null]);
    }

    /**
     * Create a lazy cursor that generates items as you iterate / fetch.
     *
     * @throws \Exception
     */
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

    /**
     * Generate all requested items and return them as a collection.
     *
     * @param array<string, mixed>|callable():array<string, mixed> $context Extra context merged per fetch.
     */
    public function generate(array|callable $context = []): \Baueri\AIFaker\Models\FakeCollection
    {
        $cursor = $this->cursor();

        $items = [];

        while ($item = $cursor->fetch($context)) {
            $items[] = $item;
        }

        return new \Baueri\AIFaker\Models\FakeCollection($items);
    }

    /**
     * Generate a single item.
     *
     * - If fields() is set: returns an associative array.
     * - Otherwise: returns a string.
     *
     * @param array<string, mixed>|callable():array<string, mixed> $context Extra context passed for this call.
     */
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
