<?php
namespace Salazarjoelo\Component\Audatoria\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory; // Para $app, $this->input
use Joomla\CMS\Router\Route; // Para redirecciones
use Joomla\CMS\Language\Text; // Para mensajes

class TimelineController extends FormController
{
    /**
     * El prefijo para los mensajes del controlador.
     * @var string
     */
    protected $text_prefix = 'COM_AUDATORIA_TIMELINE';

    /**
     * La vista para un solo ítem (formulario).
     * @var string
     */
    protected $view_item = 'timeline'; // Nombre de la carpeta View/Timeline/

    /**
     * La vista de lista a la que redirigir después de guardar/cancelar.
     * @var string
     */
    protected $view_list = 'timelines'; // Nombre de la carpeta View/Timelines/

    /**
     * Constructor.
     * @param   array  $config  An optional associative array of configuration settings.
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        // Si necesitas tareas personalizadas específicas para el formulario
        // $this->registerTask('miTareaForm', 'miMetodoForm');
    }

    /**
     * Método para obtener el modelo.
     * FormController espera el modelo singular.
     *
     * @param   string  $name    El nombre del modelo.
     * @param   string  $prefix  El prefijo para el nombre de la clase PHP.
     * @param   array   $config  Array de parámetros de configuración.
     *
     * @return  \Joomla\CMS\MVC\Model\AdminModel|false El modelo o false si ocurre un error.
     */
    public function getModel($name = 'Timeline', $prefix = 'Salazarjoelo\\Component\\Audatoria\\Administrator\\Model', $config = [])
    {
        // Asegura que ignore_request sea true para que el ID se tome del contexto o de la URL, no de jform[id]
        $config['ignore_request'] = true;
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    // El método save() de FormController base es usualmente suficiente.
    // Maneja checkToken, validación, guardado y redirección.
    // Sobrescríbelo solo si necesitas una lógica muy específica que no pueda
    // ir en los métodos del modelo (prepareTable, check, store, validate).

    // Por ejemplo, si necesitas redirigir a un lugar diferente después de guardar:
    /*
    protected function postSaveHook(\Joomla\CMS\MVC\Model\AdminModel $model, $validData = [])
    {
        parent::postSaveHook($model, $validData);

        $app = Factory::getApplication();
        $task = $this->getTask();

        if ($task === 'save' || $task === 'saveAndClose') {
            // Redirigir a un lugar específico
            // $this->setRedirect(Route::_('index.php?option=com_example&view=otroLugar', false));
        }
    }
    */
}