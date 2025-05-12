<?php
// Ubicación: site/controllers/timeline.php
namespace Joomla\Component\Audatoria\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Controlador para la vista Timeline en el frontend.
 */
class TimelineController extends BaseController
{
    /**
     * Método para mostrar la vista predeterminada (timeline).
     *
     * @param   boolean $cachable   Si es true, la vista será cacheada.
     * @param   array   $urlparams  Array asociativo de parámetros seguros para la URL.
     *
     * @return  void
     */
    public function display($cachable = false, $urlparams = []): void
    {
        // BaseController ya maneja la carga de la vista basada en el parámetro 'view'.
        // Si 'view' no está presente, usará el nombre del controlador ('timeline').
        parent::display($cachable, $urlparams);
    }
}