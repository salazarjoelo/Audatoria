<?php
// Ubicación: site/services/provider.php
namespace Joomla\Component\Audatoria\Site\Services; // Namespace para el sitio

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\DispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory as ComponentMVCFactoryProvider;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
// Asegúrate de que la ruta y el namespace de tu clase principal del sitio sean correctos
use Joomla\Component\Audatoria\Site\Extension\AudatoriaComponent;

/**
 * Proveedor de Servicios para el componente Audatoria (Sitio).
 */
return new class implements ServiceProviderInterface
{
    /**
     * Registra los servicios del componente del sitio.
     *
     * @param   Container  $container  El contenedor de servicios.
     *
     * @return  void
     */
    public function register(Container $container): void
    {
        // Registra las fábricas específicas del componente para el sitio
        // Usa el namespace base EXACTO del componente para el frontend.
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\Joomla\\Component\\Audatoria\\Site'));
        $container->registerServiceProvider(new ComponentMVCFactoryProvider('\\Joomla\\Component\\Audatoria\\Site'));

        // Define cómo construir la clase principal del componente del sitio
        $container->set(
            ComponentInterface::class,
            static function (Container $container) {
                /** @var DispatcherFactoryInterface $dispatcherFactory */
                $dispatcherFactory = $container->get(DispatcherFactoryInterface::class);

                /** @var MVCFactoryInterface $mvcFactory */
                $mvcFactory = $container->get(MVCFactoryInterface::class);

                // Instancia tu clase principal del sitio
                $component = new AudatoriaComponent($dispatcherFactory, $mvcFactory);

                return $component;
            }
        );

        // Aquí puedes registrar otros servicios específicos del frontend si los necesitas.
    }
};