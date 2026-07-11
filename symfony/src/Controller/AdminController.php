<?php

namespace App\Controller;

use App\Entity\ConnectedCard;
use App\Entity\Customer;
use App\Entity\CustomerOrder;
use App\Entity\Diagnostic;
use App\Entity\OrderItem;
use App\Entity\Payment;
use App\Service\CardLifecycleService;
use Doctrine\ORM\EntityManagerInterface;
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
            'cardsToConfigure' => $entityManager->getRepository(ConnectedCard::class)->count(['status' => 'ordered']),
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
        ]);
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
    public function card(ConnectedCard $card, CardLifecycleService $cardLifecycle): Response
    {
        return $this->render('admin/cards/show.html.twig', [
            'card' => $card,
            'availableTransitions' => $cardLifecycle->availableTransitions($card),
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
}
