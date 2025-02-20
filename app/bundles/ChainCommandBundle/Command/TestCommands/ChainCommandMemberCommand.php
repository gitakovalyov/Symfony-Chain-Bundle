<?php

namespace ChainCommandBundle\Command\TestCommands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Class ChainCommandMemberCommand
 *
 * @author Oleksii Kovalov <akovalyov@gmail.com>
 * @version 1.0
 *
 * @command test:member Outputs "Member Test!"
 * @tag console.command Registers this class as a Symfony console command.
 * @tag chain_command.member Registers this command as a chain member under one or more master commands.
 *      can be a string or an array
 */
#[AsCommand(
    name: 'test:member',
    description: 'Outputs "Member Test!"'
)]
#[AutoconfigureTag('console.command')]
#[AutoconfigureTag(
    'chain_command.member',
    [
        'master' => 'test:master',
        'sort' => 10
    ]
)]
class ChainCommandMemberCommand extends Command
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
        $output->writeln('Member Test!');
        return Command::SUCCESS;
    }
}
