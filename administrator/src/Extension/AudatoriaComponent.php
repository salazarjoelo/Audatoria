<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_audatoria
 *
 * @copyright   Copyright (C) 2025 Joel Salazar. Todos los derechos reservados.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Salazarjoelo\Component\Audatoria\Administrator\Extension; // Correct namespace

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\DispatcherFactoryInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Administrator main extension class for Audatoria component.
 *
 * @since  1.0.0
 */
class AudatoriaComponent extends MVCComponent
{
    /**
     * Constructor.
     *
     * @param   DispatcherFactoryInterface  $dispatcherFactory  The dispatcher factory.
     * @param   MVCFactoryInterface|null    $mvcFactory         The MVC factory.
     */
    public function __construct(DispatcherFactoryInterface $dispatcherFactory, ?MVCFactoryInterface $mvcFactory = null)
    {
        // El namespace de esta clase (Salazarjoelo\Component\Audatoria\Administrator\Extension)
        // es usado por MVCComponent para derivar el namespace base para controladores, modelos, vistas, etc.
        // como 'Salazarjoelo\Component\Audatoria\Administrator'.
        // Si tus clases MVC están en un sub-namespace diferente de este base,
        // puedes configurar el namespace base explícitamente:
        // $this->setNamespace('Salazarjoelo\\Component\\Audatoria\\Administrator'); // Ya debería ser inferido correctamente.

        parent::__construct($dispatcherFactory, $mvcFactory);
    }

    // No necesitas sobrescribir getContext() o setApplication() si heredas de MVCComponent,
    // ya que este se encarga de gran parte de la configuración.
    // El método dispatch() también es heredado.
}