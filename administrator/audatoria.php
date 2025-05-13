<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_audatoria
 *
 * @copyright   Copyright (C) 2025 Joel Salazar. Todos los derechos reservados.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

// Autoloader de Composer (opcional si no tienes dependencias de terceros específicas del backend)
$autoloader = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloader)) {
	require_once $autoloader;
}

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Dispatcher\DispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Salazarjoelo\Component\Audatoria\Administrator\Extension\AudatoriaComponent;

$providerClass = 'Salazarjoelo\\Component\\Audatoria\\Administrator\\Service\\Provider';

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
    // En producción, es mejor solo loguear y mostrar un error genérico.
    // Durante el desarrollo, una excepción puede ser útil.
    if (\Joomla\CMS\Factory::getApplication()->get('debug')) {
        throw new \RuntimeException($errorMessage);
    } else {
        \Joomla\CMS\Log\Log::add($errorMessage, \Joomla\CMS\Log\Log::CRITICAL, 'com_audatoria');
        // Podrías mostrar un error genérico aquí si el componente no puede arrancar.
        echo 'Error al cargar el componente Audatoria.';
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
                // MVCComponent usualmente obtiene la aplicación del contenedor si es necesario.
                // Si necesitas pasarla explícitamente:
                // $component->setApplication($container->get(AdministratorApplication::class));
				return $component;
			},
            true // Shared
		);
	}
};