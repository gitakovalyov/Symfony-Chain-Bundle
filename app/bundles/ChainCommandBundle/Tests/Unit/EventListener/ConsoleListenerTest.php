<?php

namespace ChainCommandBundle\Tests\UnitEventListener;

use ChainCommandBundle\EventListener\ConsoleListener;
use ChainCommandBundle\Registry\ChainRegistry;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Assert;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatter;

/**
 * Class ConsoleListenerTest
 *
 * @author Oleksii Kovalov <akovalyov@gmail.com>
 * @version 1.0
 */
class ConsoleListenerTest extends TestCase
{
    /**
     * @var ChainRegistry
     */
    private ChainRegistry $chainRegistry;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    /**
     * @var ConsoleListener
     */
    private ConsoleListener $listener;
    /**
     * @var Command
     */
    private Command $command;
    /**
     * @var InputInterface
     */
    private InputInterface $input;
    /**
     * @var OutputInterface
     */
    private OutputInterface $output;
    /**
     * @var Application
     */
    private Application $application;

    /**
     * Method setUp
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->chainRegistry = $this->createMock(ChainRegistry::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->listener = new ConsoleListener($this->chainRegistry, $this->logger);

        // Create command mock
        $this->command = $this->createMock(Command::class);
        $this->command->method('getName')->willReturn('test:command');

        // Create input mock
        $this->input = $this->createMock(InputInterface::class);

        // Create output mock
        $this->output = $this->createMock(OutputInterface::class);
        $outputFormatter = $this->createMock(OutputFormatter::class);
        $outputFormatter->method('format')->willReturn('formatted command');
        $this->output->method('getFormatter')->willReturn($outputFormatter);

        // Create application mock
        $this->application = $this->createMock(Application::class);
    }

    /**
     * Method testOnConsoleCommandWithChainMember
     *
     * @return void
     */
    public function testOnConsoleCommandWithChainMember(): void
    {
        $command = $this->createMock(Command::class);
        $command->method('getName')->willReturn('test:member');

        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $event = new ConsoleCommandEvent($command, $input, $output);

        $this->chainRegistry->method('isChainMember')->with('test:member')->willReturn(true);
        $this->chainRegistry->method('getMasterForMember')->with('test:member')->willReturn('test:master');

        $output->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('test:member command is a member of test:master command chain'));

        $this->listener->onConsoleCommand($event);

        $this->assertTrue($event->commandShouldRun() === false);
    }

    /**
     * Method testOnConsoleCommandWithChainMaster
     *
     * @return void
     */
    public function testOnConsoleCommandWithChainMaster(): void
    {
        $command = $this->createMock(Command::class);
        $command->method('getName')->willReturn('test:master');

        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $event = new ConsoleCommandEvent($command, $input, $output);

        $this->chainRegistry->method('isChainMember')->with('test:master')->willReturn(false);
        $this->chainRegistry->method('isChainMaster')->with('test:master')->willReturn(true);
        $this->chainRegistry->method('getChainMembers')->with('test:master')->willReturn(['test:member']);

        $this->logger->expects($this->exactly(3))
            ->method('info')
            ->willReturnCallback(function ($message) {
                static $callIndex = 0;
                $expectedParts = [
                    'is a master command of a command',
                    'registered as a member of',
                    'command itself first'
                ];

                Assert::assertStringContainsString($expectedParts[$callIndex], $message);
                $callIndex++;
            });

        $this->listener->onConsoleCommand($event);

        $this->assertTrue($event->commandShouldRun() === true);
    }
}