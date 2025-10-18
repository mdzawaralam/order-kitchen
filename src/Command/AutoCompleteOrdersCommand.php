<?php

namespace App\Command;

use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:auto-complete-orders',
    description: 'Automatically completes orders that have passed their pickup time + buffer.',
)]
class AutoCompleteOrdersCommand extends Command
{
    private OrderRepository $orderRepository;
    private EntityManagerInterface $em;
    private int $autoCompleteDelay;

    public function __construct(OrderRepository $orderRepository, EntityManagerInterface $em, string $autoCompleteDelay = '10')
    {
        parent::__construct();
        $this->orderRepository = $orderRepository;
        $this->em = $em;
        $this->autoCompleteDelay = (int)$autoCompleteDelay;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $now = new \DateTimeImmutable();
        $cutoffTime = $now->modify("-{$this->autoCompleteDelay} minutes");

        $orders = $this->orderRepository->findOrdersToAutoComplete($cutoffTime);

        if (empty($orders)) {
            $output->writeln('<info>No orders to auto-complete.</info>');
            return Command::SUCCESS;
        }

        foreach ($orders as $order) {
            $order->setStatus('completed');
            $output->writeln("✅ Auto-completed Order ID: {$order->getId()}");
        }

        $this->em->flush();
        return Command::SUCCESS;
    }
}
