<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseService
{
    protected string $url;
    protected string $anonKey;
    protected string $serviceKey;

    public function __construct()
    {
        $this->url = config('supabase.url');
        $this->anonKey = config('supabase.anon_key');
        $this->serviceKey = config('supabase.service_key');
    }

    protected function headers(bool $useServiceKey = false): array
    {
        return [
            'apikey' => $this->anonKey,
            'Authorization' => 'Bearer ' . ($useServiceKey ? $this->serviceKey : $this->anonKey),
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ];
    }

    public function authLogin(string $email, string $password): array
    {
        $response = Http::withHeaders([
            'apikey' => $this->anonKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->url}/auth/v1/token?grant_type=password", [
            'email' => $email,
            'password' => $password,
        ]);

        if ($response->failed()) {
            return ['success' => false, 'message' => 'Credenciales inválidas'];
        }

        $data = $response->json();

        return [
            'success' => true,
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'user' => $data['user'] ?? null,
        ];
    }

    public function authRegister(string $email, string $password): array
    {
        $response = Http::withHeaders([
            'apikey' => $this->anonKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->url}/auth/v1/signup", [
            'email' => $email,
            'password' => $password,
        ]);

        if ($response->failed()) {
            return ['success' => false, 'message' => $response->json('error_description') ?? 'Error al registrar'];
        }

        $data = $response->json();
        return ['success' => true, 'user' => $data['user'] ?? null];
    }

    public function query(string $table, array $params = []): array
    {
        $url = "{$this->url}/rest/v1/{$table}";
        $queryParams = ['select' => $params['select'] ?? '*'];

        if (isset($params['filters'])) {
            foreach ($params['filters'] as $column => $value) {
                $queryParams["{$column}"] = "eq.{$value}";
            }
        }
        if (isset($params['order'])) $queryParams['order'] = $params['order'];
        if (isset($params['limit'])) $queryParams['limit'] = $params['limit'];
        if (isset($params['offset'])) $queryParams['offset'] = $params['offset'];

        $response = Http::withHeaders($this->headers())->get($url, $queryParams);

        if ($response->failed()) {
            Log::error('Supabase query failed', ['table' => $table, 'status' => $response->status()]);
            return [];
        }
        return $response->json() ?? [];
    }

    public function insert(string $table, array $data): ?array
    {
        $response = Http::withHeaders($this->headers())->post("{$this->url}/rest/v1/{$table}", $data);
        if ($response->failed()) {
            Log::error('Supabase insert failed', ['table' => $table, 'status' => $response->status()]);
            return null;
        }
        $result = $response->json();
        return is_array($result) ? ($result[0] ?? $result) : null;
    }

    public function update(string $table, array $data, array $filters): bool
    {
        $url = "{$this->url}/rest/v1/{$table}";
        $queryParams = [];
        foreach ($filters as $column => $value) {
            $queryParams["{$column}"] = "eq.{$value}";
        }
        $response = Http::withHeaders($this->headers())->patch($url . '?' . http_build_query($queryParams), $data);
        return !$response->failed();
    }

    public function uploadFile(string $bucket, string $path, $fileContent, string $contentType): ?string
    {
        $response = Http::withHeaders([
            'apikey' => $this->anonKey,
            'Authorization' => 'Bearer ' . $this->serviceKey,
            'Content-Type' => $contentType,
        ])->post("{$this->url}/storage/v1/object/{$bucket}/{$path}", $fileContent);
        if ($response->failed()) return null;
        return "{$this->url}/storage/v1/object/public/{$bucket}/{$path}";
    }
}
