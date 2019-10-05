<?php declare(strict_types=1);

namespace Rector\RectorCI\HttpKernel;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class RectorCIKernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * @var string
     */
    private const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    /**
     * @return BundleInterface[]
     */
    public function registerBundles(): iterable
    {
        $bundles = require __DIR__ . '/../../config/bundles.php';

        foreach ($bundles as $bundleClass => $enabledEnvironments) {
            if ($enabledEnvironments[$this->environment] ?? $enabledEnvironments['all'] ?? false) {
                yield new $bundleClass();
            }
        }
    }

    protected function configureContainer(ContainerBuilder $containerBuilder, LoaderInterface $loader): void
    {
        $containerBuilder->addResource(new FileResource($this->getProjectDir() . '/config/bundles.php'));
        $containerBuilder->setParameter('container.dumper.inline_class_loader', true);
        $confDir = $this->getProjectDir() . '/config';

        $loader->load($confDir . '/{packages}/*' . self::CONFIG_EXTS, 'glob');
        $loader->load($confDir . '/{packages}/' . $this->environment . '/**/*' . self::CONFIG_EXTS, 'glob');
        $loader->load($confDir . '/{services}' . self::CONFIG_EXTS, 'glob');
        $loader->load($confDir . '/{services}_' . $this->environment . self::CONFIG_EXTS, 'glob');
    }

    protected function configureRoutes(RouteCollectionBuilder $routeCollectionBuilder): void
    {
        $confDir = $this->getProjectDir() . '/config';

        $routeCollectionBuilder->import(
            $confDir . '/{routes}/' . $this->environment . '/**/*' . self::CONFIG_EXTS,
            '/',
            'glob'
        );
        $routeCollectionBuilder->import($confDir . '/{routes}/*' . self::CONFIG_EXTS, '/', 'glob');
        $routeCollectionBuilder->import($confDir . '/{routes}' . self::CONFIG_EXTS, '/', 'glob');
    }
}
