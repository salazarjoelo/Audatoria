<?php
namespace Salazarjoelo\Component\Audatoria\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Controlador de visualización genérico para el backend de Audatoria.
 * Este controlador se usará si no se encuentra un controlador más específico
 * para la vista solicitada (ej. si solo se pasa ?option=com_audatoria&view=alguna_vista_sin_controlador).
 * O si se accede a ?option=com_audatoria sin especificar una vista,
 * y el default_view está configurado en este controlador.
 */
class DisplayController extends BaseController
{
    /**
     * La vista predeterminada para este controlador.
     * Si se accede al componente sin una vista específica (ej. solo ?option=com_audatoria),
     * se intentará cargar esta vista.
     * Podrías querer que sea 'timelines' o un 'dashboard'.
     *
     * @var string
     */
    protected $default_view = 'timelines'; // O el nombre de tu vista de panel de control si tienes una.

    /**
     * Método para mostrar una vista.
     * @param boolean $cachable   Si es true, la salida de la vista será cacheada.
     * @param array   $urlparams  Array de parámetros seguros de URL.
     * @return Joomla\CMS\MVC\Controller\BaseController|static
     */
    public function display($cachable = false, $urlparams = [])
    {
        // El BaseController se encarga de cargar la vista basada en el parámetro 'view' de la petición.
        // Si 'view' no está, usará $this->default_view.
        // Si tu menú principal del componente en audatoria.xml ya define &view=timelines,
        // entonces TimelinesController será llamado directamente, y este DisplayController
        // solo actuaría como fallback para vistas no cubiertas por controladores específicos.
        return parent::display($cachable, $urlparams);
    }
}