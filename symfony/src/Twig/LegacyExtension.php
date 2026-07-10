<?php

namespace App\Twig;

use App\Service\HomepageDataProviderInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class LegacyExtension extends AbstractExtension
{
    public function __construct(
        private readonly HomepageDataProviderInterface $legacyData,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('l', [$this, 'translate']),
            new TwigFunction('base_url', [$this, 'baseUrl']),
            new TwigFunction('legacy_year', static fn (): string => date('Y')),
            new TwigFunction('legacy_current_language', [$this, 'currentLanguage']),
            new TwigFunction('legacy_theme_assets_url', [$this, 'themeAssetsUrl']),
            new TwigFunction('legacy_css_files', [$this, 'cssFiles']),
            new TwigFunction('legacy_js_files', [$this, 'jsFiles']),
            new TwigFunction('legacy_products', [$this, 'products']),
            new TwigFunction('legacy_context', [$this, 'context']),
            new TwigFunction('legacy_faq_html', [$this, 'faqHtml'], ['is_safe' => ['html']]),
        ];
    }

    public function currentLanguage(): string
    {
        return $this->legacyData->currentLanguage();
    }

    public function themeAssetsUrl(): string
    {
        return $this->legacyData->themeAssetsUrl();
    }

    /**
     * @return array<int, string>
     */
    public function cssFiles(): array
    {
        return $this->legacyData->cssFiles();
    }

    /**
     * @return array<int, string>
     */
    public function jsFiles(): array
    {
        return $this->legacyData->jsFiles();
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function products(): array
    {
        return $this->legacyData->products();
    }

    public function context(string $key, mixed $default = []): mixed
    {
        return $this->legacyData->context($key, $default);
    }

    public function faqHtml(string $text): string
    {
        $html = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $links = [
            'carte connectée Beauté INÉE' => 'https://www.beauteinee.fr/',
            'carte connectée' => 'https://www.beauteinee.fr/',
            'Skinconsultations' => 'https://site.booxi.eu/beauteinee?lang=fre_fr&book=265739',
            'Skinconsultation' => 'https://site.booxi.eu/beauteinee?lang=fre_fr&book=265739',
            'Beauté INÉE' => 'https://www.beauteinee.fr/',
            'routine skincare' => 'https://beaute-inee.myshopify.com',
            'Agaskin' => 'https://agaskinbeauty.com/',
            'Monoskincare' => 'https://monoskincare.com/',
            'Torriden' => 'https://torriden.com/',
            'Cosrx' => 'https://www.cosrx.com/',
            'AXIS-Y' => 'https://www.axis-y.com/',
            'mixsoon' => 'https://mixsoon.com/',
        ];

        foreach ($links as $label => $url) {
            $escapedLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $html = str_replace(
                $escapedLabel,
                sprintf('<a class="faq-link" href="%s" target="_blank" rel="noopener noreferrer">%s</a>', htmlspecialchars($url, ENT_QUOTES), $escapedLabel),
                $html
            );
        }

        return str_replace(
            'biskinconsultation@gmail.com',
            '<a class="faq-link" href="mailto:biskinconsultation@gmail.com">biskinconsultation@gmail.com</a>',
            $html
        );
    }

    public function translate(string $key, string $default = ''): string
    {
        return $this->legacyData->translate($key, $default);
    }

    public function baseUrl(string $path = ''): string
    {
        return $this->legacyData->baseUrl($path);
    }
}
