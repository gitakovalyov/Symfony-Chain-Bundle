<?php

namespace ChainCommandBundle\Command\TestCommands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Class ChainCommandTestCommand
 *
 * @author Oleksii Kovalov <akovalyov@gmail.com>
 * @version 1.0
 *
 * @command test:greeting Outputs "Greeting from Test!"
 * @tag console.command Registers this class as a Symfony console command.
 */
#[AsCommand(
    name: 'test:greeting',
    description: 'Outputs "Greeting from Test!"'
)]
#[AutoconfigureTag('console.command')]
class ChainCommandTestCommand extends Command
{

    /**
     * Execute command method
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Greeting from Test!');
        return Command::SUCCESS;
    }
}
