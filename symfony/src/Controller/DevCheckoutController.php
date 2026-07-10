<?php

namespace App\Controller;

use App\Entity\CustomerOrder;
use App\Service\CheckoutSimulationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dev/card-checkout')]
final class DevCheckoutController extends AbstractController
{
    #[Route('', name: 'dev_card_checkout', methods: ['GET'])]
    public function new(): Response
    {
        return $this->render('dev_checkout/new.html.twig');
    }

    #[Route('', name: 'dev_card_checkout_create', methods: ['POST'])]
    public function create(Request $request, CheckoutSimulationService $checkout): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('dev_card_checkout', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton de formulaire invalide.');
        }

        $order = $checkout->createCardOrder($request->request->all());

        return $this->redirectToRoute('dev_order_show', ['reference' => $order->getReference()]);
    }

    #[Route('/orders/{reference}', name: 'dev_order_show', methods: ['GET'])]
    public function show(#[MapEntity(mapping: ['reference' => 'reference'])] CustomerOrder $order): Response
    {
        return $this->render('dev_checkout/show.html.twig', [
            'order' => $order,
            'activationToken' => null,
        ]);
    }

    #[Route('/orders/{reference}/simulate-paid', name: 'dev_order_simulate_paid', methods: ['POST'])]
    public function simulatePaid(
        #[MapEntity(mapping: ['reference' => 'reference'])] CustomerOrder $order,
        Request $request,
        CheckoutSimulationService $checkout,
        EntityManagerInterface $entityManager,
    ): Response {
        if (!$this->isCsrfTokenValid('dev_order_simulate_paid_' . $order->getReference(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton de formulaire invalide.');
        }

        $activationToken = $checkout->markOrderPaid($order);
        $entityManager->refresh($order);

        return $this->render('dev_checkout/show.html.twig', [
            'order' => $order,
            'activationToken' => $activationToken,
        ]);
    }
}
