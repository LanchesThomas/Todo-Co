<?php

namespace App;

use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Kernel extends BaseKernel
{
    public function registerBundles()
    {
        $contents = require $this->getProjectDir().'/config/bundles.php';

        $bundles = [];
        foreach ($contents as $class => $envs) {
            if ($envs[$this->getEnvironment()] ?? $envs['all'] ?? false) {
                $bundles[] = new $class();
            }
        }

        return $bundles;
    }

    public function getProjectDir(): string
    {
        return \dirname(__DIR__);
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getProjectDir().'/config/packages/*.yaml', 'glob');
        $loader->load($this->getProjectDir().'/config/services.yaml');
    }
}
