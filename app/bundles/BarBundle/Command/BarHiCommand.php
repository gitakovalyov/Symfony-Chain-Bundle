<?php

namespace BarBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Class BarHiCommand
 *
 * @author Oleksii Kovalov <akovalyov@gmail.com>
 * @version 1.0
 *
 * @command bar:hi Outputs "Hi from Bar!"
 * @tag console.command Registers this class as a Symfony console command.
 * @tag chain_command.member Registers this command as a chain member under "foo:hello".
 */
#[AsCommand(
    name: 'bar:hi',
    description: 'Outputs "Hi from Bar!"'
)]
#[AutoconfigureTag('console.command')]
#[AutoconfigureTag(
    'chain_command.member',
    [
        'master' => 'foo:hello',
        'sort' => 10
    ]
)]
class BarHiCommand extends Command
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
        $output->writeln('Hi from Bar!');
        return Command::SUCCESS;
    }
}
