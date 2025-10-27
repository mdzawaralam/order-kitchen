<?php

namespace App\Command;

use App\Service\KitchenService;
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
    private KitchenService $kitchenService;
    private int $autoCompleteDelay;

    public function __construct(KitchenService $kitchenService, string $autoCompleteDelay = '10')
    {
        parent::__construct();
        $this->kitchenService = $kitchenService;
        $this->autoCompleteDelay = (int)$autoCompleteDelay;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->kitchenService->autoCompleteOldOrders();
        $output->writeln('<info>Auto-completion done</info>');

        return Command::SUCCESS;
    }
}
