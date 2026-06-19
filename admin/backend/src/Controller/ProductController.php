<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductType;
use App\Repository\ProductRepository;
use App\Repository\ProductTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    #[Route('/api/products', name: 'api_products_list', methods: ['GET'])]
    public function list(Request $request, ProductRepository $repository): JsonResponse
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = min(100, max(1, $request->query->getInt('limit', 10)));
        $sort = $request->query->getString('sort', 'id');
        $order = $request->query->getString('order', 'asc');
        $stock = $request->query->getString('stock', 'all');

        if (!in_array($stock, ['all', 'in_stock', 'out_of_stock'], true)) {
            $stock = 'all';
        }

        $typeId = $request->query->has('typeId') ? $request->query->getInt('typeId') : null;
        if ($typeId !== null && $typeId <= 0) {
            $typeId = null;
        }

        $products = $repository->findPaginated($page, $limit, $sort, $order, $typeId, $stock);
        $total = $repository->countFiltered($typeId, $stock);

        return $this->json([
            'data' => array_map($this->serializeProduct(...), $products),
            'meta' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'totalPages' => (int) ceil($total / $limit),
            ],
        ]);
    }

    #[Route('/api/products', name: 'api_products_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        ProductTypeRepository $productTypeRepository,
    ): JsonResponse {
        $payload = $this->decodePayload($request);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        $validationError = $this->validatePayload($payload, $productTypeRepository);
        if ($validationError !== null) {
            return $this->json(['error' => $validationError], Response::HTTP_BAD_REQUEST);
        }

        $product = new Product();
        /** @var ProductType $type */
        $type = $this->getProductType($payload, $productTypeRepository);
        $product->setName(trim((string) $payload['name']));
        $product->setType($type);
        $product->setDescription($this->normalizeDescription($payload['description'] ?? null));
        $product->setQuantity((int) $payload['quantity']);
        $product->setPrice($this->normalizePrice($payload['price']));

        $entityManager->persist($product);
        $entityManager->flush();

        return $this->json($this->serializeProduct($product), Response::HTTP_CREATED);
    }

    #[Route('/api/products/{id}', name: 'api_products_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(
        int $id,
        Request $request,
        ProductRepository $repository,
        ProductTypeRepository $productTypeRepository,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $product = $repository->findActiveById($id);
        if ($product === null) {
            return $this->json(['error' => 'Товар не найден'], Response::HTTP_NOT_FOUND);
        }

        $payload = $this->decodePayload($request);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        $validationError = $this->validatePayload($payload, $productTypeRepository);
        if ($validationError !== null) {
            return $this->json(['error' => $validationError], Response::HTTP_BAD_REQUEST);
        }

        /** @var ProductType $type */
        $type = $this->getProductType($payload, $productTypeRepository);
        $product->setName(trim((string) $payload['name']));
        $product->setType($type);
        $product->setDescription($this->normalizeDescription($payload['description'] ?? null));
        $product->setQuantity((int) $payload['quantity']);
        $product->setPrice($this->normalizePrice($payload['price']));

        $entityManager->flush();

        return $this->json($this->serializeProduct($product));
    }

    #[Route('/api/products/{id}', name: 'api_products_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(
        int $id,
        ProductRepository $repository,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $product = $repository->findActiveById($id);
        if ($product === null) {
            return $this->json(['error' => 'Товар не найден'], Response::HTTP_NOT_FOUND);
        }

        $product->softDelete();
        $entityManager->flush();

        return $this->json(['success' => true]);
    }

    /**
     * @return array<string, mixed>|JsonResponse
     */
    private function decodePayload(Request $request): array|JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Некорректный JSON'], Response::HTTP_BAD_REQUEST);
        }

        return $payload;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validatePayload(array $payload, ProductTypeRepository $productTypeRepository): ?string
    {
        if (!isset($payload['name']) || !is_string($payload['name']) || trim($payload['name']) === '') {
            return 'Укажите название товара';
        }

        if (mb_strlen(trim($payload['name'])) > 255) {
            return 'Название не должно превышать 255 символов';
        }

        if (!isset($payload['typeId']) || !is_numeric($payload['typeId']) || (int) $payload['typeId'] <= 0) {
            return 'Укажите тип товара';
        }

        if ($this->getProductType($payload, $productTypeRepository) === null) {
            return 'Тип товара не найден';
        }

        if (!isset($payload['quantity']) || !is_numeric($payload['quantity']) || (int) $payload['quantity'] < 0) {
            return 'Количество должно быть неотрицательным числом';
        }

        if (!isset($payload['price']) || !is_numeric($payload['price']) || (float) $payload['price'] < 0) {
            return 'Цена должна быть неотрицательным числом';
        }

        return null;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function getProductType(array $payload, ProductTypeRepository $productTypeRepository): ?ProductType
    {
        return $productTypeRepository->find((int) $payload['typeId']);
    }

    private function normalizeDescription(mixed $description): ?string
    {
        if ($description === null || $description === '') {
            return null;
        }

        return is_string($description) ? $description : null;
    }

    private function normalizePrice(mixed $price): string
    {
        return number_format((float) $price, 2, '.', '');
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeProduct(Product $product): array
    {
        return [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'type' => $product->getType()->getName(),
            'typeId' => $product->getType()->getId(),
            'description' => $product->getDescription(),
            'quantity' => $product->getQuantity(),
            'price' => $product->getPrice(),
        ];
    }
}
