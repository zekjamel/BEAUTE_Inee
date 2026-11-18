<?php

namespace App\Service;

final class CardReference
{
    public const PRODUCT_NAME = 'Carte connectée Beauté INÉE';
    public const PROVIDER = 'CardLab';

    public static function forOrder(string $orderReference, bool $test = false): string
    {
        return ($test ? 'TEST-' : '') . 'BI-CARTE-' . preg_replace('/^BI-/', '', $orderReference);
    }
}
