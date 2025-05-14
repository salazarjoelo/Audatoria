<?php
namespace Salazarjoelo\Component\Audatoria\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Factory; // Para input
use Joomla\CMS\Router\Route; // Para redirecciones
// No es necesario usar 'use Joomla\CMS\Language\Text;' si usas Text::_() a través del helper o vistas

class TimelinesController extends AdminController
{
    /**
     * El prefijo para los mensajes del controlador.
     * @var string
     */
    protected $text_prefix = 'COM_AUDATORIA_TIMELINES';

    /**
     * La vista por defecto para la lista si no se especifica.
     * AdminController es también un ListController.
     * @var string
     */
    protected $view_list = 'timelines'; // Nombre de la carpeta de la Vista

    /**
     * Constructor.
     * @param array $config An optional associative array of configuration settings.
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        // Aquí puedes registrar tareas personalizadas si las tienes para la vista de lista
        // $this->registerTask('customTask', 'customMethod');
    }

    /**
     * Proxy para getModel.
     * @param  string $name    El nombre del modelo.
     * @param  string $prefix  El prefijo para el nombre de la clase PHP.
     * @param  array  $config  Array de parámetros de configuración.
     * @return \Joomla\CMS\MVC\Model\BaseDatabaseModel|false
     */
    public function getModel($name = 'Timeline', $prefix = 'Salazarjoelo\\Component\\Audatoria\\Administrator\\Model', $config = ['ignore_request' => true])
    {
        // AdminController usa el modelo singular (Timeline) para acciones de lote (publish, delete).
        // La vista de lista (Timelines) usará el modelo plural (TimelinesModel) que obtiene por su cuenta.
        return parent::getModel($name, $prefix, $config);
    }
}