<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_audatoria
 *
 * @copyright   Copyright (C) 2025 Joel Salazar. Todos los derechos reservados.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Salazarjoelo\Component\Audatoria\Site\Controller; // <-- NAMESPACE CORRECTO

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Controlador de visualización principal para com_audatoria (Sitio).
 * Este controlador se encarga de manejar la visualización de las vistas.
 *
 * @since  1.0.0
 */
class DisplayController extends BaseController
{
	/**
	 * Método para mostrar una vista.
	 * Generalmente, en un DisplayController simple, solo se necesita
	 * llamar al método display() del padre, que se encargará de
	 * cargar la vista correcta según la solicitud.
	 *
	 * @param   boolean $cachable   Si es true, la salida de la vista será cacheada.
	 * @param   array   $urlparams  Array de parámetros seguros de URL.
	 *
	 * @return  static  Este objeto para permitir encadenamiento.
	 *
	 * @since   1.0.0
	 */
	public function display($cachable = false, $urlparams = [])
	{
		// Aquí no se necesita lógica extra si solo queremos mostrar la vista
        // determinada por la petición (p. ej., la vista 'Auditoria').
        // La clase BaseController se encarga de eso.
		return parent::display($cachable, $urlparams);
	}
}