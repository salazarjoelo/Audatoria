<?php
// Ubicación: administrator/controllers/timeline.php
namespace Joomla\Component\Audatoria\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

class TimelineController extends FormController
{
    /**
     * El prefijo a usar al determinar la vista desde la cual redirigir.
     *
     * @var    string
     * @since  1.6
     */
    protected $view_item = 'timeline'; // Vista para el formulario de un solo ítem

    /**
     * El prefijo a usar al determinar la vista de lista desde la cual redirigir.
     *
     * @var    string
     * @since  1.6
     */
    protected $view_list = 'timelines'; // Vista para la lista de ítems

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

        // Registrar tareas adicionales aquí si es necesario.
        // $this->registerTask('mipropiatarea', 'miMetodo');
    }
    
    /**
     * Método para guardar un registro.
     *
     * @param   string  $key     El nombre de la variable POST que contiene la clave primaria.
     * @param   string  $urlVar  El nombre de la variable de URL que contendrá el ID del registro.
     *
     * @return  boolean  True en éxito, false en error.
     *
     * @since   1.6
     */
    public function save($key = null, $urlVar = 'id')
    {
        // Verificar el token CSRF
        $this->checkToken();

        $app   = Factory::getApplication();
        $model = $this->getModel('Timeline'); // Especificar el nombre del modelo
        $table = $model->getTable();
        $data  = $this->input->post->get('jform', [], 'array');
        $form  = $model->getForm($data, false);

        if (!$form) {
            $app->enqueueMessage($model->getError(), 'error');
            return false;
        }

        // Validar los datos del formulario.
        $validData = $model->validate($form, $data);

        if ($validData === false) {
            // Recuperar los errores del modelo.
            $errors = $model->getErrors();

            foreach ($errors as $error) {
                if ($error instanceof \Exception) {
                    $app->enqueueMessage($error->getMessage(), 'warning');
                } else {
                    $app->enqueueMessage($error, 'warning');
                }
            }

            // Guardar los datos en la sesión.
            $app->setUserState('com_audatoria.edit.' . $this->view_item . '.data', $data);

            // Redirigir de nuevo al formulario de edición.
            $this->setRedirect(
                Route::_(
                    'index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($validData[$table->getKeyName()] ?? null, $urlVar),
                    false
                )
            );

            return false;
        }
        
        // Intentar guardar los datos.
        if (!$model->save($validData)) {
            // Guardar los datos en la sesión.
            $app->setUserState('com_audatoria.edit.' . $this->view_item . '.data', $data);

            // Redirigir de nuevo al formulario de edición.
            $this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
            $this->setMessage($this->getError(), 'error');
            $this->setRedirect(
                Route::_(
                    'index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($validData[$table->getKeyName()] ?? null, $urlVar),
                    false
                )
            );
            return false;
        }

        $this->setMessage(Text::_('COM_AUDATORIA_MSG_SAVE_SUCCESS'));

        // Redirigir según la tarea.
        switch ($this->getTask()) {
            case 'apply':
                $id = $model->getState($this->context . '.id');
                $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . '&layout=edit&id=' . (int) $id, false));
                break;

            case 'save2new':
                $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . '&layout=edit', false));
                break;
            
            case 'save2copy':
                 $id = $model->getState($this->context . '.id');
                 // $model->copy() debería haber seteado el nuevo ID en el estado
                 $newItemId = $model->getState($this->context . '.new_id', $id); // Asumiendo que el modelo de copia setea 'new_id'
                 $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . '&layout=edit&id=' . (int) $newItemId, false));
                 break;

            default: // save
                $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend(), false));
                break;
        }
        
        // Limpiar los datos de la sesión después de guardar.
        $app->setUserState('com_audatoria.edit.' . $this->view_item . '.data', null);

        return true;
    }
}