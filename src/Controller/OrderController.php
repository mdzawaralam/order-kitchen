<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\KitchenService;
use App\Repository\OrderRepository;
use App\Entity\Order;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;

#[Route('/orders')]
class OrderController extends AbstractController
{
    private KitchenService $kitchen;
    private OrderRepository $orderRepo;

    public function __construct(KitchenService $kitchen, OrderRepository $orderRepo)
    {
        $this->kitchen = $kitchen;
        $this->orderRepo = $orderRepo;
    }

    #[Route('', name: 'create_order', methods: ['POST'])]
    public function create(Request $request): JsonResponse
{
    $payload = json_decode($request->getContent(), true);
    $items = $payload['items'] ?? [];
    $pickup = $payload['pickup_time'] ?? null;
    $vip = isset($payload['VIP']) ? (bool)$payload['VIP'] : false;

    if (!is_array($items) || !$pickup) {
        return $this->json(['error' => 'invalid payload'], 400);
    }

    try {
        $pickupTime = new \DateTimeImmutable($pickup);
    } catch (\Exception $e) {
        return $this->json(['error' => 'invalid pickup_time format'], 400);
    }

    $orderResult = $this->kitchen->tryCreateOrder($items, $pickupTime, $vip);

    // Kitchen full → show suggestion
    if (is_array($orderResult) && isset($orderResult['error'])) {
        return $this->json($orderResult, 429);
    }

    // Unexpected issue
    if ($orderResult === null) {
        return $this->json(['error' => 'Unknown issue creating order'], 500);
    }

    // Order successfully created
    $order = $orderResult;
    return $this->json([
        'id' => $order->getId(),
        'items' => $order->getItems(),
        'pickup_time' => $order->getPickupTime()->format(\DateTime::ATOM),
        'VIP' => $order->isVip(),
        'status' => $order->getStatus(),
    ], 201);
}


    #[Route('/active', name: 'list_active', methods: ['GET'])]
    public function listActive(): JsonResponse
    {
        $orders = $this->orderRepo->findActiveOrders();
        $data = array_map(function(Order $o){
            return [
                'id' => $o->getId(),
                'items' => $o->getItems(),
                'pickup_time' => $o->getPickupTime()->format(\DateTime::ATOM),
                'VIP' => $o->isVip(),
                'created_at' => $o->getCreatedAt()->format(\DateTime::ATOM),
            ];
        }, $orders);

        return $this->json($data, 200);
    }

    #[Route('/{id}/complete', name: 'complete_order', methods: ['POST'])]
    public function complete(int $id, ManagerRegistry $doctrine): JsonResponse
    {
        $order = $doctrine->getRepository(Order::class)->find($id);
        if (!$order) {
            return $this->json(['error' => 'Order not found'], 404);
        }

        $this->kitchen->completeOrder($order);
        return $this->json(['message' => 'Order completed'], 200);
    }
}
