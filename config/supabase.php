<?php

return [
    'url' => env('SUPABASE_URL', 'https://kjhaquimghhejqznleyn.supabase.co'),
    'anon_key' => env('SUPABASE_ANON_KEY'),
    'service_key' => env('SUPABASE_SERVICE_KEY'),
    'db_connection' => env('DB_CONNECTION', 'pgsql'),
    'auth_provider' => env('SUPABASE_AUTH_PROVIDER', 'eloquent'),
];
