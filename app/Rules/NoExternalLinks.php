<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoExternalLinks implements ValidationRule
{
    /**
     * Patrones bloqueados:
     * - URLs (http/https/www)
     * - Redes sociales y apps de citas
     * - Menciones @usuario
     * - Números de teléfono con formato
     * - Competencia lifestyle conocida
     */
    private array $blocked = [
        // URLs genéricas
        '/https?:\/\//i',
        '/www\./i',
        '/\.com\b/i',
        '/\.net\b/i',
        '/\.org\b/i',
        '/\.io\b/i',
        '/\.ly\b/i',
        '/bit\.ly/i',
        '/tinyurl/i',
        // Redes sociales
        '/instagram/i',
        '/whatsapp/i',
        '/telegram/i',
        '/facebook/i',
        '/twitter/i',
        '/tiktok/i',
        '/snapchat/i',
        '/onlyfans/i',
        '/linktree/i',
        '/discord/i',
        // Apps de citas / competencia lifestyle
        '/tinder/i',
        '/grindr/i',
        '/scruff/i',
        '/feeld/i',
        '/kasidie/i',
        '/swinglifestyle/i',
        '/fabswingers/i',
        '/adultfriendfinder/i',
        '/ashley.?madison/i',
        '/mixxxer/i',
        '/3fun/i',
        // Menciones de usuario
        '/@[a-z0-9._]+/i',
        // Teléfonos (formatos comunes)
        '/\b\d{3}[\s\-\.]\d{3}[\s\-\.]\d{4}\b/',
        '/\b\(\d{2,3}\)\s?\d{3,4}[\s\-]\d{4}\b/',
        '/\b\+\d{1,3}[\s\-]?\d{7,12}\b/',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) return;

        foreach ($this->blocked as $pattern) {
            if (preg_match($pattern, $value)) {
                $fail('El contenido no puede incluir enlaces, redes sociales, teléfonos ni menciones externas.');
                return;
            }
        }
    }
}
