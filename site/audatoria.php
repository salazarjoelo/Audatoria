<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_audatoria
 *
 * @copyright   Copyright (C) 2025 Joel Salazar. Todos los derechos reservados.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

// --- Autoloader de Composer (Opcional) ---
$autoloader = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
}
// No lanzar error si no existe, para permitir la instalación sin la carpeta vendor.

use Joomla\CMS\Application\SiteApplication; // Específico para el sitio
use Joomla\CMS\Dispatcher\DispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Salazarjoelo\Component\Audatoria\Site\Extension\AudatoriaComponent; // Asegúrate que este namespace y clase existan

$providerClass = 'Salazarjoelo\\Component\\Audatoria\\Site\\Service\\Provider'; // Asegúrate que este namespace y clase existan

if (!class_exists($providerClass)) {
    $providerFile = __DIR__ . '/services/provider.php';
    $errorMessage = 'Error crítico en com_audatoria (Sitio): ';
    if (file_exists($providerFile)){
        $errorMessage .= 'El archivo del proveedor de servicios (services/provider.php) fue encontrado, ' .
            'pero la clase "' . $providerClass . '" no está definida o no es localizable. ' .
            'Verifica el namespace dentro de ese archivo. Debería ser "Salazarjoelo\\Component\\Audatoria\\Site\\Service".';
    } else {
        $errorMessage .= 'El archivo del proveedor de servicios (services/provider.php) no se encuentra en ' . $providerFile;
    }

    if (\Joomla\CMS\Factory::getApplication()->get('debug')) {
        throw new \RuntimeException($errorMessage);
    } else {
        \Joomla\CMS\Log\Log::add($errorMessage, \Joomla\CMS\Log\Log::CRITICAL, 'com_audatoria');
        // Para el frontend, podrías simplemente mostrar un mensaje de error genérico o nada.
        echo 'Error al cargar el contenido de Audatoria.';
        return;
    }
}

/**
 * Punto de entrada y proveedor de servicios para com_audatoria (Sitio)
 *
 * @since  1.0.0
 */
return new class ($providerClass) implements ServiceProviderInterface {
    private string $providerClassName;

    public function __construct(string $providerClassForSite)
    {
        $this->providerClassName = $providerClassForSite;
    }

    public function register(Container $container): void
    {
        $namespace = 'Salazarjoelo\\Component\\Audatoria\\Site';

        $container->registerServiceProvider(new MVCFactory($namespace));
        $container->registerServiceProvider(new ComponentDispatcherFactory($namespace));

        if ($this->providerClassName && class_exists($this->providerClassName)) {
            $container->registerServiceProvider(new $this->providerClassName());
        }

        $container->set(
            ComponentInterface::class,
            static function (Container $container) {
                $component = new AudatoriaComponent(
                    $container->get(DispatcherFactoryInterface::class),
                    $container->get(MVCFactoryInterface::class)
                );
                // $component->setApplication($container->get(SiteApplication::class)); // MVCComponent lo maneja
                return $component;
            },
            true
        );
    }
};