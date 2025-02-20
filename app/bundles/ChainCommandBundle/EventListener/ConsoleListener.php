<?php

namespace ChainCommandBundle\EventListener;

use ChainCommandBundle\Registry\ChainRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ConsoleListener
 *
 * @author Oleksii Kovalov <akovalyov@gmail.com>
 * @version 1.0
 *
 * @tag kernel.event_subscriber This class is automatically registered as an event subscriber.
 */
#[AsTaggedItem('kernel.event_subscriber')]
class ConsoleListener implements EventSubscriberInterface
{

    /**
     * @var bool
     */
    private bool $isMasterExecuted = false;
    /**
     * @var string|null
     */
    private ?string $masterCommandName = null;

    /**
     * @param ChainRegistry $chainRegistry
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly ChainRegistry $chainRegistry,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND => ['onConsoleCommand', 10],
            ConsoleEvents::TERMINATE => ['onConsoleTerminate', 10],
        ];
    }

    /**
     * Method onConsoleCommand
     *
     * @param ConsoleCommandEvent $event
     * @return void
     */
    public function onConsoleCommand(ConsoleCommandEvent $event): void
    {
        $command = $event->getCommand();
        if (!$command) {
            return;
        }
        $commandName = $command->getName();
        $output = $event->getOutput();

        // restrict to run directly member command. Disable command
        if ($this->chainRegistry->isChainMember($commandName)) {
            $masterName = $this->chainRegistry->getMasterForMember($commandName);
            $msg = sprintf(
                "Error: %s command is a member of %s command chain and cannot be executed on its own.",
                $commandName,
                $masterName
            );
            $output->writeln("<error>$msg</error>");
            $event->disableCommand();
            $event->stopPropagation();
            return;
        }

        // Logging master and it members
        if ($this->chainRegistry->isChainMaster($commandName)) {
            $this->isMasterExecuted = true;
            $this->masterCommandName = $commandName;

            $members = $this->chainRegistry->getChainMembers($commandName);
            $this->logger->info(sprintf(
                '%s is a master command of a command chain that has registered member commands',
                $commandName
            ));
            foreach ($members as $member) {
                $this->logger->info(sprintf(
                    '%s registered as a member of %s command chain',
                    $member,
                    $commandName
                ));
            }

            $this->logger->info(sprintf('Executing %s command itself first:', $commandName));
        }
    }

    /**
     * Method onConsoleTerminate
     *
     * @param ConsoleTerminateEvent $event
     * @return void
     * @throws ExceptionInterface
     */
    public function onConsoleTerminate(ConsoleTerminateEvent $event): void
    {
        $command = $event->getCommand();
        if (!$command) {
            return;
        }
        $commandName = $command->getName();

        if ($this->isMasterExecuted && $this->masterCommandName === $commandName) {
            $output = $event->getOutput();
            $this->logger->info($output->getFormatter()->format($commandName));

            $members = $this->chainRegistry->getChainMembers($commandName);
            if (!empty($members)) {
                $this->logger->info(sprintf('Executing %s chain members:', $commandName));
                foreach ($members as $memberName) {
                    $this->runSubCommand($memberName, $event);
                }
            }

            $this->logger->info(sprintf('Execution of %s chain completed.', $commandName));

            $this->isMasterExecuted = false;
            $this->masterCommandName = null;
        }
    }

    /**
     * Run Sub Command for master chain
     *
     * @param string $commandName
     * @param ConsoleTerminateEvent $event
     * @return void
     * @throws ExceptionInterface
     */
    private function runSubCommand(string $commandName, ConsoleTerminateEvent $event): void
    {
        $application = $event->getCommand()->getApplication();
        $bufferedOutput = $this->getBufferedOutput();

        $subcommand = $application->find($commandName);
        $input = new \Symfony\Component\Console\Input\ArrayInput([
            'command' => $commandName
        ]);

        $subcommand->run($input, $bufferedOutput);

        $outputMessage = $bufferedOutput->fetch();

        $event->getOutput()->write($outputMessage);
        $this->logger->info($outputMessage);
    }

    /**
     * Method getBufferedOutput
     *
     * @return BufferedOutput
     */
    private function getBufferedOutput(): BufferedOutput
    {
        return new BufferedOutput();
    }
}