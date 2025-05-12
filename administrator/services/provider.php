<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_audatoria
 *
 * @copyright   Copyright (C) 2025 Joel Salazar. Todos los derechos reservados.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Salazarjoelo\Component\Audatoria\Site\Service; // <-- NAMESPACE CORRECTO

\defined('_JEXEC') or die;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\View\CategoryView; // Ejemplo si necesitas vistas específicas
use Joomla\CMS\MVC\View\HtmlView;     // Ejemplo si necesitas vistas específicas


/**
 * Proveedor de Servicios para el componente Audatoria (Sitio)
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
		// Aquí es donde puedes registrar servicios específicos o sobreescribir
        // la forma en que se crean las clases MVC si necesitas lógica personalizada.

        /* Ejemplo: Sobreescribir cómo se crea la vista HtmlView
        $container->set(
            HtmlView::class,
            function (Container $container) {
                $view = new \Salazarjoelo\Component\Audatoria\Site\View\Auditoria\HtmlView(
                    $container->get(MVCFactoryInterface::class),
                    $container->get('ComponentDispatcherShared'), // Obtener el dispatcher
                    $container->get(\Joomla\Event\DispatcherInterface::class) // Obtener el event dispatcher
                );

                // Inyectar dependencias adicionales si es necesario
                // $view->setSomething(...);

                return $view;
            }
        );
        */

        // Por ahora, si no necesitas personalización avanzada, este archivo
        // puede simplemente existir y estar correctamente referenciado
        // en el manifiesto y el punto de entrada.
	}
}