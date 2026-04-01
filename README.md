# AI Faker PHP

AI Faker PHP is a lightweight, provider-agnostic PHP library for generating structured fake data using AI.

> ⚠️ This package was largely generated with the help of AI, including code, structure, and documentation.

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

## 🚀 Basic Usage

The package provides a flexible, fluent interface for generating AI-powered fake data. You can generate simple strings, structured data, or full collections with minimal configuration.

### 1. Initialization

Start by creating a provider and (optionally) a cache manager:

```php
use Baueri\AIFaker\Generator\Fake;
use Baueri\AIFaker\Providers\OpenAIProvider;
use Baueri\AIFaker\Cache\FileCacheManager;

$provider = new OpenAIProvider('your-api-key');
$cache = new FileCacheManager(__DIR__ . '/cache');

$fake = new Fake($provider, $cache);
```
### 2. Generate a Single Value

Generate a simple string:

```php
echo $fake
    ->for('book title')
    ->language('en')
    ->one();

// Add input parameters to influence the output:

echo $fake
    ->for('book title')
    ->language('en')
    ->one(['genre' => 'Sci-fi']);
```

### 3. Generate Structured Data

Request multiple fields instead of a single string:

```php
echo $fake
    ->for('book')
    ->language('hu')
    ->fields(['title', 'author', 'description', 'isbn'])
    ->one();
```

### 4. Generate Multiple Items

Generate a collection:

```php
$data = $fake
    ->for('school names')
    ->count(5)
    ->generate();

// Use batching to reduce API load:

$data = $fake
    ->for('school names')
    ->count(10)
    ->batch(5)
    ->generate();
```

### 5. Add Context and Constraints

You can guide generation with additional context:

```php
$data = $fake
    ->for('movie')
    ->fields(['title', 'director', 'description', 'genres', 'imdb_rating'])
    ->context([
        'description' => ['3-5 paragraphs', '5-8 sentences per paragraph'],
        'genre_selection' => ['horror', 'sci-fi', 'comedy']
    ])
    ->count(10)
    ->batch(5)
    ->language('fr')
    ->generate();
```

### 6. Streaming with Cursor

Iterate over results lazily:

```php
$cursor = $fake
    ->for('fictive brand')
    ->count(10)
    ->cursor();

foreach ($cursor as $item) {
    echo $item . PHP_EOL;
}

// Or fetch manually:

while ($item = $cursor->fetch()) {
    echo $item . PHP_EOL;
}

// You can also pass dynamic input per iteration:

$categories = ['sport', 'jewelry', 'furniture'];

foreach ($categories as $category) {
    var_dump($cursor->fetch(['category' => $category]));
}
```

### 7. Tone Control

Adjust the tone of the generated content:

```php
echo $fake
    ->for('letter')
    ->context(['blackmail', 'two paragraphs'])
    ->tone('friendly')
    ->one(['wants 1000 bitcoins']);
```

## 🧠 API

### Core Methods
- `for(string $domain)`
- `fields(array $fields)`
- `context(array $context)`
- `language(string $lang)`
- `tone(string $tone)`
- `count(int $count)`
- `maxRetries(int $retries)`
- `cache(CacheInterface $cache)`
- `provider(AIProviderInterface $provider)`
- `generate($conext)`
- `one($context)`
- `cursor()`

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

## 🤝 Contributing

Contributions are welcome!

This project was largely generated with the help of AI, but it is actively maintained and improved by humans.  
If you find issues, inconsistencies, or have ideas for improvements, feel free to open an issue or submit a pull request.

### Guidelines

- Keep the API simple and consistent
- Prefer explicit, structured solutions over "magic"
- Ensure new features don’t break existing behavior
- Add clear examples when introducing new functionality

### Areas for Improvement

- Additional AI providers (Claude, local models, etc.)
- More robust schema/type support
- Additional cache drivers (Redis, memory)
- Better error handling and edge case coverage

---

Thanks for helping improve the project 🚀