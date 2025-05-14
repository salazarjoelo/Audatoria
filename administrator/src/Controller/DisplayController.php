<?php
namespace Salazarjoelo\Component\Audatoria\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Controlador de visualización genérico para el backend de Audatoria.
 * Este controlador se usará si no se encuentra un controlador más específico
 * para la vista solicitada (ej. si solo se pasa ?option=com_audatoria&view=alguna_vista).
 */
class DisplayController extends BaseController
{
    /**
     * Método para mostrar una vista.
     * @param boolean $cachable   Si es true, la salida de la vista será cacheada.
     * @param array   $urlparams  Array de parámetros seguros de URL.
     * @return static
     */
    public function display($cachable = false, $urlparams = [])
    {
        // El BaseController se encarga de cargar la vista basada en el parámetro 'view'.
        return parent::display($cachable, $urlparams);
    }
}