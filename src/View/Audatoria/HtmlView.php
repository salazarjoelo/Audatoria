<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_audatoria
 *
 * @copyright   Copyright (C) 2025 Joel Salazar. Todos los derechos reservados.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Salazarjoelo\Component\Audatoria\Site\View\Auditoria; // <-- NAMESPACE CORREGIDO

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Log\Log;

/**
 * HTML View class for the Audatoria Component (Site)
 * View 'Auditoria'
 *
 * @since  1.0.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * Display the view
	 *
	 * @param   string $tpl The name of the template file to parse.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function display($tpl = null): void
	{
        // Ejemplo: Obtener parámetros del componente o del menú
        $this->params = $this->state?->get('params') ?? $this->getApplication()->getParams();

        Log::add('Cargando vista: Auditoria', Log::INFO, 'com_audatoria');

        // Asigna datos si es necesario (requeriría un Modelo asociado)
        // $this->items = $this->get('Items');

		parent::display($tpl);
	}
}