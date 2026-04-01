<?php

require __DIR__ . '/../vendor/autoload.php';

use Baueri\AIFaker\Generator\Fake;
use Baueri\AIFaker\Cache\FileCacheManager;
use Baueri\AIFaker\Providers\OpenAIProvider;

$provider = new OpenAIProvider('YOUR_API_KEY');
$cache = new FileCacheManager(__DIR__ . '/cache');

/** Basic usage, fetch a single string value */

$fake = new Fake($provider, $cache);

echo $fake
    ->for('book title')
    ->language('en')
    ->one() . PHP_EOL; // returns a random book title

echo $fake
    ->for('book title')
    ->language('en')
    ->one(['genre' => 'Sci-fi']) . PHP_EOL; // returns a sci-fi book title

/** Query a data structure instead of a single text */

echo $fake
    ->for('book')
    ->language('hu')
    ->fields(['title', 'author', 'description', 'isbn'])
    ->one() . PHP_EOL;

/** Get a collection of generated items */

$data = $fake->for('school names')->count(5)->generate();

var_dump($data); echo PHP_EOL;

/** Set a batch to reduce the load */

$data = $fake->for('school names')
    ->count(10)
    ->batch(5)
    ->generate(); // this will fetch data from the api twice, each times gets 5-5 items

/** Full example  */

$data = $fake->for('movie')
    ->fields(['title', 'director', 'description', 'genres', 'imdb_rating'])
    ->context([
        'description' => ['3-5 paragraphs', '5-8 sentence per paragraph'],
        'genre_selection' => ['horror', 'sci-fi', 'comedy', 'drama', 'action', 'thriller', 'animation']
    ])
    ->batch(5)
    ->count(10)
    ->language('fr')
    ->maxRetries(3)
    ->generate();

/** With cursor */

$cursor = $fake->for('fictive brand')->count(10)->cursor();

foreach ($cursor as $item) {
    echo $item . PHP_EOL;
}

// or

while ($item = $cursor->fetch()) {
    echo $item . PHP_EOL;
}

/** Add some context to each iteration */

$cursor = $fake->for('fictive brand')->count(10)->cursor();
$categories = ['sport', 'jewelry', 'furniture'];

foreach ($categories as $category) {
    var_dump($cursor->fetch(['category' => $category]));
}

/** Add a tone */

echo $fake->for('letter')
    ->context(['blackmail', 'two paragraphs'])
    ->tone('friendly')
    ->one(['wants 1000 bitcoins']) . PHP_EOL;
