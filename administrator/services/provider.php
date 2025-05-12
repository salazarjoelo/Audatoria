<?php
// Ubicación: administrator/services/provider.php
namespace Joomla\Component\Audatoria\Administrator\Services;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\DispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory as ComponentMVCFactoryProvider;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Component\Audatoria\Administrator\Extension\AudatoriaComponent;

/**
 * Proveedor de Servicios para el componente Audatoria (Administrador).
 */
return new class implements ServiceProviderInterface
{
    /**
     * Registra los servicios del componente.
     *
     * @param   Container  $container  El contenedor de servicios.
     *
     * @return  void
     */
    public function register(Container $container): void
    {
        // Registra las fábricas específicas del componente para Dispatcher y MVC
        // Usa el namespace base EXACTO del componente para el backend.
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\Joomla\\Component\\Audatoria\\Administrator'));
        $container->registerServiceProvider(new ComponentMVCFactoryProvider('\\Joomla\\Component\\Audatoria\\Administrator'));

        // Define cómo construir la clase principal del componente (AudatoriaComponent)
        // cuando se solicite ComponentInterface dentro del contexto de este componente.
        $container->set(
            ComponentInterface::class,
            static function (Container $container) {
                // Obtiene las fábricas GLOBALES de Joomla (Dispatcher y MVC) del contenedor.
                // Si alguna de estas líneas falla, indica un problema más profundo en la
                // inicialización de Joomla o un conflicto.
                /** @var DispatcherFactoryInterface $dispatcherFactory */
                $dispatcherFactory = $container->get(DispatcherFactoryInterface::class);

                /** @var MVCFactoryInterface $mvcFactory */
                $mvcFactory = $container->get(MVCFactoryInterface::class);

                // Crea una instancia de tu clase principal de componente, inyectando las fábricas.
                $component = new AudatoriaComponent($dispatcherFactory, $mvcFactory);

                // Opcional: Puedes establecer propiedades aquí si es necesario.
                // $component->setRegistry($container->get(\Joomla\Registry\Registry::class));
                // $component->setMVCFactory($mvcFactory); // MVCComponent ya lo hace en el constructor

                return $component;
            }
        );

        // Aquí puedes registrar otros servicios específicos del backend si los necesitas.
        // Ejemplo:
        // $container->set(
        //     \Joomla\Component\Audatoria\Administrator\Helper\AudatoriaHelper::class,
        //     \Joomla\Component\Audatoria\Administrator\Helper\AudatoriaHelper::class
        // );
    }
};