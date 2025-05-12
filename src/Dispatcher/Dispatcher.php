<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_audatoria
 *
 * @copyright   Copyright (C) 2025 Joel Salazar. Todos los derechos reservados.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Salazarjoelo\Component\Audatoria\Site\Dispatcher; // <-- NAMESPACE CORREGIDO

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractComponentDispatcher;
use Joomla\CMS\Extension\ComponentInterface;

/**
 * Dispatcher class for com_audatoria
 *
 * @since  1.0.0
 */
class Dispatcher extends AbstractComponentDispatcher
{
    /**
	 * Component Interface
	 *
	 * @var ComponentInterface
	 * @since 1.0.0
	 */
	private $component;

	/**
	 * Constructor.
	 *
	 * @param   array               $options    Optional arguments
	 * @param   ComponentInterface  $component  The component instance
	 *
	 * @since   1.0.0
	 */
	public function __construct($options = [], ComponentInterface $component = null)
	{
		parent::__construct($options);
		$this->component = $component;
	}

    /**
	 * Método para despachar la solicitud al controlador adecuado.
     * Sobrescribir si necesitas lógica de enrutamiento personalizada aquí.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
    public function dispatch(): void
    {
        // La lógica por defecto de AbstractComponentDispatcher buscará
        // un controlador basado en la petición (task, view).
        // Con DisplayController.php ahora presente, debería funcionar
        // para mostrar vistas estándar.
        parent::dispatch();
    }

	/**
	 * Returns the prefix for controller script names.
     * Ajusta si tus clases Controller tienen un prefijo diferente.
	 *
	 * @return  string  The prefix.
	 *
	 * @since   1.0.0
	 */
	protected function getControllerPrefix(): string
	{
		return 'Audatoria'; // Por defecto buscaría AudatoriaController, DisplayController, etc.
	}
}