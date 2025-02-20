<?php

namespace ChainCommandBundle;

use ChainCommandBundle\DependencyInjection\Compiler\ChainRegistrationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class ChainCommandBundle
 *
 * @author Oleksii Kovalov <akovalyov@gmail.com>
 * @version 1.0
 */
class ChainCommandBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new ChainRegistrationPass());
    }
}