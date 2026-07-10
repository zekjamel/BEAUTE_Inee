<?php

namespace App\Service;

interface HomepageDataProviderInterface
{
    public function translate(string $key, string $default = ''): string;

    public function baseUrl(string $path = ''): string;

    public function currentLanguage(): string;

    public function themeAssetsUrl(): string;

    /**
     * @return array<int, string>
     */
    public function cssFiles(): array;

    /**
     * @return array<int, string>
     */
    public function jsFiles(): array;

    /**
     * @return array<int, array<string, string>>
     */
    public function products(): array;

    public function context(string $key, mixed $default = []): mixed;
}
