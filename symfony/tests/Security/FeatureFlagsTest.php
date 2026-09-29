<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\ActiveUserChecker;
use App\Service\FeatureFlags;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

final class FeatureFlagsTest extends TestCase
{
    public function testDisabledCustomerLoginRejectsCustomerAuthentication(): void
    {
        $user = (new User())
            ->setRoles(['ROLE_USER'])
            ->setIsActive(true);

        $checker = new ActiveUserChecker(new FeatureFlags(false, false));

        $this->expectException(CustomUserMessageAccountStatusException::class);
        $this->expectExceptionMessage('L’espace client sera bientôt disponible.');

        $checker->checkPreAuth($user);
    }

    public function testDisabledCustomerLoginKeepsAdministratorAuthenticationAvailable(): void
    {
        $user = (new User())
            ->setRoles(['ROLE_ADMIN'])
            ->setIsActive(true);

        $checker = new ActiveUserChecker(new FeatureFlags(false, false));

        $checker->checkPreAuth($user);

        self::assertTrue(true);
    }

    public function testOperatorCanAuthenticateWhenCustomerLoginIsDisabled(): void
    {
        $checker = new ActiveUserChecker(new FeatureFlags(false, false));
        $checker->checkPreAuth((new User())->setRoles(['ROLE_OPERATOR'])->setIsActive(true));
        self::assertTrue(true);
    }

    public function testInactiveOperatorCannotAuthenticate(): void
    {
        $checker = new ActiveUserChecker(new FeatureFlags(false, false));
        $this->expectException(CustomUserMessageAccountStatusException::class);
        $checker->checkPreAuth((new User())->setRoles(['ROLE_OPERATOR']));
    }

    public function testFeatureFlagsCanBeEnabledIndependently(): void
    {
        $featureFlags = new FeatureFlags(true, false);

        self::assertTrue($featureFlags->isCustomerLoginEnabled());
        self::assertFalse($featureFlags->isCardSalesEnabled());
    }
}
