<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Generator;

use Baueri\AIFaker\Contracts\AIProviderInterface;
use Baueri\AIFaker\Prompt\PromptBuilder;
use Baueri\AIFaker\Parser\ResponseParser;
use Baueri\AIFaker\Models\FakeCollection;
use Baueri\AIFaker\Cache\CacheInterface;
use Baueri\AIFaker\Models\FakeItem;

class Fake
{
    protected string $domain;
    protected array $fields = [];
    protected array $constraints = [];
    protected ?string $language = null;
    protected ?string $tone = null;
    protected int $count = 1;

    protected AIProviderInterface $provider;
    protected ?CacheInterface $cache = null;
    protected int $maxRetries = 3;

    public static function for(string $domain): self
    {
        $i = new self();
        $i->domain = $domain;
        return $i;
    }

    public function provider(AIProviderInterface $p): self
    {
        $this->provider = $p;
        return $this;
    }
    public function cache(CacheInterface $c): self
    {
        $this->cache = $c;
        return $this;
    }
    public function maxRetries(int $r): self
    {
        $this->maxRetries = $r;
        return $this;
    }
    public function fields(array $f): self
    {
        $this->fields = $f;
        return $this;
    }
    public function constraints(array $c): self
    {
        $this->constraints = $c;
        return $this;
    }
    public function language(string $l): self
    {
        $this->language = $l;
        return $this;
    }
    public function tone(string $t): self
    {
        $this->tone = $t;
        return $this;
    }

    public function count(int $c): self
    {
        if ($c < 1 || $c > 50) throw new \InvalidArgumentException();
        $this->count = $c;
        return $this;
    }

    public function generate(): FakeCollection
    {
        $builder = new PromptBuilder();
        $parser = new ResponseParser();
        $agg = new ResultAggregator($this->maxRetries);

        $state = [
            'domain' => $this->domain,
            'fields' => $this->fields,
            'constraints' => $this->constraints,
            'language' => $this->language,
            'tone' => $this->tone,
        ];

        $data = $agg->collect($this->count, function ($missing, $existing) use ($builder, $parser, $state) {

            $prompt = $builder->build([
                ...$state,
                'count' => $missing,
                'existing' => $existing,
            ], !empty($existing));

            $key = md5($prompt);

            if ($this->cache && $cached = $this->cache->get($key)) {
                return $cached;
            }

            $raw = $this->provider->generate($prompt);

            try {
                $parsed = $parser->parse($raw, $state['fields']);

                if ($this->cache) {
                    $this->cache->set($key, $parsed);
                }

                return $parsed;
            } catch (\Exception) {
                return [];
            }
        });

        return new FakeCollection($data);
    }

    public function generateOne(): FakeItem
    {
        return $this->count(1)
            ->generate()[0] ?? null;
    }
}
