# PHP SSE Stream

A lightweight, framework-agnostic Server-Sent Events (SSE) streaming library for PHP.

## Installation

```bash
composer require theranken/php-sse-stream
```

## Usage

### Basic Example

```php
use Streamer\ServerSentEvents;

$sse = new ServerSentEvents();

// Send a single event
$sse->sendEvent([
    'message' => 'Hello, World!',
    'timestamp' => time()
]);

// Stream events
$sse->streamEvents(function() {
    return [
        'type' => 'status',
        'data' => [
            'message' => 'Periodic update',
            'timestamp' => time()
        ]
    ];
});

// Using static methods
ServerSentEvents::send([
    'message' => 'Static send example',
    'timestamp' => time()
]);

ServerSentEvents::stream(function() {
    return [
        'type' => 'status',
        'data' => [
            'message' => 'Static stream example',
            'timestamp' => time()
        ]
    ];
});
```

## Features

- Framework-agnostic
- Simple event streaming
- Customizable event types
- Connection timeout management

## Requirements

- PHP 7.4+
- Output buffering disabled


Usage in Different Frameworks:

```php

// Laravel
Route::get('/events', function () {
    $sse = new Streamer\ServerSentEvents();
    $sse->stream(fn() => ['type' => 'status', 'data' => ['message' => 'Laravel']]);
});

// Symfony
public function events()
{
    $sse = new Streamer\ServerSentEvents();
    $sse->stream(fn() => ['type' => 'status', 'data' => ['message' => 'Symfony']]);
}

// Leaf PHP
app()->get('/events', function () {
    $sse = new Streamer\ServerSentEvents();
    $sse->stream(fn() => ['type' => 'status', 'data' => ['message' => 'Leaf']]);
});


// Statically
ServerSentEvents::send([
    'type' => 'status',
    'data' => ['message' => 'Static example']
]);

ServerSentEvents::stream(fn() => [
    'type' => 'status',
    'data' => ['message' => 'Static stream']
]);


```

Key Benefits:
- Minimal dependencies
- Easy integration
- Framework-agnostic
- Lightweight implementation

Would you like me to elaborate on any aspect of creating this package?