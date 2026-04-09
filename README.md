# AI Faker (PHP)

Provider-agnostic fake data generation for PHP using LLMs.

Generate either:

- simple lists of strings (e.g. names, titles, tags)
- structured objects (e.g. `{title, author, isbn}`) with required fields

The library asks the model for a **JSON array only**, validates/parses it, retries when needed, and optionally caches results.

> Note: parts of this project were generated with AI assistance and are iterated on by humans.

## Requirements

- PHP **8.1+**
- PHP extensions: **`ext-json`**, **`ext-curl`**

## Installation

```bash
composer require baueri/ai-faker
```

## Quick start

## Available providers

Built-in providers in `src/Providers/`:

- `Baueri\AIFaker\Providers\OpenAIProvider` (OpenAI Responses API)
- `Baueri\AIFaker\Providers\AnthropicProvider` (Anthropic Claude Messages API)
- `Baueri\AIFaker\Providers\OllamaProvider` (local Ollama server)
- `Baueri\AIFaker\Providers\GoogleAIStudioProvider` (Gemini via Google AI Studio)

### 1) Choose a provider

```php
use Baueri\AIFaker\Generator\Fake;
use Baueri\AIFaker\Providers\OpenAIProvider;
use Baueri\AIFaker\Cache\FileCacheManager;

$provider = new OpenAIProvider('YOUR_OPENAI_API_KEY');
$cache = new FileCacheManager(__DIR__ . '/cache'); // optional but recommended

$fake = new Fake($provider, $cache);
```

### 2) Generate a single value (string mode)

```php
echo $fake
    ->for('book title')
    ->language('en')
    ->one();
```

You can pass extra context at call time:

```php
echo $fake
    ->for('book title')
    ->language('en')
    ->one(['genre' => 'sci-fi']);
```

### 3) Generate a single structured object (fields mode)

```php
$book = $fake
    ->for('book')
    ->fields(['title', 'author', 'isbn'])
    ->language('hu')
    ->one();

var_dump($book);
```

### 4) Generate multiple items

```php
$items = $fake
    ->for('school names')
    ->count(10)
    ->generate();

var_dump($items->toArray());
```

### 5) Batching (recommended for larger counts)

Batching splits the generation into smaller chunks to reduce payload size and improve reliability:

```php
$items = $fake
    ->for('school names')
    ->count(20)
    ->batch(5)
    ->generate();
```

When caching is enabled, each batch chunk uses a distinct prompt key (so batch 1/2/3/4 do not collide in cache).

### 6) Disable cache for a single chain (`withoutCache()`)

Sometimes you want fresh generations even if a cache entry exists (e.g. debugging prompts).

`withoutCache()` disables cache reads/writes for **that immutable chain only**:

```php
$cached = $fake->for('book title')->count(5)->generate();

$fresh = $fake->for('book title')
    ->withoutCache()
    ->count(5)
    ->generate();
```

The original `$fake` instance remains unchanged (no need to “re-enable” caching).

### 7) Cursor (lazy streaming)

```php
$cursor = $fake
    ->for('fictive brand')
    ->count(10)
    ->batch(5)
    ->cursor();

foreach ($cursor as $item) {
    echo $item . PHP_EOL;
}
```

You can also pass per-iteration context:

```php
$categories = ['sport', 'jewelry', 'furniture'];
$cursor = $fake->for('fictive brand')->count(3)->cursor();

foreach ($categories as $category) {
    var_dump($cursor->fetch(['category' => $category]));
}
```

### 7) Tone + context

```php
echo $fake
    ->for('letter')
    ->tone('friendly')
    ->context(['two paragraphs'])
    ->one(['topic' => 'asking for a refund']);
```

## API overview

Fluent configuration (immutable builder style):

- `for(string $domain)`
- `fields(array $fields)` (omit for string mode)
- `context(array $context)`
- `language(string $language)`
- `tone(string $tone)`
- `count(int $count)`
- `batch(int $batchSize)`
- `maxRetries(int $attempts)`

Execution:

- `one(array|callable $context = []): null|array|string`
- `generate(array|callable $context = []): Baueri\AIFaker\Models\FakeCollection`
- `cursor(): Baueri\AIFaker\Generator\FakeCursor`

## Retry + deduplication

If the provider returns fewer valid items than requested, the library retries and asks only for the missing amount. Results are merged with basic deduplication.

## Caching

`FileCacheManager` stores parsed JSON arrays keyed by a hash of the prompt.

Caching is best used for:

- local development / prototyping
- test data seeding
- reducing costs while iterating on prompts

## Writing a custom provider

Implement `Baueri\AIFaker\Contracts\AIProviderInterface`:

```php
use Baueri\AIFaker\Contracts\AIProviderInterface;

final class MyProvider implements AIProviderInterface
{
    public function generate(string $prompt): string
    {
        return '["example"]';
    }
}
```

## Development

### Run unit tests

This project uses PHPUnit (PHP 8.1 compatible).

```bash
composer install
./vendor/bin/phpunit
```

## License

MIT. See `LICENSE`.
