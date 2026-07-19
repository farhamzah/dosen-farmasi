<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

class SafeUrlValidator
{
    public function validate(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        $url = trim((string) $url);

        if (str_starts_with($url, '/')) {
            if (str_contains($url, '..') || str_contains($url, '\\')) {
                throw ValidationException::withMessages(['action_url' => 'URL internal tidak aman.']);
            }

            return $url;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        if (! in_array($scheme, ['http', 'https'], true)) {
            throw ValidationException::withMessages(['action_url' => 'URL hanya boleh http, https, atau path internal.']);
        }

        return $url;
    }
}
