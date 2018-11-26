<?php

namespace Kiboko\Component\Payum\Sips\Bridge\Symfony\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class TwigPathCompilerPass implements CompilerPassInterface
{
    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('twig.loader.filesystem')) {
            $loader = $container->getDefinition('twig.loader.filesystem');

            $path = realpath(__DIR__ . '/../../../Resources/views');
            $loader->addMethodCall('addPath', [$path, 'EkynaPayumSips']);
        }
    }
}
