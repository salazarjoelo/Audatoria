<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_audatoria
 * @copyright   Copyright (C) 2024 Joel Salazar. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

$namespace = 'Salazarjoelo\\Component\\Audatoria\\Administrator';
$providerClass = $namespace . '\\Service\\Provider';

if (!class_exists($providerClass)) {
    // Loguear o lanzar excepción si el Service Provider no se encuentra es útil para depurar.
    // \Joomla\CMS\Log\Log::add('Audatoria Administrator Service Provider not found: ' . $providerClass, \Joomla\CMS\Log\Log::CRITICAL, 'com_audatoria_bootstrap');
    if (JDEBUG) {
        throw new \RuntimeException('Audatoria Administrator Service Provider no encontrado: ' . $providerClass);
    }
    return;
}

return new class ($namespace, $providerClass) implements ServiceProviderInterface {
    private string $namespace;
    private string $providerClassName;

    public function __construct(string $namespace, string $providerClassForAdmin)
    {
        $this->namespace = $namespace;
        $this->providerClassName = $providerClassForAdmin;
    }

    public function register(Container $container): void
    {
        $container->registerServiceProvider(new MVCFactory($this->namespace));
        $container->registerServiceProvider(new ComponentDispatcherFactory($this->namespace));

        if ($this->providerClassName && class_exists($this->providerClassName)) {
            $container->registerServiceProvider(new $this->providerClassName());
        }

        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                $component = new ($this->namespace . '\\Extension\\AudatoriaComponent')(
                    $container->get(\Joomla\CMS\Dispatcher\DispatcherFactoryInterface::class),
                    $container->get(\Joomla\CMS\MVC\Factory\MVCFactoryInterface::class)
                );
                return $component;
            }
        );
    }
};