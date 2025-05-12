<?php
// Ubicación: administrator/controllers/timelines.php
namespace Joomla\Component\Audatoria\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController; // AdminController maneja publish, unpublish, delete, etc.
use Joomla\CMS\Factory;

class TimelinesController extends AdminController
{
    /**
     * El prefijo a usar al determinar la vista desde la cual redirigir.
     *
     * @var    string
     * @since  1.6
     */
    protected $view_list = 'timelines'; // Vista por defecto es la lista de timelines

    /**
     * Constructor.
     *
     * @param   array  $config  Un array asociativo de parámetros de configuración.
     *
     * @see     \Joomla\CMS\MVC\Controller\BaseController
     * @since   1.6
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        // Registrar tareas específicas de la lista si es necesario
        // $this->registerTask('miTareaDeLista', 'miMetodoDeLista');
    }
    
    /**
     * Método para obtener el modelo.
     *
     * @param   string  $name    El nombre del modelo. Opcional.
     * @param   string  $prefix  El prefijo para el nombre de clase. Opcional.
     * @param   array   $config  Array de configuración para el modelo. Opcional.
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel|\Joomla\CMS\MVC\Model\ListModel|false  Objeto del modelo o false si no se puede crear.
     */
    public function getModel($name = 'Timeline', $prefix = 'Joomla\\Component\\Audatoria\\Administrator\\Model', $config = ['ignore_request' => true])
    {
        // Para acciones de lote como publish, delete, necesitamos el modelo singular 'Timeline'
        // que sabe cómo manejar una tabla individual.
        // Para la vista de lista, se usa el modelo 'Timelines'.
        // AdminController ya está configurado para usar el modelo singular (ej. 'TimelineModel')
        // para sus tareas estándar de lote.
        if (empty($name)) {
            // El nombre del controlador es 'Timelines', así que por defecto busca 'TimelinesModel'
            // Para acciones de lote, queremos 'TimelineModel' (singular)
            $name = 'Timeline';
        }
        return parent::getModel($name, $prefix, $config);
    }
}