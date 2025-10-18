<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\OrderRepository;
use App\Entity\Order;
use Doctrine\DBAL\Connection;

class KitchenService
{
    private int $capacity;
    private EntityManagerInterface $em;
    private OrderRepository $orderRepository;
    private Connection $conn;
    

    public function __construct(EntityManagerInterface $em, OrderRepository $orderRepository, string $kitchenCapacity)
    {
        $this->em = $em;
        $this->orderRepository = $orderRepository;
        $this->conn = $em->getConnection();
        $this->capacity = (int)$kitchenCapacity;
    }

    /**
     * Try to create an order, respecting capacity. Returns the Order if accepted, or null if rejected.
     */
   public function tryCreateOrder(array $items, \DateTimeImmutable $pickupTime, bool $vip): Order|array|null
{
    // 🟢 VIP orders bypass capacity limit
    if ($vip) {
        $order = new Order($items, $pickupTime, true);
        $this->em->persist($order);
        $this->em->flush();
        return $order;
    }

    $conn = $this->em->getConnection();
    $conn->beginTransaction();

    try {
        // 🧱 Lock table to prevent race conditions (Postgres)
        $conn->executeStatement('LOCK TABLE "order" IN SHARE ROW EXCLUSIVE MODE');

        $activeCount = $this->orderRepository->countActiveOrders();

        if ($activeCount >= $this->capacity) {
            // 🚫 Kitchen full — suggest next available pickup time
            $earliestPickup = $this->orderRepository->findEarliestActivePickupTime();

            // If there are active orders, suggest 15 mins after earliest one finishes
            if ($earliestPickup instanceof \DateTimeImmutable) {
                $suggested = $earliestPickup->modify('+15 minutes');
            } else {
                // Otherwise, suggest 15 mins from now
                $suggested = (new \DateTimeImmutable())->modify('+15 minutes');
            }

            $conn->rollBack();

            return [
                'error' => 'Kitchen is full',
                'next_available_pickup_time' => $suggested->format(\DateTime::ATOM),
            ];
        }

        // 🟢 Create and persist new normal order
        $order = new Order($items, $pickupTime, false);
        $this->em->persist($order);
        $this->em->flush();

        $conn->commit();
        return $order;

    } catch (\Throwable $e) {
        $conn->rollBack();
        throw $e;
    }
}




    public function completeOrder(Order $order): void
    {
        $order->setStatus(Order::STATUS_COMPLETED);
        $this->em->persist($order);
        $this->em->flush();
    }
}
