# PHP SSE Stream

A lightweight, framework-agnostic Server-Sent Events (SSE) streaming library for PHP.

## Installation

```bash
composer require theranken/php-sse-stream
```

## Usage

### Basic Example

```php
use SSEStream\ServerSentEvents;

$sse = new ServerSentEvents();

// Send a single event
$sse->send([
    'message' => 'Hello, World!',
    'timestamp' => time()
]);

// Stream events
$sse->stream(function() {
    return [
        'type' => 'status',
        'data' => [
            'message' => 'Periodic update',
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
    $sse = new SSEStream\ServerSentEvents();
    $sse->stream(fn() => ['type' => 'status', 'data' => ['message' => 'Laravel']]);
});

// Symfony
public function events()
{
    $sse = new SSEStream\ServerSentEvents();
    $sse->stream(fn() => ['type' => 'status', 'data' => ['message' => 'Symfony']]);
}

// Leaf PHP
app()->get('/events', function () {
    $sse = new SSEStream\ServerSentEvents();
    $sse->stream(fn() => ['type' => 'status', 'data' => ['message' => 'Leaf']]);
});
```

Key Benefits:
- Minimal dependencies
- Easy integration
- Framework-agnostic
- Lightweight implementation

Would you like me to elaborate on any aspect of creating this package?