<?php
// Ubicación: administrator/controllers/channels.php
namespace Joomla\Component\Audatoria\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route; // Para redirecciones
use Joomla\CMS\Language\Text; // Para mensajes de idioma

class ChannelsController extends AdminController
{
    protected $view_list = 'channels';

    public function __construct($config = [])
    {
        parent::__construct($config);
        // La tarea 'import' ahora está en ChannelController (singular),
        // pero podrías tener una tarea de lote aquí si quisieras importar varios seleccionados.
        // Sin embargo, la tarea 'import' en ChannelController toma 'cid' (array de IDs)
        // que es como AdminController maneja las tareas de lote para publish, delete, etc.
        // Así que el botón "Importar" en la barra de herramientas de la lista de canales
        // que llama a `channels.import` debería funcionar.
    }

    public function getModel($name = 'Channel', $prefix = 'Joomla\\Component\\Audatoria\\Administrator\\Model', $config = ['ignore_request' => true])
    {
        if (empty($name)) {
            $name = 'Channel';
        }
        return parent::getModel($name, $prefix, $config);
    }

    // Si necesitas una tarea de importación específica para la lista (ej. un botón "Importar Todos los Habilitados")
    // public function importAllEnabled()
    // {
    //     Session::checkToken() or die(Text::_('JINVALID_TOKEN'));
    //     $app = Factory::getApplication();
    //     // Lógica para obtener todos los canales habilitados y llamar al CLI/método de importación
    //     $app->enqueueMessage('Importación de todos los canales habilitados iniciada (simulado).', 'message');
    //     $this->setRedirect(Route::_('index.php?option=com_audatoria&view=channels', false));
    // }
}