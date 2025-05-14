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
use Joomla\CMS\MVC\Factory\MVCFactoryInterface; // Importar MVCFactoryInterface
use Joomla\CMS\Dispatcher\DispatcherFactoryInterface; // Importar DispatcherFactoryInterface
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

// Define el namespace base para las clases MVC del administrador
$namespace = 'Salazarjoelo\\Component\\Audatoria\\Administrator';

// Define la clase del proveedor de servicios
$providerClass = $namespace . '\\Service\\Provider';

if (!class_exists($providerClass)) {
    $providerFile = __DIR__ . '/services/provider.php'; // Asume que provider.php está en administrator/services/
    $errorMessage = 'Error crítico en com_audatoria (Administrador): ';
    if (file_exists($providerFile)){
        require_once $providerFile; // Intenta cargar el archivo
        if (!class_exists($providerClass)) {
             $errorMessage .= 'El archivo del proveedor de servicios (services/provider.php) fue encontrado y cargado, ' .
            'pero la clase "' . $providerClass . '" aún no está definida o no es localizable. ' .
            'Verifica el namespace dentro de ese archivo. Debería ser "' . $namespace . '\\Service\\Provider".';
        }
    } else {
        $errorMessage .= 'El archivo del proveedor de servicios (services/provider.php) no se encuentra en ' . $providerFile;
    }

    if (!class_exists($providerClass)) { // Comprueba de nuevo después de intentar cargarlo
        if (\Joomla\CMS\Factory::getApplication()->get('debug')) {
            throw new \RuntimeException($errorMessage);
        } else {
            \Joomla\CMS\Log\Log::add($errorMessage, \Joomla\CMS\Log\Log::CRITICAL, 'com_audatoria_bootstrap');
            // No mostrar un mensaje de error simple aquí en el admin, ya que la página en blanco es el síntoma
            return;
        }
    }
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
        // Registra las factorías MVC y de Despachador para el namespace del componente
        $container->registerServiceProvider(new MVCFactory($this->namespace));
        $container->registerServiceProvider(new ComponentDispatcherFactory($this->namespace));

        // Registra tu proveedor de servicios específico del componente
        if ($this->providerClassName && class_exists($this->providerClassName)) {
            $container->registerServiceProvider(new $this->providerClassName());
        }

        // Define cómo se debe construir la clase principal de tu componente
        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                // Asegúrate de que la clase AudatoriaComponent exista en el namespace correcto
                $componentClass = $this->namespace . '\\Extension\\AudatoriaComponent';
                if (!class_exists($componentClass)) {
                    // Intenta cargar el archivo si no existe la clase
                    $componentFile = JPATH_ADMINISTRATOR . '/components/com_audatoria/src/Extension/AudatoriaComponent.php';
                    if (file_exists($componentFile)) {
                        require_once $componentFile;
                    }
                    if (!class_exists($componentClass)) {
                         throw new \RuntimeException('Clase principal del componente administrador no encontrada: ' . $componentClass);
                    }
                }
                $component = new $componentClass(
                    $container->get(DispatcherFactoryInterface::class), // Corregido aquí
                    $container->get(MVCFactoryInterface::class)          // Corregido aquí
                );
                // MVCComponent se encarga de setApplication si es necesario
                return $component;
            }
        );
    }
};