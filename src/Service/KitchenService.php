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
    private int $autoCompleteDelay;

    public function __construct(EntityManagerInterface $em, OrderRepository $orderRepository, string $kitchenCapacity, int $autoCompleteDelay = 10)
    {
        $this->em = $em;
        $this->orderRepository = $orderRepository;
        $this->conn = $em->getConnection();
        $this->capacity = (int)$kitchenCapacity;
        $this->autoCompleteDelay = $autoCompleteDelay;
    }

    /**
     * Try to create an order, respecting capacity. Returns the Order if accepted, or array if rejected.
     */
    public function tryCreateOrder(array $items, \DateTimeImmutable $pickupTime, bool $vip): Order|array|null
    {
        if ($vip) {
            $order = new Order($items, $pickupTime, true);
            $this->em->persist($order);
            $this->em->flush();
            return $order;
        }

        $conn = $this->em->getConnection();
        $conn->beginTransaction();

        try {
            $conn->executeStatement('LOCK TABLE "order" IN SHARE ROW EXCLUSIVE MODE');

            $activeCount = $this->orderRepository->countActiveOrders();

            if ($activeCount >= $this->capacity) {
                $earliestPickup = $this->orderRepository->findEarliestActivePickupTime();
                $suggested = $earliestPickup instanceof \DateTimeImmutable
                    ? $earliestPickup->modify('+15 minutes')
                    : (new \DateTimeImmutable())->modify('+15 minutes');

                $conn->rollBack();

                return [
                    'error' => 'Kitchen is full',
                    'next_available_pickup_time' => $suggested->format(\DateTime::ATOM),
                ];
            }

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

    /**
     * Auto-complete orders past their pickup time + buffer
     */
    public function autoCompleteOldOrders(int $delay = null): void
    {
        $delayMinutes = $delay ?? $this->autoCompleteDelay;
        $cutoff = new \DateTimeImmutable('-'.$delayMinutes.' minutes');
        $orders = $this->orderRepository->findOrdersToAutoComplete($cutoff);

        foreach ($orders as $order) {
            $this->completeOrder($order);
        }
    }
}
