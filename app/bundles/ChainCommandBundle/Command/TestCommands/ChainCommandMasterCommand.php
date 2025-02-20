<?php

namespace ChainCommandBundle\Command\TestCommands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Class ChainCommandMasterCommand
 *
 * @author Oleksii Kovalov <akovalyov@gmail.com>
 * @version 1.0
 *
 * @command test:master Outputs "Master Test!"
 * @tag console.command Registers this class as a Symfony console command.
 */
#[AsCommand(
    name: 'test:master',
    description: 'Outputs "Master Test!"'
)]
#[AutoconfigureTag('console.command')]
class ChainCommandMasterCommand extends Command
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
        $output->writeln('Master Test!');
        return Command::SUCCESS;
    }
}
