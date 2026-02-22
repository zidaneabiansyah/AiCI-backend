<?php

namespace App\Cache;

use GuzzleHttp\Client;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Support\Facades\Log;

class UpstashStore implements Store
{
    protected Client $client;
    protected string $baseUrl;
    protected string $token;

    public function __construct(string $baseUrl, string $token)
    {
        $this->baseUrl = $baseUrl;
        $this->token = $token;
        
        $this->client = new Client([
            'base_uri' => $baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Retrieve an item from the cache by key.
     */
    public function get($key): mixed
    {
        try {
            $response = $this->client->get("/get/{$key}");
            $data = json_decode($response->getBody()->getContents(), true);
            
            if (!empty($data['result'])) {
                return json_decode($data['result'], true);
            }
            
            return null;
        } catch (\Exception $e) {
            Log::warning('Upstash cache get error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get multiple items from the cache.
     */
    public function many(array $keys): array
    {
        $values = [];
        foreach ($keys as $key) {
            $values[$key] = $this->get($key);
        }
        return $values;
    }

    /**
     * Store an item in the cache for a given number of seconds.
     */
    public function put($key, $value, $seconds): void
    {
        try {
            $encoded = json_encode($value);
            
            if ($seconds === null) {
                // No expiration
                $this->client->post("/set/{$key}", [
                    'json' => ['value' => $encoded],
                ]);
            } else {
                // With expiration in seconds
                $this->client->post("/set/{$key}", [
                    'json' => [
                        'value' => $encoded,
                        'ex' => (int) $seconds,
                    ],
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Upstash cache put error: ' . $e->getMessage());
        }
    }

    /**
     * Store multiple items in the cache.
     */
    public function putMany(array $values, $seconds): void
    {
        foreach ($values as $key => $value) {
            $this->put($key, $value, $seconds);
        }
    }

    /**
     * Increment a value in the cache.
     */
    public function increment($key, $value = 1): int|bool
    {
        try {
            $response = $this->client->post("/incr/{$key}", [
                'json' => ['value' => $value],
            ]);
            $data = json_decode($response->getBody()->getContents(), true);
            return $data['result'] ?? false;
        } catch (\Exception $e) {
            Log::warning('Upstash cache increment error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Decrement a value in the cache.
     */
    public function decrement($key, $value = 1): int|bool
    {
        try {
            $response = $this->client->post("/decr/{$key}", [
                'json' => ['value' => $value],
            ]);
            $data = json_decode($response->getBody()->getContents(), true);
            return $data['result'] ?? false;
        } catch (\Exception $e) {
            Log::warning('Upstash cache decrement error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Store an item in the cache indefinitely.
     */
    public function forever($key, $value): void
    {
        $this->put($key, $value, null);
    }

    /**
     * Remove an item from the cache.
     */
    public function forget($key): bool
    {
        try {
            $this->client->post("/del/{$key}");
            return true;
        } catch (\Exception $e) {
            Log::warning('Upstash cache forget error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove all items from the cache.
     */
    public function flush(): bool
    {
        try {
            $this->client->post('/flushdb');
            return true;
        } catch (\Exception $e) {
            Log::warning('Upstash cache flush error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the cache key prefix.
     */
    public function getPrefix(): string
    {
        return '';
    }
}
