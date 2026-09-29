<?php

namespace App\Tests\Service;

use App\Entity\ConnectedCard;
use App\Entity\Customer;
use App\Exception\QuardlockApiException;
use App\Integration\Quardlock\OfficialQuardlockServerApi;
use App\Service\QuardlockEnrollmentService;
use App\Service\QuardlockServerApiClient;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class QuardlockEnrollmentServiceTest extends TestCase
{
    #[DataProvider('verifiedStatuses')]
    public function testRetryPreservesExistingIdentity(string $status): void
    {
        $card = $this->activeCard($status);
        $enrolledAt = $card->getQuardlockEnrolledAt();
        $service = $this->service([new MockResponse('{"result":"test-session"}')]);

        $nonce = $service->start($card);
        self::assertSame($status, $card->getQuardlockEnrollmentStatus());
        self::assertTrue($service->hasValidEnrollment($card, $nonce));
        self::assertSame('test-session', $service->issueClientSession($card, $nonce));

        // A failed RegisterToken relay never calls complete().
        self::assertSame($status, $card->getQuardlockEnrollmentStatus());
        self::assertSame('old-token', $card->getQuardlockTokenSerialNumber());
        self::assertSame($enrolledAt, $card->getQuardlockEnrolledAt());
        self::assertSame('active', $card->getStatus());
    }

    public static function verifiedStatuses(): iterable
    {
        yield 'usable identity' => ['enrolled'];
        yield 'locked identity stays locked' => ['enrolled_locked'];
    }

    public function testSessionFailurePreservesExistingIdentity(): void
    {
        $card = $this->activeCard();
        $service = $this->service([new MockResponse('{}', ['http_code' => 503])]);
        $nonce = $service->start($card);

        try {
            $service->issueClientSession($card, $nonce);
            self::fail('The unavailable API must reject session creation.');
        } catch (QuardlockApiException) {
            self::assertSame('enrolled', $card->getQuardlockEnrollmentStatus());
            self::assertSame('old-token', $card->getQuardlockTokenSerialNumber());
        }
    }

    public function testInitialEnrollmentStillRequiresVerification(): void
    {
        $card = (new ConnectedCard())
            ->setStatus('collected')
            ->setCustomer(new Customer())
            ->setCardLabIdentifier('physical-test-card');
        $service = $this->service([new MockResponse('{"result":"test-session"}')]);
        $nonce = $service->start($card);
        self::assertSame('pending', $card->getQuardlockEnrollmentStatus());
        $service->issueClientSession($card, $nonce);
        self::assertSame('session_issued', $card->getQuardlockEnrollmentStatus());
        self::assertNull($card->getQuardlockTokenSerialNumber());
    }

    public function testUnrecognizedReplacementDoesNotReplaceExistingIdentity(): void
    {
        $card = $this->activeCard();
        $service = $this->service([new MockResponse('{"result":null}')]);
        $nonce = $service->start($card);
        try {
            $service->complete($card, $nonce, 'unknown-token');
            self::fail('An unrecognized token must not replace the old token.');
        } catch (QuardlockApiException) {
            self::assertSame('old-token', $card->getQuardlockTokenSerialNumber());
            self::assertSame('enrolled', $card->getQuardlockEnrollmentStatus());
        }
    }

    public function testVerifiedReplacementKeepsActiveLifecycle(): void
    {
        $card = $this->activeCard();
        $service = $this->service([new MockResponse('{"result":false}')]);
        $nonce = $service->start($card);
        $service->complete($card, $nonce, 'new-token');
        self::assertSame('new-token', $card->getQuardlockTokenSerialNumber());
        self::assertSame('enrolled', $card->getQuardlockEnrollmentStatus());
        self::assertSame('active', $card->getStatus());
        self::assertFalse($service->hasValidEnrollment($card, $nonce));
    }

    private function activeCard(string $status = 'enrolled'): ConnectedCard
    {
        return (new ConnectedCard())
            ->setStatus('active')
            ->setCollectedAt(new \DateTimeImmutable('-1 month'))
            ->setCustomer(new Customer())
            ->setCardLabIdentifier('physical-test-card')
            ->setQuardlockEnrollmentStatus($status)
            ->setQuardlockTokenSerialNumber('old-token')
            ->setQuardlockEnrolledAt(new \DateTimeImmutable('-1 month'));
    }

    /** @param list<MockResponse> $responses */
    private function service(array $responses): QuardlockEnrollmentService
    {
        $http = new MockHttpClient($responses);
        $api = new QuardlockServerApiClient(
            new OfficialQuardlockServerApi($http),
            'https://example.test/client',
            'test-key',
            $http,
            new NullLogger(),
        );

        return new QuardlockEnrollmentService($this->createStub(EntityManagerInterface::class), $api);
    }
}
