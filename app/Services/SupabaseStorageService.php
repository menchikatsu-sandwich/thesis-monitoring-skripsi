<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SupabaseStorageService
{
    protected $url;
    protected $key;
    protected $bucket;

    public function __construct()
    {
        $this->url = config('services.supabase.url');
        $this->key = config('services.supabase.key');
        $this->bucket = 'skripsi-drafts'; // Pastikan nama bucket di Supabase Anda persis ini
    }

    public function uploadFile($file, $folder = 'drafts')
    {
        $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = "{$folder}/{$fileName}";

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->key}",
            'apikey' => $this->key,
            'Content-Type' => $file->getMimeType(),
        ])->attach('file', file_get_contents($file->getRealPath()), $fileName)
        ->post("{$this->url}/storage/v1/object/{$this->bucket}/{$path}");

        if ($response->successful()) {
            // Return Public URL
            return "{$this->url}/storage/v1/object/public/{$this->bucket}/{$path}";
        }

        throw new \Exception("Gagal upload ke Supabase: " . $response->body());
    }

    public function deleteFile($publicUrl)
    {
        // Extract path from URL to delete
        $basePath = "/storage/v1/object/public/{$this->bucket}/";
        if (str_contains($publicUrl, $basePath)) {
            $path = str_replace($basePath, '', $publicUrl);
            
            Http::withHeaders([
                'Authorization' => "Bearer {$this->key}",
                'apikey' => $this->key,
            ])->delete("{$this->url}/storage/v1/object/{$this->bucket}/{$path}");
        }
    }
}