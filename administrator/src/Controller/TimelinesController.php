<?php
namespace Salazarjoelo\Component\Audatoria\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
// Si necesitas Factory para input u otras cosas directamente aquí:
// use Joomla\CMS\Factory;

class TimelinesController extends AdminController
{
    /**
     * El prefijo para los mensajes del controlador.
     * @var string
     */
    protected $text_prefix = 'COM_AUDATORIA_TIMELINES';

    /**
     * Nombre de la vista por defecto para la lista.
     * Usado por AdminController para determinar el contexto.
     * @var string
     */
    protected $view_list = 'timelines'; // Corresponde a la carpeta View/Timelines/

    /**
     * Constructor.
     * @param array $config An optional associative array of configuration settings.
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        // No necesitas registrar tareas estándar como publish, unpublish, delete, etc.
        // AdminController las maneja si los permisos son correctos y el modelo singular se puede cargar.
    }

    /**
     * Proxy para getModel.
     * Para AdminController, el nombre del modelo por defecto para acciones de lote es el singular.
     * @param  string $name    El nombre del modelo. Default 'Timeline' para acciones sobre un item.
     * @param  string $prefix  El prefijo para el nombre de la clase PHP.
     * @param  array  $config  Array de parámetros de configuración.
     * @return \Joomla\CMS\MVC\Model\BaseDatabaseModel|false
     */
    public function getModel($name = 'Timeline', $prefix = 'Salazarjoelo\\Component\\Audatoria\\Administrator\\Model', $config = ['ignore_request' => true])
    {
        // AdminController usa el modelo singular (Timeline) para acciones de lote (publish, delete).
        // La vista de lista (HtmlView en View/Timelines/) obtendrá el modelo plural (TimelinesModel) por su cuenta.
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }
}