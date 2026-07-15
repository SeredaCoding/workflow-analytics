<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class LinkPreviewService
{
    public function fetch(string $url): array
    {
        try {
            $response = Http::timeout(5)
                ->withUserAgent('Mozilla/5.0 (compatible; WorkflowAnalytics/1.0)')
                ->get($url);

            if (!$response->successful()) {
                return $this->fallback($url);
            }

            $html = $response->body();

            $title = $this->extractMeta($html, 'og:title')
                  ?? $this->extractMeta($html, 'twitter:title')
                  ?? $this->extractTitle($html)
                  ?? $url;

            $description = $this->extractMeta($html, 'og:description')
                         ?? $this->extractMeta($html, 'twitter:description')
                         ?? $this->extractMeta($html, 'description')
                         ?? null;

            $image = $this->extractMeta($html, 'og:image')
                  ?? $this->extractMeta($html, 'og:image:secure_url')
                  ?? $this->extractMeta($html, 'twitter:image')
                  ?? null;

            if ($image && !str_starts_with($image, 'http')) {
                $image = $this->makeUrlAbsolute($image, $url);
            }

            if (!$image) {
                $image = $this->extractFavicon($html, $url);
            }

            return [
                'title' => $title,
                'description' => $description,
                'image_url' => $image,
            ];
        } catch (\Exception $e) {
            return $this->fallback($url);
        }
    }

    private function extractMeta(string $html, string $property): ?string
    {
        $patterns = [
            '/<meta\s+[^>]*property=["\']' . preg_quote($property, '/') . '["\'][^>]*content=["\']([^"\']*)["\'][^>]*\/?>/is',
            '/<meta\s+[^>]*content=["\']([^"\']*)["\'][^>]*property=["\']' . preg_quote($property, '/') . '["\'][^>]*\/?>/is',
            '/<meta\s+[^>]*name=["\']' . preg_quote($property, '/') . '["\'][^>]*content=["\']([^"\']*)["\'][^>]*\/?>/is',
            '/<meta\s+[^>]*content=["\']([^"\']*)["\'][^>]*name=["\']' . preg_quote($property, '/') . '["\'][^>]*\/?>/is',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                return html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
            }
        }

        return null;
    }

    private function extractTitle(string $html): ?string
    {
        if (preg_match('/<title>([^<]*)<\/title>/is', $html, $matches)) {
            return html_entity_decode(trim($matches[1]), ENT_QUOTES, 'UTF-8');
        }
        return null;
    }

    private function extractFavicon(string $html, string $url): ?string
    {
        $patterns = [
            '/<link\s+[^>]*rel=["\']icon["\'][^>]*href=["\']([^"\']*)["\'][^>]*\/?>/is',
            '/<link\s+[^>]*rel=["\']shortcut icon["\'][^>]*href=["\']([^"\']*)["\'][^>]*\/?>/is',
            '/<link\s+[^>]*href=["\']([^"\']*)["\'][^>]*rel=["\']icon["\'][^>]*\/?>/is',
            '/<link\s+[^>]*href=["\']([^"\']*)["\'][^>]*rel=["\']shortcut icon["\'][^>]*\/?>/is',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                $favicon = $matches[1];
                if (!str_starts_with($favicon, 'http')) {
                    $favicon = $this->makeUrlAbsolute($favicon, $url);
                }
                return $favicon;
            }
        }

        $parsed = parse_url($url);
        return $parsed['scheme'] . '://' . $parsed['host'] . '/favicon.ico';
    }

    private function makeUrlAbsolute(string $path, string $baseUrl): string
    {
        $parsed = parse_url($baseUrl);
        $base = $parsed['scheme'] . '://' . $parsed['host'];
        if (isset($parsed['port'])) {
            $base .= ':' . $parsed['port'];
        }
        return $base . ($path[0] === '/' ? '' : '/') . $path;
    }

    private function fallback(string $url): array
    {
        return [
            'title' => $url,
            'description' => null,
            'image_url' => null,
        ];
    }
}
