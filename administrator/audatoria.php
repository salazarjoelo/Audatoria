<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_audatoria
 *
 * @copyright   Copyright (C) 2025 Joel Salazar. Todos los derechos reservados.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

// --- Autoloader de Composer (Opcional) ---
// Solo intenta cargar si existe, sin lanzar error si no.
$autoloader = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
}
// Si tus clases en src/ dependen estrictamente de este autoloader y no solo del de Joomla,
// entonces la ausencia de este archivo podría causar problemas más adelante al intentar usar esas clases.
// Por ahora, la instalación no fallará por la ausencia del archivo en sí.

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Dispatcher\DispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Salazarjoelo\Component\Audatoria\Administrator\Extension\AudatoriaComponent; // Asegúrate que este namespace y clase existan

$providerClass = 'Salazarjoelo\\Component\\Audatoria\\Administrator\\Service\\Provider'; // Asegúrate que este namespace y clase existan

if (!class_exists($providerClass)) {
    $providerFile = __DIR__ . '/services/provider.php';
    $errorMessage = 'Error crítico en com_audatoria (Administrador): ';
    if (file_exists($providerFile)){
        $errorMessage .= 'El archivo del proveedor de servicios (services/provider.php) fue encontrado, ' .
            'pero la clase "' . $providerClass . '" no está definida o no es localizable. ' .
            'Verifica el namespace dentro de ese archivo. Debería ser "Salazarjoelo\\Component\\Audatoria\\Administrator\\Service".';
    } else {
        $errorMessage .= 'El archivo del proveedor de servicios (services/provider.php) no se encuentra en ' . $providerFile;
    }
    if (\Joomla\CMS\Factory::getApplication()->get('debug')) {
        throw new \RuntimeException($errorMessage);
    } else {
        \Joomla\CMS\Log\Log::add($errorMessage, \Joomla\CMS\Log\Log::CRITICAL, 'com_audatoria');
        if (\Joomla\CMS\Factory::getApplication()->isClient('administrator')) {
             \Joomla\CMS\Factory::getApplication()->enqueueMessage($errorMessage, 'error');
        } else {
             echo 'Error al cargar el componente Audatoria.'; // Mensaje genérico para el sitio si esto se incluye por error
        }
        return;
    }
}

/**
 * Punto de entrada y proveedor de servicios para com_audatoria (Administrador)
 *
 * @since  1.0.0
 */
return new class ($providerClass) implements ServiceProviderInterface {
    private string $providerClassName;

    public function __construct(string $providerClassForAdmin)
    {
        $this->providerClassName = $providerClassForAdmin;
    }

    public function register(Container $container): void
    {
        $namespace = 'Salazarjoelo\\Component\\Audatoria\\Administrator';

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
                // $component->setApplication($container->get(AdministratorApplication::class)); // MVCComponent lo maneja
                return $component;
            },
            true
        );
    }
};