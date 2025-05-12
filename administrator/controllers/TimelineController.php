<?php
namespace Joomla\Component\Audatoria\Administrator\Controller; // Añadir namespace

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;

class TimelineController extends FormController // Extender FormController
{
    protected $view_item = 'timeline'; // Para la vista de edición individual
    protected $view_list = 'timelines'; // Para la vista de lista
}