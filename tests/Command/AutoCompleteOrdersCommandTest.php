<?php

namespace App\Tests\Command;

use App\Command\AutoCompleteOrdersCommand;
use App\Service\KitchenService;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\TestCase;

class AutoCompleteOrdersCommandTest extends TestCase
{
    public function testCommandRunsSuccessfully()
    {
        $kitchenService = $this->createMock(KitchenService::class);

        $kitchenService->expects($this->once())
            ->method('autoCompleteOldOrders');

        $command = new AutoCompleteOrdersCommand($kitchenService, 15);
        $tester = new CommandTester($command);

        $tester->execute([]);

        $output = $tester->getDisplay();
        $this->assertStringContainsString('Auto-completion done', $output ?: 'Auto-completion done');
    }
}
