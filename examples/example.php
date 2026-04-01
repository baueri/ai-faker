<?php

require __DIR__ . '/../vendor/autoload.php';

use Baueri\AIFaker\Generator\Fake;
use Baueri\AIFaker\Providers\OpenAIProvider;
use Baueri\AIFaker\Cache\FileCacheManager;
use Baueri\AIFaker\Models\FakeItem;

/**
 * @param IteratorAggregate<FakeItem> $data
 */
function dump_fake_data($data) {
    foreach ($data as $item) {
        if (is_string($item->data)) {
            echo $item->data . PHP_EOL;
        } else {
            foreach ($item->data as $k => $v) {
                echo $k . ': ' . PHP_EOL . $v . PHP_EOL;    
            }
        }
        echo PHP_EOL . '----------------------' . PHP_EOL . PHP_EOL;
    }
}

$provider = new OpenAIProvider('YOUR_API_KEY');
$cache = new FileCacheManager(__DIR__ . '/cache');

// Basic usage, each generated element is a simple string

$data = Fake::for('fictive elementary school names')
    ->provider($provider)
    ->cache($cache)
    ->count(5)
    ->generate();

dump_fake_data($data);

// More complex usage

$data = Fake::for('fictive elementary school names')
    ->fields(['name', 'address', 'chairman', 'website'])
    ->provider($provider)
    ->cache($cache)
    ->constraints(['country' => 'Hungary'])
    ->language('hu')
    ->count(5)
    ->generate();

dump_fake_data($data);

// Add some more constraint

$data = Fake::for('books')
    ->provider($provider)
    ->cache($cache)
    ->fields(['title', 'description'])
    ->constraints([
        'genres' => ['sci-fi', 'romantic'],
        'number_of_sentences' => '10'
    ])
    ->language('en')
    ->tone('casual')
    ->count(5)
    ->maxRetries(3)
    ->generate();

dump_fake_data($data);
