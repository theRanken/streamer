<?php

namespace Ranken\Streamer;



/**
 * Server-Sent Events (SSE) class
 * 
 * This class provides a simple way to send Server-Sent Events (SSE) to clients.
 * 
 * Usage:
 * 
 * // Send a message event
 * ServerSentEvents::send('Hello, world!');
 * 
 * // Send a status event
 * ServerSentEvents::send('Server is running', 'status');
 * 
 * // Send an update event with an ID
 * ServerSentEvents::send('New message', 'update', '123');
 * 
 * // Stream events
 * ServerSentEvents::stream(function() {
 *     return [
 *         'data' => 'New message',
 *         'type' => 'update'
 *     ];
 * });
 */
class ServerSentEvents {

    // Configuration properties
    private $maxClients = 500;
    private $connectionTimeout = 300;
    private $allowedEventTypes = ['message', 'status', 'update', 'error'];

    /**
     * Send an event to clients
     * 
     * @param mixed $data
     * @param string $eventType
     * @param string|null $id
     * @return mixed
     */
    public static function send($data, string $eventType = 'message', ?string $id = null): mixed
    {
        $instance = new self();
        return $instance->sendEvent($data, $eventType, $id);
    }

    /**
     * Stream events to clients
     * 
     * @param callable $eventGenerator
     * @param int $timeout
     * @return mixed
     */
    public static function stream(callable $eventGenerator, int $timeout = 300): mixed
    {
        $instance = new self();
        return $instance->streamEvents($eventGenerator);
    }

    /**
     * Set the connection timeout
     * 
     * @param int $timeout
     * @return self
     */
    public function setTimeout(int $timeout): self
    {
        $this->connectionTimeout = $timeout;
        return $this;
    }

    /**
     * Get the connection timeout
     * 
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->connectionTimeout;
    }

    /**
     * Set the allowed event types
     * 
     * @param string|array $eventType
     * @return self
     */
    public function addEventType(string|array $eventType): self
    {
        $this->allowedEventTypes = array_merge(
            $this->allowedEventTypes, 
            is_array($eventType) ? $eventType : [$eventType]
        );
        return $this;
    }

    /**
     * Set Max Clients
     * 
     * @param int $maxClients
     * @return self
     */
    public function setMaxClients(int $maxClients): self
    {
        $this->maxClients = $maxClients;
        return $this;
    }

    /**
     * Get Max Clients
     * 
     * @return int
     */
    public function getMaxClients(): int
    {
        return $this->maxClients;
    }

    /**
     * Send an event to clients
     * 
     * @param mixed $data
     * @param string $eventType
     * @param string|null $id
     * @return self
     */
    public function sendEvent($data, string $eventType = 'message', ?string $id = null): self
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

    /**
     * Stream events to clients
     * 
     * @param callable $eventGenerator
     * @return mixed
     */
    public function streamEvents(callable $eventGenerator): mixed
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