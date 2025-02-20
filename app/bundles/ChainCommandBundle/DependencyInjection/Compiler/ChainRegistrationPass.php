<?php

namespace ChainCommandBundle\DependencyInjection\Compiler;

use ChainCommandBundle\Exception\ChainCommandException;
use ChainCommandBundle\Registry\ChainRegistry;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class ChainRegistrationPass
 *
 * @author Oleksii Kovalov <akovalyov@gmail.com>
 * @version 1.0
 */
class ChainRegistrationPass implements CompilerPassInterface
{
    /**
     * Method process
     *
     * @param ContainerBuilder $container
     * @return void
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(ChainRegistry::class)) {
            return;
        }

        // Get tag`s and add to Registry master and member
        $registryDef = $container->findDefinition(ChainRegistry::class);
        $taggedServices = $container->findTaggedServiceIds(ChainRegistry::MEMBER_TAG);
        foreach ($taggedServices as $serviceId => $tags) {
            $master = $tags[0][ChainRegistry::MEMBER_TAG_MASTER_ATTR] ?? null;
            $sort = $tags[0][ChainRegistry::MEMBER_TAG_SORT_ATTR] ?? ChainRegistry::MEMBER_TAG_DEFAULT_SORT_ATTR;
            if (!$master) {
                continue;
            }

            try {
                $defaultName = $this->getCommandName($serviceId, $container);
                $registryDef->addMethodCall('registerChainMember', [$master, $defaultName, intval($sort)]);
            } catch (ChainCommandException $e) {}
        }
    }

    /**
     * Get command name
     *
     * @param string $serviceId
     * @param ContainerBuilder $container
     * @return string
     * @throws ChainCommandException
     */
    private function getCommandName(string $serviceId, ContainerBuilder $container): string
    {
        $definition = $container->getDefinition($serviceId);
        $class = $definition->getClass();
        if (!class_exists($class)) {
            throw new ChainCommandException('Class ' . $class . ' does not exist');
        }

        $reflection = new ReflectionClass($class);
        if (!$reflection->isSubclassOf(Command::class)) {
            throw new ChainCommandException('Command not found');
        }

        $attributes = $reflection->getAttributes(AsCommand::class);
        if (empty($attributes)) {
            throw new ChainCommandException('Attributes not found');
        }

        $asCommand = $attributes[0]->newInstance();
        return $asCommand->name;
    }
}