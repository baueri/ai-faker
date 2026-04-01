# AI Faker PHP

AI Faker PHP is a lightweight, provider-agnostic PHP library for generating structured fake data using AI.

## ✨ Features

- AI-powered fake data generation
- Fluent API
- Structured JSON output
- Retry mechanism for missing items
- Deduplication
- Chunk-level caching
- Language & tone support
- Constraint-based generation

---

## 📦 Installation

```bash
composer require baueri/ai-faker
```

## 🚀 Usage

```php

<?php

require __DIR__ . '/../vendor/autoload.php';

use Baueri\AIFaker\Generator\Fake;
use Baueri\AIFaker\Providers\OpenAIProvider;
use Baueri\AIFaker\Cache\FileCacheManager;
use Baueri\AIFaker\Models\FakeItem;

$provider = new OpenAIProvider('YOUR_API_KEY');
$cache = new FileCacheManager(__DIR__ . '/cache');

// Basic usage, returns a single item

$data = Fake::for('fictive elementary school names')
    ->provider($provider)
    ->generateOne();

// Return multiple items
$data = Fake::for('fictive elementary school names')
    ->provider($provider)
    ->count(5)
    ->generate();

// Fully featured usage

$data = Fake::for('books')
    ->provider($provider)
    ->cache($cache) // cache the result for the prompt to reduce api usage
    ->fields(['title', 'description']) // generates a list of structured data
    ->constraints([
        'genres' => ['sci-fi', 'romantic'],
        'number_of_sentences' => '10'
    ]) // add some other context for the prompt
    ->language('en') // set language
    ->tone('casual') // set tone (casual, formal, etc)
    ->count(5) // set the number of items to generate
    ->maxRetries(3) // set how many times to retry in case the endpoint cannot generate all items in one api call 
    ->generate();
```

## 🧠 API

### Core Methods
- `for(string $domain)`
- `fields(array $fields)`
- `constraints(array $constraints)`
- `language(string $lang)`
- `tone(string $tone)`
- `count(int $count)`
- `maxRetries(int $retries)`
- `cache(CacheInterface $cache)`
- `provider(AIProviderInterface $provider)`
- `generate()`

## 🔁 Retry Logic

The library automatically handles incomplete AI responses:

- If fewer items are returned than requested → retries
- Only requests the missing amount
-Stops when:
  - max retries reached
  - no progress detected

## 💾 Cache
- Stores parsed JSON
- Chunk-level caching
- Works across retries

## 🧩 Custom Provider

```php
class MyProvider implements AIProviderInterface {
    public function generate(string $prompt): string {
        return "...";
    }
}
```

## ⚠️ Notes
- AI output is validated but still depends on prompt quality
- Large datasets may require multiple retries
- Using cache is highly recommended in development