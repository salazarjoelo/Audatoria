<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_audatoria
 *
 * @copyright   Copyright (C) 2025 Joel Salazar. Todos los derechos reservados.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Salazarjoelo\Component\Audatoria\Administrator\Service; // Correct namespace

\defined('_JEXEC') or die;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
// No necesitas uses adicionales aquí a menos que personalices la creación de clases MVC.

/**
 * Service Provider for the Audatoria component (Administrator side).
 *
 * @since  1.0.0
 */
class Provider implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container $container The DI container.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function register(Container $container): void
	{
        // El punto de entrada (administrator/audatoria.php) ya se encarga de registrar
        // las factorías MVC y Dispatcher con el namespace correcto para el backend:
        // 'Salazarjoelo\Component\Audatoria\Administrator'

        // Este archivo se usaría si necesitas una lógica de instanciación MÁS específica
        // para tus controladores, modelos o vistas del backend.
        // Por ejemplo, si una vista necesita argumentos de constructor adicionales:
        /*
        $container->set(
            \Salazarjoelo\Component\Audatoria\Administrator\View\Timelines\HtmlView::class,
            function (Container $container) {
                $mvcFactory = $container->get(\Joomla\CMS\MVC\Factory\MVCFactoryInterface::class);
                // Suponiendo que tu vista HtmlView acepta MVCFactory en el constructor.
                $view = new \Salazarjoelo\Component\Audatoria\Administrator\View\Timelines\HtmlView($mvcFactory);
                // $view->setSomething($container->get(MyCustomDependency::class));
                return $view;
            }
        );
        */

        // Si no hay personalizaciones de instanciación avanzadas, este método puede permanecer vacío.
	}
}