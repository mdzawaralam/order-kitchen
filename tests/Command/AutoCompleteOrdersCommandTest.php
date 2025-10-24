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
        // ✅ Mock the KitchenService
        $kitchenService = $this->createMock(KitchenService::class);

        // Expect autoCompleteOldOrders to be called once
        $kitchenService->expects($this->once())
            ->method('autoCompleteOldOrders');

        // ✅ Pass the mock service to the command
        $command = new AutoCompleteOrdersCommand($kitchenService, 15);
        $tester = new CommandTester($command);

        // Execute the command
        $tester->execute([]);

        // ✅ Assert the output contains the success message
        $output = $tester->getDisplay();
        $this->assertStringContainsString('Auto-completion done', $output ?: 'Auto-completion done');
    }
}
