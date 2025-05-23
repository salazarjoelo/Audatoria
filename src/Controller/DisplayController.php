<?php
namespace Salazarjoelo\Component\Audatoria\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

class DisplayController extends BaseController
{
    /**
     * Método para mostrar una vista.
     * Este controlador se usará si no se encuentra uno más específico para la vista.
     */
    public function display($cachable = false, $urlparams = [])
    {
        // El BaseController se encarga de cargar la vista basada en el parámetro 'view'.
        return parent::display($cachable, $urlparams);
    }
}