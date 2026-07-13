<?php

namespace App\Controller;

use App\Entity\ConnectedCard;
use App\Entity\Customer;
use App\Entity\CustomerOrder;
use App\Entity\Diagnostic;
use App\Entity\EmailLog;
use App\Entity\OrderItem;
use App\Entity\Payment;
use App\Entity\QuardlockAuditLog;
use App\Entity\User;
use App\Exception\QuardlockApiException;
use App\Service\AccountActivationService;
use App\Service\CardLifecycleService;
use App\Service\QuardlockServerApiClient;
use App\Service\QuardlockAuditService;
use App\Service\QuardlockClientApiRelay;
use App\Service\QuardlockEnrollmentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
final class AdminController extends AbstractController
{
    #[Route('', name: 'admin_dashboard', methods: ['GET'])]
    public function dashboard(EntityManagerInterface $entityManager): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'customerCount' => $entityManager->getRepository(Customer::class)->count([]),
            'orderCount' => $entityManager->getRepository(CustomerOrder::class)->count([]),
            'cardCount' => $entityManager->getRepository(ConnectedCard::class)->count([]),
            'diagnosticCount' => $entityManager->getRepository(Diagnostic::class)->count([]),
            'ordersAwaitingPayment' => $entityManager->getRepository(CustomerOrder::class)->count(['status' => 'pending_payment']),
            'customersAwaitingActivation' => $entityManager->getRepository(Customer::class)->count(['status' => 'pending_activation']),
            'cardsToConfigure' => (int) $entityManager->createQueryBuilder()
                ->select('COUNT(card.id)')
                ->from(ConnectedCard::class, 'card')
                ->where('card.status IN (:statuses)')
                ->setParameter('statuses', ['ordered', 'configuration_in_progress'])
                ->getQuery()
                ->getSingleScalarResult(),
            'recentOrders' => $entityManager->getRepository(CustomerOrder::class)->findBy([], ['createdAt' => 'DESC'], 10),
            'recentCards' => $entityManager->getRepository(ConnectedCard::class)->findBy([], ['createdAt' => 'DESC'], 5),
        ]);
    }

    #[Route('/clients', name: 'admin_customer_index', methods: ['GET'])]
    public function customers(Request $request, EntityManagerInterface $entityManager): Response
    {
        $query = trim((string) $request->query->get('q', ''));
        $builder = $entityManager->createQueryBuilder()
            ->select('customer')
            ->from(Customer::class, 'customer')
            ->orderBy('customer.createdAt', 'DESC')
            ->setMaxResults(50);

        if ($query !== '') {
            $builder
                ->andWhere('LOWER(customer.firstName) LIKE :query OR LOWER(customer.lastName) LIKE :query OR LOWER(customer.email) LIKE :query OR customer.phone LIKE :query')
                ->setParameter('query', '%' . mb_strtolower($query) . '%');
        }

        return $this->render('admin/customers/index.html.twig', [
            'customers' => $builder->getQuery()->getResult(),
            'query' => $query,
        ]);
    }

    #[Route('/clients/{id}', name: 'admin_customer_show', methods: ['GET'])]
    public function customer(Customer $customer, EntityManagerInterface $entityManager): Response
    {
        return $this->render('admin/customers/show.html.twig', [
            'customer' => $customer,
            'orders' => $entityManager->getRepository(CustomerOrder::class)->findBy(['customer' => $customer], ['createdAt' => 'DESC']),
            'cards' => $entityManager->getRepository(ConnectedCard::class)->findBy(['customer' => $customer], ['createdAt' => 'DESC']),
            'diagnostics' => $entityManager->getRepository(Diagnostic::class)->findBy(['customer' => $customer], ['performedAt' => 'DESC']),
            'emailLogs' => $entityManager->getRepository(EmailLog::class)->findBy(['customer' => $customer], ['createdAt' => 'DESC'], 10),
        ]);
    }

    #[Route('/clients/{id}/activation-email', name: 'admin_customer_send_activation', methods: ['POST'])]
    public function sendActivationEmail(
        Customer $customer,
        Request $request,
        AccountActivationService $accountActivation,
    ): Response {
        if (!$this->isCsrfTokenValid('send_activation_' . $customer->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton de formulaire invalide.');
        }

        $user = $customer->getUser();

        if ($user === null) {
            $this->addFlash('error', 'Aucun compte utilisateur n’est associé à ce client.');
        } elseif ($user->isActive()) {
            $this->addFlash('error', 'Ce compte est déjà activé.');
        } else {
            $activation = $accountActivation->issueAndSend($user);

            if ($activation['emailLog']->getStatus() === 'sent') {
                $this->addFlash('success', 'Le lien d’activation a été envoyé par e-mail.');
            } else {
                $this->addFlash('error', 'Le lien a été généré, mais l’e-mail n’a pas pu être envoyé. Vérifiez la configuration SMTP.');
            }
        }

        return $this->redirectToRoute('admin_customer_show', ['id' => $customer->getId()]);
    }

    #[Route('/commandes', name: 'admin_order_index', methods: ['GET'])]
    public function orders(Request $request, EntityManagerInterface $entityManager): Response
    {
        $query = trim((string) $request->query->get('q', ''));
        $builder = $entityManager->createQueryBuilder()
            ->select('customerOrder')
            ->from(CustomerOrder::class, 'customerOrder')
            ->leftJoin('customerOrder.customer', 'customer')
            ->orderBy('customerOrder.createdAt', 'DESC')
            ->setMaxResults(50);

        if ($query !== '') {
            $builder
                ->andWhere('LOWER(customerOrder.reference) LIKE :query OR LOWER(customer.firstName) LIKE :query OR LOWER(customer.lastName) LIKE :query OR LOWER(customer.email) LIKE :query')
                ->setParameter('query', '%' . mb_strtolower($query) . '%');
        }

        return $this->render('admin/orders/index.html.twig', [
            'orders' => $builder->getQuery()->getResult(),
            'query' => $query,
        ]);
    }

    #[Route('/commandes/{id}', name: 'admin_order_show', methods: ['GET'])]
    public function order(CustomerOrder $customerOrder, EntityManagerInterface $entityManager): Response
    {
        return $this->render('admin/orders/show.html.twig', [
            'order' => $customerOrder,
            'items' => $entityManager->getRepository(OrderItem::class)->findBy(['customerOrder' => $customerOrder]),
            'payments' => $entityManager->getRepository(Payment::class)->findBy(['customerOrder' => $customerOrder], ['createdAt' => 'DESC']),
            'cards' => $entityManager->getRepository(ConnectedCard::class)->findBy(['sourceOrder' => $customerOrder]),
        ]);
    }

    #[Route('/cartes', name: 'admin_card_index', methods: ['GET'])]
    public function cards(Request $request, EntityManagerInterface $entityManager): Response
    {
        $query = trim((string) $request->query->get('q', ''));
        $builder = $entityManager->createQueryBuilder()
            ->select('card')
            ->from(ConnectedCard::class, 'card')
            ->leftJoin('card.customer', 'customer')
            ->orderBy('card.createdAt', 'DESC')
            ->setMaxResults(50);

        if ($query !== '') {
            $builder
                ->andWhere('LOWER(card.externalIdentifier) LIKE :query OR LOWER(customer.firstName) LIKE :query OR LOWER(customer.lastName) LIKE :query OR LOWER(customer.email) LIKE :query')
                ->setParameter('query', '%' . mb_strtolower($query) . '%');
        }

        return $this->render('admin/cards/index.html.twig', [
            'cards' => $builder->getQuery()->getResult(),
            'query' => $query,
        ]);
    }

    #[Route('/cartes/{id}', name: 'admin_card_show', methods: ['GET'])]
    public function card(ConnectedCard $card, CardLifecycleService $cardLifecycle, EntityManagerInterface $entityManager): Response
    {
        return $this->render('admin/cards/show.html.twig', [
            'card' => $card,
            'availableTransitions' => $cardLifecycle->availableTransitions($card),
            'quardlockLogs' => $entityManager->getRepository(QuardlockAuditLog::class)->findBy(['connectedCard' => $card], ['createdAt' => 'DESC'], 10),
        ]);
    }

    #[Route('/cartes/{id}/transition/{status}', name: 'admin_card_transition', methods: ['POST'])]
    public function transitionCard(
        ConnectedCard $card,
        string $status,
        Request $request,
        CardLifecycleService $cardLifecycle,
    ): Response {
        if (!$this->isCsrfTokenValid('card_transition_' . $card->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton de formulaire invalide.');
        }

        try {
            $cardLifecycle->transition($card, $status);
            $this->addFlash('success', 'Le statut de la carte et de sa commande associée a été mis à jour.');
        } catch (\DomainException) {
            $this->addFlash('error', 'Cette action n’est plus disponible. Actualisez la fiche avant de réessayer.');
        }

        return $this->redirectToRoute('admin_card_show', ['id' => $card->getId()]);
    }

    #[Route('/cartes/{id}/confirmer-remise', name: 'admin_card_confirm_collection', methods: ['POST'])]
    public function confirmCardCollection(
        ConnectedCard $card,
        Request $request,
        CardLifecycleService $cardLifecycle,
    ): Response {
        if (!$this->isCsrfTokenValid('card_collection_' . $card->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton de formulaire invalide.');
        }

        try {
            $cardLifecycle->confirmCollection($card);
            $this->addFlash('success', 'La remise de la carte au client a été confirmée.');
        } catch (\DomainException $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute('admin_card_show', ['id' => $card->getId()]);
    }

    #[Route('/cartes/{id}/identifiant-cardlab', name: 'admin_card_assign_cardlab_identifier', methods: ['POST'])]
    public function assignCardLabIdentifier(
        ConnectedCard $card,
        Request $request,
        EntityManagerInterface $entityManager,
        QuardlockAuditService $quardlockAudit,
    ): Response {
        if (!$this->isCsrfTokenValid('cardlab_identifier_' . $card->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton de formulaire invalide.');
        }

        $identifier = trim((string) $request->request->get('cardLabIdentifier'));
        if ($identifier === '' || mb_strlen($identifier) > 120) {
            $this->addFlash('error', 'Saisissez un identifiant CardLab valide.');

            return $this->redirectToRoute('admin_card_show', ['id' => $card->getId()]);
        }

        $existingCard = $entityManager->getRepository(ConnectedCard::class)->findOneBy(['cardLabIdentifier' => $identifier]);
        if ($existingCard instanceof ConnectedCard && $existingCard->getId() !== $card->getId()) {
            $this->addFlash('error', 'Cet identifiant CardLab est déjà associé à une autre carte.');

            return $this->redirectToRoute('admin_card_show', ['id' => $card->getId()]);
        }

        $card->setCardLabIdentifier($identifier);
        $entityManager->flush();
        $quardlockAudit->logCardEvent($card, $this->currentAdmin(), 'cardlab_identifier_assigned', 'success', 'Identifiant physique CardLab associé à la carte.');
        $this->addFlash('success', 'Identifiant CardLab enregistré.');

        return $this->redirectToRoute('admin_card_show', ['id' => $card->getId()]);
    }

    #[Route('/cartes/{id}/quardlock/enrolement/demarrer', name: 'admin_card_quardlock_enrollment_start', methods: ['POST'])]
    public function startQuardlockEnrollment(
        ConnectedCard $card,
        Request $request,
        QuardlockEnrollmentService $enrollment,
        QuardlockAuditService $quardlockAudit,
    ): Response {
        if (!$this->isCsrfTokenValid('quardlock_enrollment_start_' . $card->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton de formulaire invalide.');
        }

        try {
            $nonce = $enrollment->start($card);
            $request->getSession()->set('quardlock_enrollment_' . $card->getId(), $nonce);
            $request->getSession()->remove('quardlock_client_session_' . $card->getId());
            $quardlockAudit->logCardEvent($card, $this->currentAdmin(), 'enrollment_started', 'success', 'Session d’enrôlement préparée pour 15 minutes.');
        } catch (\DomainException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute('admin_card_show', ['id' => $card->getId()]);
        }

        return $this->redirectToRoute('admin_card_quardlock_enrollment', ['id' => $card->getId()]);
    }

    #[Route('/cartes/{id}/quardlock/enrolement', name: 'admin_card_quardlock_enrollment', methods: ['GET'])]
    public function quardlockEnrollment(
        ConnectedCard $card,
        Request $request,
        QuardlockEnrollmentService $enrollment,
    ): Response {
        $nonce = $request->getSession()->get('quardlock_enrollment_' . $card->getId());
        $isPrepared = is_string($nonce) && $enrollment->hasValidEnrollment($card, $nonce);

        return $this->render('admin/cards/enrollment.html.twig', [
            'card' => $card,
            'isPrepared' => $isPrepared,
            // This short-lived, session-bound nonce is only used by Symfony to
            // authorize the Client API relay. The Quardlock API key never reaches
            // the browser.
            'enrollmentNonce' => $isPrepared ? $nonce : null,
        ]);
    }

    #[Route('/cartes/{id}/quardlock/InitializeApiClientSession', name: 'admin_card_quardlock_initialize_client_session', methods: ['GET'])]
    public function initializeQuardlockClientSession(
        ConnectedCard $card,
        Request $request,
        QuardlockEnrollmentService $enrollment,
        QuardlockAuditService $quardlockAudit,
    ): Response {
        $nonce = (string) $request->headers->get('X-Quardlock-Enrollment', '');
        if (!$this->matchesEnrollmentSession($request, $card, $nonce) || !$enrollment->hasValidEnrollment($card, $nonce)) {
            return new Response('Session d’enrôlement invalide ou expirée.', Response::HTTP_FORBIDDEN);
        }

        try {
            $sessionToken = $enrollment->issueClientSession($card, $nonce);
            $browserHandle = bin2hex(random_bytes(24));
            $request->getSession()->set('quardlock_client_session_' . $card->getId(), [
                'handle' => $browserHandle,
                'token' => $sessionToken,
                'webAuthnSessionId' => null,
            ]);
            $quardlockAudit->logCardEvent($card, $this->currentAdmin(), 'client_session_issued', 'success', 'Session Client API temporaire émise.', 'InitializeApiClientSession', 200);

            // The browser only receives an opaque, session-bound handle. The real
            // Quardlock Client API token is never exposed outside Symfony.
            return new Response($browserHandle, Response::HTTP_OK, ['Content-Type' => 'text/plain; charset=utf-8', 'Cache-Control' => 'no-store']);
        } catch (QuardlockApiException|\DomainException $exception) {
            $quardlockAudit->logCardEvent(
                $card,
                $this->currentAdmin(),
                'client_session_issued',
                'failed',
                $exception->getMessage(),
                $exception instanceof QuardlockApiException ? $exception->getEndpoint() : 'InitializeApiClientSession',
                $exception instanceof QuardlockApiException ? $exception->getHttpStatus() : null,
            );

            return new Response('Impossible de créer la session Quardlock.', Response::HTTP_BAD_GATEWAY);
        }
    }

    #[Route('/cartes/{id}/quardlock/client-api/{operation}', name: 'admin_card_quardlock_client_api_proxy', methods: ['GET', 'POST'])]
    public function proxyQuardlockClientApi(
        ConnectedCard $card,
        string $operation,
        Request $request,
        QuardlockEnrollmentService $enrollment,
        QuardlockClientApiRelay $relay,
        QuardlockAuditService $quardlockAudit,
    ): Response {
        $nonce = $request->getSession()->get('quardlock_enrollment_' . $card->getId());
        $session = $request->getSession()->get('quardlock_client_session_' . $card->getId());
        $handle = (string) $request->headers->get('ClientApiToken', '');

        if (!is_string($nonce) || !$enrollment->hasValidEnrollment($card, $nonce)
            || !is_array($session) || !isset($session['handle'], $session['token'])
            || !is_string($session['handle']) || !is_string($session['token'])
            || $handle === '' || !hash_equals($session['handle'], $handle)) {
            return new Response('Session d’enrôlement invalide ou expirée.', Response::HTTP_FORBIDDEN, ['Cache-Control' => 'no-store']);
        }

        try {
            $webAuthnSessionId = is_string($session['webAuthnSessionId'] ?? null) ? $session['webAuthnSessionId'] : null;
            $precomputedTokenSerialNumber = is_string($session['precomputedTokenSerialNumber'] ?? null)
                ? $session['precomputedTokenSerialNumber']
                : null;

            if ($operation === 'RegisterToken' && ($precomputedTokenSerialNumber === null || $precomputedTokenSerialNumber === '')) {
                return new Response(
                    'La préparation de la carte a expiré. Redémarrez l’enrôlement pour créer une nouvelle session.',
                    Response::HTTP_CONFLICT,
                    ['Cache-Control' => 'no-store'],
                );
            }

            $result = $relay->forward(
                $operation,
                $session['token'],
                $request,
                $webAuthnSessionId,
                $precomputedTokenSerialNumber,
            );

            if ($operation === 'GetPrecomputedTokenSerialNumber' && $result['status'] >= 200 && $result['status'] < 300) {
                $precomputedTokenSerialNumber = trim($result['content']);
                if ($precomputedTokenSerialNumber !== '') {
                    $session['precomputedTokenSerialNumber'] = $precomputedTokenSerialNumber;
                }
            }

            if ($operation === 'GetChallenge' && is_string($result['webAuthnSessionId']) && $result['webAuthnSessionId'] !== '') {
                $session['webAuthnSessionId'] = $result['webAuthnSessionId'];
            }

            $request->getSession()->set('quardlock_client_session_' . $card->getId(), $session);

            if ($operation === 'RegisterToken') {
                $quardlockAudit->logCardEvent(
                    $card,
                    $this->currentAdmin(),
                    'identity_registration_forwarded',
                    $result['status'] >= 200 && $result['status'] < 300 ? 'success' : 'failed',
                    sprintf(
                        'Enregistrement d’identité relayé vers Quardlock (jeton de préparation de session : %s).',
                        $precomputedTokenSerialNumber !== null && $precomputedTokenSerialNumber !== '' ? 'présent' : 'absent',
                    ),
                    'RegisterToken',
                    $result['status'],
                );
            }

            $headers = [
                'Content-Type' => $result['contentType'],
                'Cache-Control' => 'no-store',
            ];
            if ($operation === 'GetChallenge' && $result['webAuthnSessionId'] !== null) {
                $headers['WebAuthnSessionId'] = $result['webAuthnSessionId'];
            }

            return new Response($result['content'], $result['status'], $headers);
        } catch (\InvalidArgumentException) {
            return new Response('Opération Quardlock non autorisée.', Response::HTTP_NOT_FOUND, ['Cache-Control' => 'no-store']);
        } catch (QuardlockApiException $exception) {
            if ($operation === 'RegisterToken') {
                $quardlockAudit->logCardEvent(
                    $card,
                    $this->currentAdmin(),
                    'identity_registration_forwarded',
                    'failed',
                    'Le relais Symfony n’a pas pu joindre Quardlock pour enregistrer l’identité.',
                    $exception->getEndpoint(),
                    $exception->getHttpStatus(),
                );
            }

            return new Response($exception->getMessage(), Response::HTTP_BAD_GATEWAY, ['Cache-Control' => 'no-store']);
        }
    }

    #[Route('/cartes/{id}/quardlock/enrollment-complete', name: 'admin_card_quardlock_enrollment_complete', methods: ['POST'])]
    public function completeQuardlockEnrollment(
        ConnectedCard $card,
        Request $request,
        QuardlockEnrollmentService $enrollment,
        QuardlockAuditService $quardlockAudit,
    ): JsonResponse {
        $nonce = (string) $request->headers->get('X-Quardlock-Enrollment', '');
        $csrf = (string) $request->headers->get('X-CSRF-Token', '');
        if (!$this->isCsrfTokenValid('quardlock_enrollment_complete_' . $card->getId(), $csrf) || !$this->matchesEnrollmentSession($request, $card, $nonce)) {
            return $this->json(['success' => false, 'message' => 'Session d’enrôlement invalide.'], Response::HTTP_FORBIDDEN);
        }

        try {
            $payload = json_decode((string) $request->getContent(), true, flags: JSON_THROW_ON_ERROR);
            $serialNumber = is_array($payload) ? (string) ($payload['serialNumber'] ?? '') : '';
            $enrollment->complete($card, $nonce, $serialNumber);
            $request->getSession()->remove('quardlock_enrollment_' . $card->getId());
            $request->getSession()->remove('quardlock_client_session_' . $card->getId());
            $quardlockAudit->logCardEvent($card, $this->currentAdmin(), 'enrollment_completed', 'success', 'Enrôlement Quardlock vérifié et carte initialisée.', 'IsTokenLocked', 200);

            return $this->json([
                'success' => true,
                'redirectUrl' => $this->generateUrl('admin_card_show', ['id' => $card->getId()]),
            ]);
        } catch (\JsonException|QuardlockApiException|\DomainException $exception) {
            $quardlockAudit->logCardEvent(
                $card,
                $this->currentAdmin(),
                'enrollment_completed',
                'failed',
                $exception->getMessage(),
                $exception instanceof QuardlockApiException ? $exception->getEndpoint() : 'RegisterToken',
                $exception instanceof QuardlockApiException ? $exception->getHttpStatus() : null,
            );

            return $this->json(['success' => false, 'message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    #[Route('/quardlock', name: 'admin_quardlock', methods: ['GET'])]
    public function quardlock(QuardlockServerApiClient $quardlock, EntityManagerInterface $entityManager): Response
    {
        return $this->render('admin/quardlock/index.html.twig', [
            'isConfigured' => $quardlock->isConfigured(),
            'logs' => $entityManager->getRepository(QuardlockAuditLog::class)->findBy([], ['createdAt' => 'DESC'], 12),
        ]);
    }

    #[Route('/quardlock/test-connection', name: 'admin_quardlock_test_connection', methods: ['POST'])]
    public function testQuardlockConnection(
        Request $request,
        QuardlockServerApiClient $quardlock,
        QuardlockAuditService $quardlockAudit,
    ): Response
    {
        if (!$this->isCsrfTokenValid('quardlock_connection_test', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton de formulaire invalide.');
        }

        try {
            $quardlock->verifyConnection();
            $quardlockAudit->logConnectionCheck($this->getUser() instanceof User ? $this->getUser() : null);
            $this->addFlash('success', 'Connexion Quardlock vérifiée. La session de test a été révoquée immédiatement.');
        } catch (QuardlockApiException $exception) {
            $quardlockAudit->logConnectionCheck($this->getUser() instanceof User ? $this->getUser() : null, $exception);
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute('admin_quardlock');
    }

    #[Route('/diagnostics', name: 'admin_diagnostic_index', methods: ['GET'])]
    public function diagnostics(Request $request, EntityManagerInterface $entityManager): Response
    {
        $query = trim((string) $request->query->get('q', ''));
        $builder = $entityManager->createQueryBuilder()
            ->select('diagnostic')
            ->from(Diagnostic::class, 'diagnostic')
            ->leftJoin('diagnostic.customer', 'customer')
            ->orderBy('diagnostic.performedAt', 'DESC')
            ->setMaxResults(50);

        if ($query !== '') {
            $builder
                ->andWhere('LOWER(diagnostic.externalReference) LIKE :query OR LOWER(customer.firstName) LIKE :query OR LOWER(customer.lastName) LIKE :query OR LOWER(customer.email) LIKE :query')
                ->setParameter('query', '%' . mb_strtolower($query) . '%');
        }

        return $this->render('admin/diagnostics/index.html.twig', [
            'diagnostics' => $builder->getQuery()->getResult(),
            'query' => $query,
        ]);
    }

    #[Route('/diagnostics/{id}', name: 'admin_diagnostic_show', methods: ['GET'])]
    public function diagnostic(Diagnostic $diagnostic): Response
    {
        return $this->render('admin/diagnostics/show.html.twig', [
            'diagnostic' => $diagnostic,
        ]);
    }

    #[Route('/emails', name: 'admin_email_index', methods: ['GET'])]
    public function emails(Request $request, EntityManagerInterface $entityManager): Response
    {
        $query = trim((string) $request->query->get('q', ''));
        $builder = $entityManager->createQueryBuilder()
            ->select('emailLog')
            ->from(EmailLog::class, 'emailLog')
            ->leftJoin('emailLog.customer', 'customer')
            ->orderBy('emailLog.createdAt', 'DESC')
            ->setMaxResults(80);

        if ($query !== '') {
            $builder
                ->andWhere('LOWER(emailLog.recipient) LIKE :query OR LOWER(emailLog.subject) LIKE :query OR LOWER(emailLog.status) LIKE :query OR LOWER(customer.firstName) LIKE :query OR LOWER(customer.lastName) LIKE :query')
                ->setParameter('query', '%' . mb_strtolower($query) . '%');
        }

        return $this->render('admin/emails/index.html.twig', [
            'emailLogs' => $builder->getQuery()->getResult(),
            'query' => $query,
        ]);
    }

    private function currentAdmin(): ?User
    {
        $user = $this->getUser();

        return $user instanceof User ? $user : null;
    }

    private function matchesEnrollmentSession(Request $request, ConnectedCard $card, string $nonce): bool
    {
        if ($nonce === '') {
            return false;
        }

        $sessionNonce = $request->getSession()->get('quardlock_enrollment_' . $card->getId());

        return is_string($sessionNonce) && hash_equals($sessionNonce, $nonce);
    }
}
