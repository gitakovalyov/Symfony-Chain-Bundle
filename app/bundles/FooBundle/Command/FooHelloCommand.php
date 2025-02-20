<?php

namespace FooBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

/**
 * Class FooHelloCommand
 *
 * @author Oleksii Kovalov <akovalyov@gmail.com>
 * @version 1.0
 *
 * @command foo:hello Outputs "Hello from Foo!"
 * @tag console.command Registers this class as a Symfony console command.
 */
#[AsCommand(
    name: 'foo:hello',
    description: 'Outputs "Hello from Foo!"'
)]
#[AsTaggedItem('console.command')]
class FooHelloCommand extends Command
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
        $output->writeln('Hello from Foo!');
        return Command::SUCCESS;
    }
}
