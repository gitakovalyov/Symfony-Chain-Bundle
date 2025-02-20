<?php

namespace ChainCommandBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Bundle\FrameworkBundle\Console\Application;

class ChainCommandFunctionalTest extends KernelTestCase
{
    /**
     * Test run master command. Should trigger member
     *
     * @return void
     * @throws \Exception
     */
    public function testMasterCommandTriggersMemberOutput()
    {
        self::bootKernel();

        $outputText = $this->runCommand('test:master');

        $this->assertStringContainsString('Master Test!', $outputText);
        $this->assertStringContainsString('Member Test!', $outputText);
    }

    /**
     * Test run member command. Member shouldn't be executed, error display
     *
     * @return void
     * @throws \Exception
     */
    public function testMemberCommandAloneShowsError()
    {
        self::bootKernel();

        $outputText = $this->runCommand('test:member');

        $this->assertStringContainsString(
            'Error: test:member command is a member of test:master command',
            $outputText
        );
    }

    /**
     * Method runCommand
     *
     * @param string $commandName
     * @param array $args
     * @return string
     * @throws \Exception
     */
    private function runCommand(string $commandName, array $args = []): string
    {
        $application = new Application(self::$kernel);

        $application->setAutoExit(false);

        $input = new ArrayInput(array_merge([
            'command' => $commandName,
        ], $args));

        $output = new BufferedOutput();

        $exitCode = $application->run($input, $output);

        return $output->fetch();
    }
}