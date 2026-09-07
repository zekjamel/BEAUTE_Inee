<?php

namespace App\Tests\Security;

use PHPUnit\Framework\TestCase;

final class CardLoginPolicyTest extends TestCase
{
    public function testEnrollmentChecklistDoesNotCollectPinAndKeepsVerificationRequired(): void
    {
        $template = file_get_contents(dirname(__DIR__, 2).'/templates/admin/cards/enrollment.html.twig');

        self::assertIsString($template);
        self::assertSame(3, substr_count($template, '<input type="checkbox" data-fido-confirmation>'));
        self::assertStringNotContainsString('type="password"', $template);
        self::assertStringNotContainsString('name="pin"', strtolower($template));
        self::assertStringContainsString("userVerification: 'required'", $template);
        self::assertStringNotContainsString('webauthn.io', $template);
    }

    public function testApplicationContainsNoPinFieldOnEntities(): void
    {
        $entityFiles = glob(dirname(__DIR__, 2).'/src/Entity/*.php');

        self::assertIsArray($entityFiles);
        self::assertNotEmpty($entityFiles);

        foreach ($entityFiles as $entityFile) {
            $entity = file_get_contents($entityFile);
            self::assertIsString($entity);
            self::assertDoesNotMatchRegularExpression('/(?:private|protected|public)\\s+[^;]*\\$pin\\b/i', $entity, $entityFile);
        }
    }
}
