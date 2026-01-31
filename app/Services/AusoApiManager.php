<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class AusoApiManager
{
    protected string $baseUrl;
    protected string $username;
    protected string $password;
    protected int $timeout;
    protected int $retryAttempts;

    public function __construct()
    {
        $this->baseUrl = config('services.auso.url');
        $this->username = config('services.auso.username');
        $this->password = config('services.auso.password');
        $this->timeout = config('services.auso.timeout', 30);
        $this->retryAttempts = config('services.auso.retry_attempts', 3);

        if (empty($this->baseUrl)) {
            throw new Exception('Auso API URL not configured');
        }
    }

    /**
     * Make an authenticated GET request to the Auso API
     */
    public function get(string $endpoint, array $query = []): mixed
    {
        return $this->request('GET', $endpoint, $query);
    }

    /**
     * Make an authenticated POST request to the Auso API
     */
    public function post(string $endpoint, array $data = []): mixed
    {
        return $this->request('POST', $endpoint, [], $data);
    }

    /**
     * Make an authenticated PUT request to the Auso API
     */
    public function put(string $endpoint, array $data = []): mixed
    {
        return $this->request('PUT', $endpoint, [], $data);
    }

    /**
     * Make an authenticated DELETE request to the Auso API
     */
    public function delete(string $endpoint): mixed
    {
        return $this->request('DELETE', $endpoint);
    }

    /**
     * Make a request to the Auso API with retry logic
     */
    protected function request(string $method, string $endpoint, array $query = [], array $data = []): mixed
    {
        $url = $this->buildUrl($endpoint, $query);
        $attempt = 0;

        while ($attempt < $this->retryAttempts) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withBasicAuth($this->username, $this->password)
                    ->acceptJson()
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                    ]);

                $response = match ($method) {
                    'GET' => $response->get($url),
                    'POST' => $response->post($url, $data),
                    'PUT' => $response->put($url, $data),
                    'DELETE' => $response->delete($url),
                    default => throw new Exception("Unsupported HTTP method: $method"),
                };

                if ($response->failed()) {
                    throw new Exception(
                        "Auso API request failed: {$response->status()} - {$response->body()}"
                    );
                }

                return $response->json();
            } catch (Exception $e) {
                $attempt++;

                if ($attempt >= $this->retryAttempts) {
                    throw $e;
                }

                sleep(pow(2, $attempt - 1)); // Exponential backoff
            }
        }
    }

    /**
     * Build the full URL for an endpoint
     */
    protected function buildUrl(string $endpoint, array $query = []): string
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');

        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $url;
    }

    /**
     * Create a new extension with multipart form data
     */
    public function createExtension(array $data): int
    {
        try {
            $url = $this->buildUrl('/auExtenAPI/create_exten.php');
            --dd($url, $data);
            $response = Http::timeout($this->timeout)
                ->withBasicAuth($this->username, $this->password)
                ->asMultipart()
                ->post($url, $data);

            if ($response->failed()) {
                throw new Exception(
                    "Failed to create extension: {$response->status()} - {$response->body()}"
                );
            }

            return $response->status();
        } catch (Exception $e) {
            throw $e;
        }
    }
}
