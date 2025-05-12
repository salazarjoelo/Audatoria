<?php
namespace Joomla\Component\Audatoria\Administrator\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;

class AudatoriaComponent implements BootableExtensionInterface, ComponentInterface
{
    /**
     * @var ComponentDispatcherFactoryInterface
     */
    protected $dispatcherFactory;

    /**
     * @var MVCFactoryInterface|null
     */
    protected $mvcFactory;

    /**
     * @var Registry
     */
    protected $registry;

    public function __construct(ComponentDispatcherFactoryInterface $dispatcherFactory)
    {
        $this->dispatcherFactory = $dispatcherFactory;
    }

    /**
     * Boot the component
     */
    public function boot(Container $container): void
    {
        // Lógica de inicialización si es necesaria
    }

    /**
     * Obtiene el factory de dispatchers
     */
    public function getDispatcherFactory(): ComponentDispatcherFactoryInterface
    {
        return $this->dispatcherFactory;
    }

    /**
     * Establece el factory MVC
     */
    public function setMVCFactory(MVCFactoryInterface $factory): void
    {
        $this->mvcFactory = $factory;
    }

    /**
     * Establece el registro de componentes
     */
    public function setRegistry(Registry $registry): void
    {
        $this->registry = $registry;
    }

    /**
     * Registra listeners legacy (requerido por ComponentInterface)
     */
    public function registerLegacyListeners(): void
    {
        // No necesario en Joomla 5, pero requerido por la interfaz
    }

    /**
     * Obtiene el registro (requerido por ComponentInterface)
     */
    public function getRegistry(): Registry
    {
        return $this->registry;
    }
}