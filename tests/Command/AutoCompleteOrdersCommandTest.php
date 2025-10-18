<?php

namespace App\Tests\Command;

use App\Command\AutoCompleteOrdersCommand;
use App\Repository\OrderRepository;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\TestCase;

class AutoCompleteOrdersCommandTest extends TestCase
{
    public function testCommandRunsSuccessfully()
    {
        $orderRepo = $this->createMock(OrderRepository::class);
        $orderRepo->expects($this->once())->method('autoCompleteOldOrders');

        $command = new AutoCompleteOrdersCommand($orderRepo, 15);
        $tester = new CommandTester($command);
        $tester->execute([]);

        $this->assertStringContainsString('Auto-completion done', $tester->getDisplay());
    }
}
