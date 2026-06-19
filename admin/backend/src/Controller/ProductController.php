<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    #[Route('/api/products', name: 'api_products_list', methods: ['GET'])]
    public function list(ProductRepository $repository): JsonResponse
    {
        $products = array_map(
            static fn (Product $product): array => [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'type' => $product->getType(),
                'description' => $product->getDescription(),
                'quantity' => $product->getQuantity(),
                'price' => $product->getPrice(),
            ],
            $repository->findAll(),
        );

        return $this->json($products);
    }
}
