<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    private const SORTABLE_FIELDS = ['id', 'name', 'type', 'quantity', 'price'];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findActiveById(int $id): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.id = :id')
            ->andWhere('p.deletedAt IS NULL')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return list<Product>
     */
    public function findPaginated(
        int $page,
        int $limit,
        string $sort = 'id',
        string $order = 'asc',
        ?int $typeId = null,
        string $stock = 'all',
    ): array {
        if (!in_array($sort, self::SORTABLE_FIELDS, true)) {
            $sort = 'id';
        }

        $order = strtolower($order) === 'desc' ? 'DESC' : 'ASC';
        $sortField = 'p.'.$sort;

        return $this->createFilteredQueryBuilder($typeId, $stock)
            ->orderBy($sortField, $order)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countFiltered(?int $typeId = null, string $stock = 'all'): int
    {
        return (int) $this->createFilteredQueryBuilder($typeId, $stock)
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function createFilteredQueryBuilder(?int $typeId, string $stock): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p')
            ->innerJoin('p.type', 't')
            ->addSelect('t')
            ->andWhere('p.deletedAt IS NULL');

        if ($typeId !== null) {
            $qb
                ->andWhere('t.id = :typeId')
                ->setParameter('typeId', $typeId);
        }

        if ('in_stock' === $stock) {
            $qb->andWhere('p.quantity > 0');
        } elseif ('out_of_stock' === $stock) {
            $qb->andWhere('p.quantity = 0');
        }

        return $qb;
    }
}
