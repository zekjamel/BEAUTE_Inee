<?php
defined('BASEPATH') or exit('No direct script access allowed');

/* Emit ONLY XML. Save this file as UTF-8 without BOM. No closing "?>" at end. */

if (!function_exists('xml_e')) {
    function xml_e($str) {
        return htmlspecialchars($str ?? '', ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

if (!empty($urls) && is_array($urls)) {
    foreach ($urls as $u) {
        echo "  <url>\n";
        echo '    <loc>' . xml_e($u['loc'] ?? '') . "</loc>\n";
        if (!empty($u['lastmod'])) {
            echo '    <lastmod>' . xml_e($u['lastmod']) . "</lastmod>\n";
        }
        if (!empty($u['changefreq'])) {
            echo '    <changefreq>' . xml_e($u['changefreq']) . "</changefreq>\n";
        }
        if (!empty($u['priority'])) {
            echo '    <priority>' . xml_e($u['priority']) . "</priority>\n";
        }
        echo "  </url>\n";
    }
}

echo "</urlset>";