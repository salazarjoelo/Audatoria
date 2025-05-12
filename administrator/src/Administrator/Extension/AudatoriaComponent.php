<?php
namespace Joomla\Component\Audatoria\Administrator\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\DI\Container;

class AudatoriaComponent implements BootableExtensionInterface, ComponentInterface
{
    protected $dispatcherFactory;

    public function __construct(ComponentDispatcherFactoryInterface $dispatcherFactory)
    {
        $this->dispatcherFactory = $dispatcherFactory;
    }

    public function boot(Container $container) {}

    public function getDispatcherFactory(): ComponentDispatcherFactoryInterface
    {
        return $this->dispatcherFactory;
    }

    public function setMVCFactory(MVCFactoryInterface $factory): void
    {
        // Implementación MVC si es necesaria
    }

    public function setRegistry(Registry $registry): void
    {
        // Configuración de registro
    }
}