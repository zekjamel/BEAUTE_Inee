<?php

namespace App\Service;

final readonly class FeatureFlags
{
    public function __construct(
        private bool $customerLoginEnabled,
        private bool $cardSalesEnabled,
    ) {
    }

    public function isCustomerLoginEnabled(): bool
    {
        return $this->customerLoginEnabled;
    }

    public function isCardSalesEnabled(): bool
    {
        return $this->cardSalesEnabled;
    }
}
