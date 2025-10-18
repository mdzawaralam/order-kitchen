<?php

namespace App\Tests\Service;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Service\KitchenService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class KitchenServiceTest extends TestCase
{
    public function testCreateOrderWhenCapacityAvailable()
    {
        $orderRepo = $this->createMock(OrderRepository::class);
        $orderRepo->method('countActiveOrders')->willReturn(1);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('persist')->willReturn(null);
        $em->method('flush')->willReturn(null);

        $service = new KitchenService($em, $orderRepo, 5);

        $order = $service->tryCreateOrder(['Pizza'], new \DateTimeImmutable('+10 minutes'), false);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertFalse($order->isVip());
    }

    public function testCreateOrderWhenKitchenFull()
    {
        $orderRepo = $this->createMock(OrderRepository::class);
        $orderRepo->method('countActiveOrders')->willReturn(5);
        $orderRepo->method('findEarliestActivePickupTime')->willReturn(new \DateTimeImmutable());

        $em = $this->createMock(EntityManagerInterface::class);
        $service = new KitchenService($em, $orderRepo, 5);

        $result = $service->tryCreateOrder(['Pizza'], new \DateTimeImmutable('+10 minutes'), false);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('next_available_pickup_time', $result);
    }
}
