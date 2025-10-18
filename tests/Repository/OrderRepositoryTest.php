<?php

namespace App\Tests\Repository;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class OrderRepositoryTest extends KernelTestCase
{
    private OrderRepository $orderRepository;
    private \Doctrine\ORM\EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->em = static::getContainer()->get('doctrine')->getManager();
        $this->orderRepository = $this->em->getRepository(Order::class);
    }

    public function testCountActiveOrders()
    {
        // clear all orders
        $conn = $this->em->getConnection();
        $conn->executeStatement('DELETE FROM "order"');

        // create active order
        $order1 = new Order(['Burger'], new \DateTimeImmutable('+10 minutes'), false);
        $order1->setStatus(Order::STATUS_ACTIVE);

        // create completed order
        $order2 = new Order(['Pizza'], new \DateTimeImmutable('+20 minutes'), false);
        $order2->setStatus(Order::STATUS_COMPLETED);

        $this->em->persist($order1);
        $this->em->persist($order2);
        $this->em->flush();

        $count = $this->orderRepository->countActiveOrders();

        $this->assertSame(1, $count, 'There should be exactly one active order.');
    }

    public function testFindEarliestActivePickupTime()
    {
        // clear all orders
        $conn = $this->em->getConnection();
        $conn->executeStatement('DELETE FROM "order"');

        $o1 = new Order(['Tea'], new \DateTimeImmutable('+15 minutes'), false);
        $o1->setStatus(Order::STATUS_ACTIVE);

        $o2 = new Order(['Coffee'], new \DateTimeImmutable('+5 minutes'), false);
        $o2->setStatus(Order::STATUS_ACTIVE);

        $this->em->persist($o1);
        $this->em->persist($o2);
        $this->em->flush();

        $earliest = $this->orderRepository->findEarliestActivePickupTime();

        $this->assertInstanceOf(\DateTimeImmutable::class, $earliest);
        $this->assertSame(
            $o2->getPickupTime()->format('Y-m-d H:i'),
            $earliest->format('Y-m-d H:i'),
            'The earliest pickup time should match the earliest active order.'
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
    }
}
