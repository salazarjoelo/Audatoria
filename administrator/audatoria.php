<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_audatoria
 *
 * @copyright   Copyright (C) 2025 Joel Salazar. Todos los derechos reservados.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

// --- Autoloader ---
$autoloader = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloader)) {
	require_once $autoloader;
} else {
    throw new \RuntimeException('El archivo autoload.php de Composer no se encuentra. Ejecuta "composer install".');
}

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Dispatcher\DispatcherInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

// Clase del componente principal
use Salazarjoelo\Component\Audatoria\Site\Extension\AudatoriaComponent; // <-- NAMESPACE CORRECTO

// Comprueba y carga el proveedor de servicios
$providerClass = null;
$providerFile = __DIR__ . '/services/provider.php';
if (file_exists($providerFile)) {
	// No necesitas incluirlo aquí si el autoloader ya lo maneja por PSR-4
	$providerClass = 'Salazarjoelo\\Component\\Audatoria\\Site\\Service\\Provider'; // <-- NAMESPACE CORRECTO DEL PROVEEDOR
    if (!class_exists($providerClass)) {
        // Si el archivo existe pero la clase no (p.ej. error en namespace o no autoloadable)
         throw new \RuntimeException('El archivo services/provider.php fue encontrado, pero la clase ' . $providerClass . ' no está definida o no es localizable.');
    }
}

/**
 * Bootstraping class for com_audatoria component
 *
 * @since  1.0.0
 */
return new class ($providerClass) implements ServiceProviderInterface {
	private $providerClassName;

	public function __construct(?string $providerClass)
	{
		$this->providerClassName = $providerClass;
	}

	public function register(Container $container): void
	{
        $namespace = 'Salazarjoelo\\Component\\Audatoria\\Site'; // Namespace base del componente

		$container->registerServiceProvider(new MVCFactory($namespace));
		$container->registerServiceProvider(new ComponentDispatcherFactory($namespace));

		if ($this->providerClassName) {
			$container->registerServiceProvider(new $this->providerClassName());
		}

		$container->set(
			ComponentInterface::class,
			function (Container $container) {
				$component = new AudatoriaComponent();
                $component->setApplication($container->get(CMSApplication::class));
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));
                $component->setDispatcher($container->get(DispatcherInterface::class));
				return $component;
			},
            true
		);
	}
};