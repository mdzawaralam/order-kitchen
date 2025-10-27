<?php

namespace App\Tests\Service;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Service\KitchenService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class KitchenServiceTest extends TestCase
{
    public function testCreateOrderWhenCapacityAvailable(): void
    {
        $orderRepo = $this->createMock(OrderRepository::class);
        $orderRepo->method('countActiveOrders')->willReturn(1);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');

        $service = new KitchenService($em, $orderRepo, 5);

        $order = $service->tryCreateOrder(['Pizza'], new \DateTimeImmutable('+10 minutes'), false);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertFalse($order->isVip());
    }

    public function testCreateOrderWhenKitchenFull(): void
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

    public function testAutoCompleteOldOrders(): void
    {
        $order = $this->createMock(Order::class);
        $order->expects($this->once())->method('setStatus')->with(Order::STATUS_COMPLETED);

        $orderRepo = $this->createMock(OrderRepository::class);
        $orderRepo->method('findOrdersToAutoComplete')->willReturn([$order]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist')->with($order);
        $em->expects($this->once())->method('flush');

        $service = new KitchenService($em, $orderRepo, 5);
        $service->autoCompleteOldOrders();
    }
}
