<?php

namespace Streamer;

class ServerSentEvents {
    // Configuration properties
    private $maxClients = 100;
    private $connectionTimeout = 300;
    private $allowedEventTypes = ['message', 'status', 'update', 'error'];

    // Static method wrappers
    public static function send($data, string $eventType = 'message', ?string $id = null)
    {
        $instance = new self();
        return $instance->sendEvent($data, $eventType, $id);
    }

    public static function stream(callable $eventGenerator)
    {
        $instance = new self();
        return $instance->streamEvents($eventGenerator);
    }

    // Instance methods with different names to avoid conflicts
    public function sendEvent($data, string $eventType = 'message', ?string $id = null)
    {
        // Disable output buffering
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Set SSE headers
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        // Validate event type
        $eventType = in_array($eventType, $this->allowedEventTypes) 
            ? $eventType 
            : 'message';

        // Prepare event
        $output = [];
        
        if ($id !== null) {
            $output[] = "id: $id";
        }
        
        $output[] = "event: $eventType";
        $output[] = "data: " . json_encode($data);
        $output[] = "\n";

        // Send event
        echo implode("\n", $output);
        
        // Flush output
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            ob_flush();
            flush();
        }

        return $this;
    }

    public function streamEvents(callable $eventGenerator)
    {
        // Disable script timeout
        set_time_limit(0);
        
        $startTime = time();

        try {
            while (true) {
                // Check for connection timeout
                if (time() - $startTime > $this->connectionTimeout) {
                    break;
                }

                // Generate event
                $event = $eventGenerator();
                
                // Send event if generated
                if ($event) {
                    $this->sendEvent(
                        $event['data'] ?? null, 
                        $event['type'] ?? 'message'
                    );
                }

                // Prevent high CPU usage
                sleep(1);

                // Check if client disconnected
                if (function_exists('connection_aborted') && connection_aborted()) {
                    break;
                }
            }
        } catch (\Exception $e) {
            // Error handling
            $this->sendEvent([
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ], 'error');
        }

        exit();
    }
}