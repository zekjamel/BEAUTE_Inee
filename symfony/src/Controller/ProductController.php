<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\ProductCatalog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/produits')]
#[IsGranted('ROLE_ADMIN')]
final class ProductController extends AbstractController
{
    #[Route('', name: 'admin_product_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        return $this->render('admin/products/index.html.twig', [
            'products' => $em->getRepository(Product::class)->findBy([], ['id' => 'ASC']), 'types' => ProductCatalog::TYPES,
        ]);
    }

    #[Route('/nouveau', name: 'admin_product_new', methods: ['GET', 'POST'])]
    #[Route('/{id}/modifier', name: 'admin_product_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, ProductCatalog $catalog, ?Product $product = null): Response
    {
        if ($request->attributes->has('id') && $product === null) {
            throw $this->createNotFoundException('Offre introuvable.');
        }
        $product ??= new Product();
        $error = null;
        $values = [];
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('product_' . ($product->getId() ?? 'new'), (string) $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Jeton de formulaire invalide.');
            }
            $values = array_map(static fn ($value): string => is_string($value) ? $value : '', $request->request->all());
            try {
                $catalog->save($product, $request->request->all());
                $this->addFlash('success', 'Offre enregistrée. Les commandes existantes conservent leurs prix et modalités.');
                return $this->redirectToRoute('admin_product_index');
            } catch (\InvalidArgumentException $exception) {
                $error = $exception->getMessage();
            }
        } elseif ($product->getId() !== null) {
            $values = ['sku' => $product->getSku(), 'name' => $product->getName(), 'type' => $product->getType(),
                'description' => $product->getDescription(), 'price' => number_format($product->getUnitAmountCents() / 100, 2, '.', ''),
                'diagnosticCount' => $product->getDiagnosticCount(), 'isActive' => $product->isActive() ? '1' : ''];
            foreach ($product->getDeliveryOptions() as $mode => $option) {
                $values[$mode . 'Enabled'] = '1';
                $values[$mode . 'Price'] = number_format($option['amount'] / 100, 2, '.', '');
                $values[$mode . 'Terms'] = $option['terms'];
                $values[$mode . 'Countries'] = implode(', ', $option['countries'] ?? []);
            }
        }
        return $this->render('admin/products/edit.html.twig', [
            'product' => $product, 'values' => $values, 'error' => $error, 'types' => ProductCatalog::TYPES,
            'deliveryLabels' => ProductCatalog::DELIVERY_LABELS,
        ], new Response(status: $error === null ? 200 : 422));
    }
}
