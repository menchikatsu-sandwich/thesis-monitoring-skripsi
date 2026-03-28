<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SupabaseStorage
{
    protected $url;
    protected $key;
    protected $bucket;

    public function __construct()
    {
        $this->url = config('services.supabase.url') ?? env('SUPABASE_URL');
        $this->key = config('services.supabase.key') ?? env('SUPABASE_KEY');
        $this->bucket = env('SUPABASE_BUCKET', 'skripsi-drafts');
    }

    public function uploadFile($file, $folder = 'proposals')
    {
        $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = "{$folder}/{$fileName}";

        $response = Http::withHeaders([
            'apikey' => $this->key,
            'Authorization' => 'Bearer ' . $this->key,
            'Content-Type' => $file->getMimeType(),
        ])->attach('file', file_get_contents($file->getRealPath()), $fileName)
        ->post("{$this->url}/storage/v1/object/{$this->bucket}/{$path}");

        if ($response->successful()) {
            // Return public URL
            return "{$this->url}/storage/v1/object/public/{$this->bucket}/{$path}";
        }

        throw new \Exception("Gagal upload ke Supabase: " . $response->body());
    }
}