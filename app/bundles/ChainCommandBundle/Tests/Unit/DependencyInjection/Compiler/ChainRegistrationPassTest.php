<?php

namespace ChainCommandBundle\Tests\DependencyInjection\Compiler;

use ChainCommandBundle\DependencyInjection\Compiler\ChainRegistrationPass;
use ChainCommandBundle\Exception\ChainCommandException;
use ChainCommandBundle\Registry\ChainRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

#[AsCommand(name: 'test:master')]
class MockCommand extends Command {}

/**
 * Class ChainRegistrationPassTest
 *
 * @author Oleksii Kovalov <akovalyov@gmail.com>
 * @version 1.0
 */
class ChainRegistrationPassTest extends TestCase
{
    /**
     * @var ChainRegistrationPass
     */
    private ChainRegistrationPass $compilerPass;

    /**
     * @var ContainerBuilder
     */
    private ContainerBuilder $containerBuilder;

    /**
     * @var Definition
     */
    private Definition $registryDefinition;

    /**
     * Method setUp
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->compilerPass = new ChainRegistrationPass();
        $this->containerBuilder = $this->createMock(ContainerBuilder::class);
        $this->registryDefinition = $this->createMock(Definition::class);
    }

    /**
     * Method testProcessWithValidTaggedServices
     *
     * @return void
     */
    public function testProcessWithValidTaggedServices(): void
    {
        $serviceId = 'test.command.service';
        $masterCommandName = 'test:master';
        $commandClass = MockCommand::class;
        $commandName = 'test:master';
        $sortValue = 10;

        $commandDefinition = $this->createMock(Definition::class);
        $commandDefinition->expects($this->once())
            ->method('getClass')
            ->willReturn($commandClass);

        $this->containerBuilder->expects($this->once())
            ->method('has')
            ->with(ChainRegistry::class)
            ->willReturn(true);

        $this->containerBuilder->expects($this->once())
            ->method('findDefinition')
            ->with(ChainRegistry::class)
            ->willReturn($this->registryDefinition);

        $this->containerBuilder->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with(ChainRegistry::MEMBER_TAG)
            ->willReturn([
                $serviceId => [[
                    ChainRegistry::MEMBER_TAG_MASTER_ATTR => $masterCommandName,
                    ChainRegistry::MEMBER_TAG_SORT_ATTR => $sortValue,
                ]],
            ]);

        $this->containerBuilder->expects($this->once())
            ->method('getDefinition')
            ->with($serviceId)
            ->willReturn($commandDefinition);

        $this->registryDefinition->expects($this->once())
            ->method('addMethodCall')
            ->with('registerChainMember', [$masterCommandName, $commandName, $sortValue]);

        $this->compilerPass->process($this->containerBuilder);
    }

    /**
     * Method testGetCommandNameThrowsExceptionWhenClassDoesntExist
     *
     * @return void
     * @throws \ReflectionException
     */
    public function testGetCommandNameThrowsExceptionWhenClassDoesntExist(): void
    {
        $serviceId = 'test.invalid.service';
        $nonExistentClass = 'NonExistentClass';

        $commandDefinition = $this->createMock(Definition::class);
        $commandDefinition->expects($this->once())
            ->method('getClass')
            ->willReturn($nonExistentClass);

        $this->containerBuilder->expects($this->once())
            ->method('getDefinition')
            ->with($serviceId)
            ->willReturn($commandDefinition);

        $reflection = new \ReflectionClass(ChainRegistrationPass::class);
        $method = $reflection->getMethod('getCommandName');
        $method->setAccessible(true);

        $this->expectException(ChainCommandException::class);
        $this->expectExceptionMessage('Class ' . $nonExistentClass . ' does not exist');

        $method->invoke($this->compilerPass, $serviceId, $this->containerBuilder);
    }

    /**
     * Method testGetCommandNameThrowsExceptionWhenNotCommand
     *
     * @return void
     * @throws \ReflectionException
     */
    public function testGetCommandNameThrowsExceptionWhenNotCommand(): void
    {
        $serviceId = 'test.invalid.service';
        $nonCommandClass = self::class;

        $commandDefinition = $this->createMock(Definition::class);
        $commandDefinition->expects($this->once())
            ->method('getClass')
            ->willReturn($nonCommandClass);

        $this->containerBuilder->expects($this->once())
            ->method('getDefinition')
            ->with($serviceId)
            ->willReturn($commandDefinition);

        $reflection = new \ReflectionClass(ChainRegistrationPass::class);
        $method = $reflection->getMethod('getCommandName');
        $method->setAccessible(true);

        $this->expectException(ChainCommandException::class);
        $this->expectExceptionMessage('Command not found');

        $method->invoke($this->compilerPass, $serviceId, $this->containerBuilder);
    }

    /**
     * Method testGetCommandNameThrowsExceptionWhenNoAttributes
     *
     * @return void
     * @throws \ReflectionException
     */
    public function testGetCommandNameThrowsExceptionWhenNoAttributes(): void
    {
        $commandWithoutAttributeClass = new class extends Command {};
        $serviceId = 'test.invalid.service';

        $commandDefinition = $this->createMock(Definition::class);
        $commandDefinition->expects($this->once())
            ->method('getClass')
            ->willReturn(get_class($commandWithoutAttributeClass));

        $this->containerBuilder->expects($this->once())
            ->method('getDefinition')
            ->with($serviceId)
            ->willReturn($commandDefinition);

        $reflection = new \ReflectionClass(ChainRegistrationPass::class);
        $method = $reflection->getMethod('getCommandName');
        $method->setAccessible(true);

        $this->expectException(ChainCommandException::class);
        $this->expectExceptionMessage('Attributes not found');

        $method->invoke($this->compilerPass, $serviceId, $this->containerBuilder);
    }
}