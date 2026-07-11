<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

final class SymfonyHomepageDataProvider implements HomepageDataProviderInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $assets;

    /**
     * @var array<string, mixed>
     */
    private array $data;

    /**
     * @var array<string, array<string, string>>
     */
    private array $translations;

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
        $this->assets = require dirname(__DIR__, 2) . '/config/homepage/assets.php';
        $this->data = require dirname(__DIR__, 2) . '/config/homepage/data.php';
        $this->translations = [
            'fr' => require dirname(__DIR__, 2) . '/translations/messages.fr.php',
            'en' => require dirname(__DIR__, 2) . '/translations/messages.en.php',
        ];
    }

    public function translate(string $key, string $default = ''): string
    {
        $language = $this->currentLanguage();

        if (isset($this->translations[$language][$key])) {
            return $this->translations[$language][$key];
        }

        if (isset($this->translations['fr'][$key])) {
            return $this->translations['fr'][$key];
        }

        return $default !== '' ? $default : $key;
    }

    public function baseUrl(string $path = ''): string
    {
        return $this->normalizePublicPath($path);
    }

    public function currentLanguage(): string
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request === null) {
            return 'fr';
        }

        $language = $request->attributes->get('_preferred_language')
            ?: $request->query->get('lang')
            ?: $request->cookies->get('language')
            ?: $request->getPreferredLanguage(['fr', 'en']);

        return $language === 'en' ? 'en' : 'fr';
    }

    public function themeAssetsUrl(): string
    {
        return $this->normalizePublicPath($this->assets['theme_assets_url'] ?? '/');
    }

    public function cssFiles(): array
    {
        return array_map($this->normalizePublicPath(...), $this->assets['css_files'] ?? []);
    }

    public function jsFiles(): array
    {
        return array_map($this->normalizePublicPath(...), $this->assets['js_files'] ?? []);
    }

    public function products(): array
    {
        return $this->context('products', []);
    }

    public function context(string $key, mixed $default = []): mixed
    {
        return $this->data[$key] ?? $default;
    }

    private function normalizePublicPath(string $path): string
    {
        if ($path === '') {
            return '/';
        }

        if (
            str_starts_with($path, 'http://')
            || str_starts_with($path, 'https://')
            || str_starts_with($path, 'mailto:')
            || str_starts_with($path, 'tel:')
            || str_starts_with($path, '#')
        ) {
            return $path;
        }

        $path = preg_replace('#^\./+#', '', $path) ?? $path;

        return '/' . ltrim($path, '/');
    }
}
