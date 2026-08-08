<?php

if (!function_exists('supabase_photo_url')) {
    function supabase_photo_url(?string $filePath): ?string
    {
        if (!$filePath) return null;
        if (str_starts_with($filePath, 'http')) return $filePath;

        $base = env(
            'SUPABASE_PUBLIC_URL',
            'https://kjhaquimghhejqznleyn.supabase.co/storage/v1/object/public/gallery'
        );

        return rtrim($base, '/') . '/' . ltrim($filePath, '/');
    }
}