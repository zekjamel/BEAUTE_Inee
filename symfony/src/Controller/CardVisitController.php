<?php

namespace App\Controller;

use App\Entity\CustomerOrder;
use App\Entity\Diagnostic;
use App\Entity\OrderItem;
use App\Exception\QuardlockApiException;
use App\Service\CardVisitService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/visite', name: 'admin_visit_')]
#[IsGranted('ROLE_OPERATOR')]
final class CardVisitController extends AbstractController
{
    public function __construct(
        private readonly CardVisitService $visits,
        #[Autowire(env: 'bool:CARD_LOGIN_MOBILE_NFC_ENABLED')]
        private readonly bool $cardLoginMobileNfcEnabled,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->render('admin/visit/identify.html.twig', [], $this->privateResponse());
    }

    #[Route('/session', name: 'initialize', methods: ['POST'])]
    public function initialize(Request $request): JsonResponse
    {
        $this->csrf($request);
        if (!$this->cardLoginMobileNfcEnabled && ($request->headers->get('Sec-CH-UA-Mobile') === '?1'
            || preg_match('/Android|iPhone|iPad|iPod/i', (string) $request->headers->get('User-Agent')) === 1)) {
            return new JsonResponse(['message' => 'Utilisez le poste boutique : le NFC mobile est en cours de validation.'], 503, ['Cache-Control' => 'no-store']);
        }
        try {
            return new JsonResponse(['handle' => $this->visits->start($request, $this->getUser()->getUserIdentifier())], headers: ['Cache-Control' => 'no-store']);
        } catch (QuardlockApiException) {
            return $this->unavailable();
        }
    }

    #[Route('/defi', name: 'challenge', methods: ['GET'])]
    public function challenge(Request $request): Response
    {
        $this->csrf($request);
        try {
            return new Response($this->visits->challenge($request, $this->getUser()->getUserIdentifier()), headers: [
                'Content-Type' => 'application/octet-stream', 'Cache-Control' => 'no-store',
            ]);
        } catch (QuardlockApiException) {
            return $this->unavailable();
        }
    }

    #[Route('/verification', name: 'verify', methods: ['POST'])]
    public function verify(Request $request): JsonResponse
    {
        $this->csrf($request);
        try {
            $this->visits->verify($request, $this->getUser()->getUserIdentifier());
            return new JsonResponse(['redirectUrl' => $this->generateUrl('admin_visit_customer')], headers: ['Cache-Control' => 'no-store']);
        } catch (QuardlockApiException) {
            return $this->unavailable();
        } catch (HttpExceptionInterface $exception) {
            return new JsonResponse(['message' => $exception->getMessage()], $exception->getStatusCode(), ['Cache-Control' => 'no-store']);
        }
    }

    #[Route('/dossier', name: 'customer', methods: ['GET'])]
    public function customer(Request $request, EntityManagerInterface $em): Response
    {
        $customer = $this->visits->customer($request, $this->getUser()->getUserIdentifier());
        if ($customer === null) {
            $response = $this->redirectToRoute('admin_visit_index');
            $response->headers->set('Cache-Control', 'no-store');
            return $response;
        }
        $orders = $em->getRepository(CustomerOrder::class)->findBy(['customer' => $customer], ['createdAt' => 'DESC']);
        $items = [];
        foreach ($orders as $order) {
            $items[$order->getId()] = $em->getRepository(OrderItem::class)->findBy(['customerOrder' => $order]);
        }
        return $this->render('admin/visit/customer.html.twig', [
            'customer' => $customer, 'orders' => $orders, 'items' => $items,
            'remainingSeconds' => $this->visits->remainingSeconds($request),
            'diagnostics' => $em->getRepository(Diagnostic::class)->findBy(['customer' => $customer], ['performedAt' => 'DESC']),
        ], $this->privateResponse());
    }

    #[Route('/terminer', name: 'finish', methods: ['POST'])]
    public function finish(Request $request): Response
    {
        $this->csrf($request);
        $this->visits->finish($request);
        $response = $this->redirectToRoute('admin_visit_index', status: Response::HTTP_SEE_OTHER);
        $response->headers->set('Cache-Control', 'no-store');
        return $response;
    }

    private function csrf(Request $request): void
    {
        if (!$this->isCsrfTokenValid('card_visit', (string) ($request->headers->get('X-CSRF-Token') ?? $request->request->get('_token')))) {
            throw $this->createAccessDeniedException('Rechargez la page puis recommencez.');
        }
    }

    private function privateResponse(): Response
    {
        return new Response(headers: ['Cache-Control' => 'private, no-store, max-age=0']);
    }

    private function unavailable(): JsonResponse
    {
        return new JsonResponse(['message' => 'Le service d’identification par carte est temporairement indisponible.'], 502, ['Cache-Control' => 'no-store']);
    }
}
