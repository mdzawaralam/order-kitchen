<?php
namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\LockMode;

class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Order::class);
    }

    /** @return Order[] */
    public function findActiveOrders(): array {
        return $this->createQueryBuilder('o')
            ->where('o.status = :status')
            ->setParameter('status', Order::STATUS_ACTIVE)
            ->orderBy('o.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countActiveOrders(): int
{
    $qb = $this->createQueryBuilder('o')
        ->select('COUNT(o.id)')
        ->where('o.status = :status')
        ->andWhere('o.vip = :vip') // Exclude VIP orders
        ->setParameter('status', Order::STATUS_ACTIVE)
        ->setParameter('vip', false);

    return (int) $qb->getQuery()->getSingleScalarResult();
}


 public function findEarliestActivePickupTime(): ?\DateTimeImmutable
{
    $qb = $this->createQueryBuilder('o')
        ->select('o.pickupTime')
        ->where('o.status = :status')
        ->andWhere('o.vip = :vip') // Exclude VIPs if you wish
        ->setParameter('status', Order::STATUS_ACTIVE)
        ->setParameter('vip', false)
        ->orderBy('o.pickupTime', 'ASC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();

    return $qb['pickupTime'] ?? null;
}

public function findOrdersToAutoComplete(\DateTimeImmutable $cutoffTime): array
{
    return $this->createQueryBuilder('o')
        ->where('o.status = :status')
        ->andWhere('o.pickupTime <= :cutoff')
        ->setParameter('status', 'active')
        ->setParameter('cutoff', $cutoffTime)
        ->getQuery()
        ->getResult();
}


}
