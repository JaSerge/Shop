<?php

namespace App\Controller;

use App\Entity\ProductType;
use App\Repository\ProductTypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ProductTypeController extends AbstractController
{
    #[Route('/api/product-types', name: 'api_product_types_list', methods: ['GET'])]
    public function list(ProductTypeRepository $repository): JsonResponse
    {
        $types = $repository->findBy([], ['name' => 'ASC']);

        return $this->json(array_map(static fn (ProductType $type): array => [
            'id' => $type->getId(),
            'name' => $type->getName(),
        ], $types));
    }
}
