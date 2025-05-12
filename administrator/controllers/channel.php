<?php
// Ubicación: administrator/controllers/channel.php
namespace Joomla\Component\Audatoria\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session; // Para el token CSRF

class ChannelController extends FormController
{
    protected $view_item = 'channel';
    protected $view_list = 'channels';

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->registerTask('import', 'importVideos'); // Registrar la tarea personalizada
    }
    
    public function save($key = null, $urlVar = 'id')
    {
        $this->checkToken();
        $app   = Factory::getApplication();
        $model = $this->getModel('Channel');
        $table = $model->getTable();
        $data  = $this->input->post->get('jform', [], 'array');
        $form  = $model->getForm($data, false);

        if (!$form) {
            $app->enqueueMessage($model->getError(), 'error');
            return false;
        }

        $validData = $model->validate($form, $data);

        if ($validData === false) {
            $errors = $model->getErrors();
            foreach ($errors as $error) {
                $app->enqueueMessage($error instanceof \Exception ? $error->getMessage() : $error, 'warning');
            }
            $app->setUserState('com_audatoria.edit.' . $this->view_item . '.data', $data);
            $this->setRedirect(
                Route::_(
                    'index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($validData[$table->getKeyName()] ?? null, $urlVar),
                    false
                )
            );
            return false;
        }
        
        if (!$model->save($validData)) {
            $app->setUserState('com_audatoria.edit.' . $this->view_item . '.data', $data);
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
        $channelId = $model->getState($this->context . '.id');

        switch ($this->getTask()) {
            case 'apply':
                $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . '&layout=edit&id=' . (int) $channelId, false));
                break;
            case 'save2new':
                $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . '&layout=edit', false));
                break;
            default:
                $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend(), false));
                break;
        }
        
        $app->setUserState('com_audatoria.edit.' . $this->view_item . '.data', null);
        return true;
    }

    /**
     * Método para iniciar la importación de videos de un canal.
     * Esta es una tarea personalizada.
     */
    public function importVideos()
    {
        // Verificar token CSRF, especialmente si la acción modifica datos o consume muchos recursos.
        // Session::checkToken('get') para GET, o Session::checkToken() para POST.
        // La URL en la plantilla `default.php` de channels incluye el token, así que Session::checkToken('get') está bien.
        Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));

        $app = Factory::getApplication();
        $cid = $this->input->get('cid', [], 'array'); // Obtener IDs de los canales seleccionados
        
        if (empty($cid)) {
             $id = $this->input->getInt('id'); // Si se llama desde un enlace con un solo ID
             if ($id) {
                $cid = [$id];
             }
        }


        if (empty($cid)) {
            $app->enqueueMessage(Text::_('COM_AUDATORIA_ERROR_NO_CHANNELS_SELECTED_FOR_IMPORT'), 'error');
            $this->setRedirect(Route::_('index.php?option=com_audatoria&view=channels', false));
            return false;
        }

        $channelIds = array_map('intval', $cid);
        $successCount = 0;
        $errorMessages = [];

        // $channelModel = $this->getModel('Channel'); // Modelo singular para obtener detalles si es necesario

        foreach ($channelIds as $channelId) {
            if ($channelId <= 0) continue;

            // Aquí es donde invocarías la lógica real de importación.
            // Esto puede ser una llamada a un helper, al script CLI, o a un método del modelo.
            // Por ahora, solo un mensaje.
            
            // Ejemplo simplificado:
            // $result = $channelModel->triggerImportForChannel($channelId);
            // if ($result && isset($result['success']) && $result['success']) {
            //     $successCount++;
            // } else {
            //     $errorMessages[] = Text::sprintf('COM_AUDATORIA_IMPORT_FAILED_FOR_CHANNEL_ID', $channelId, ($result['message'] ?? 'Unknown error'));
            // }

            // Mensaje temporal mientras la lógica principal reside en el CLI
            $app->enqueueMessage(Text::sprintf('COM_AUDATORIA_CHANNEL_IMPORT_ACTION_FOR_ID', $channelId), 'message');
            Log::add(Text::sprintf('COM_AUDATORIA_CHANNEL_IMPORT_ACTION_FOR_ID', $channelId), Log::INFO, 'com_audatoria');
            
            // Lógica para invocar el script CLI
            // Esto es un ejemplo básico y puede necesitar ajustes de seguridad y rutas.
            $cliPath = JPATH_ROOT . '/cli/audatoria_youtubeimport.php'; // Asumiendo que el script se mueve a la raíz de /cli
            $phpPath = PHP_BINDIR . '/php'; // Intenta encontrar el ejecutable de PHP

            if (file_exists($cliPath) && is_executable($phpPath)) {
                // Construir el comando. Asegúrate de escapar los argumentos.
                // Podrías querer pasar el ID del sitio/aplicación si tu CLI lo necesita.
                $command = escapeshellcmd($phpPath) . ' ' . escapeshellarg($cliPath) . ' --channel_id=' . escapeshellarg((string)$channelId) . ' > /dev/null 2>&1 &';
                // El `> /dev/null 2>&1 &` es para ejecutar en segundo plano en sistemas *nix.
                // En Windows, esto sería diferente y podrías usar `pclose(popen("start /B " . $command, "r"));`
                
                // @TODO: Considerar usar Joomla\CMS\Console\SymfonyAdapter para ejecutar comandos de forma más integrada si es una opción.
                // O usar un sistema de colas si las importaciones son largas.
                
                exec($command, $output, $return_var);

                if ($return_var === 0) {
                    $app->enqueueMessage(Text::sprintf('COM_AUDATORIA_IMPORT_CLI_TRIGGERED_FOR_CHANNEL', $channelId), 'message');
                } else {
                    $app->enqueueMessage(Text::sprintf('COM_AUDATORIA_IMPORT_CLI_ERROR_FOR_CHANNEL', $channelId), 'error');
                    Log::add("Error ({$return_var}) al ejecutar script CLI para canal {$channelId}: " . implode("\n", $output), Log::ERROR, 'com_audatoria');
                }
            } else {
                 $app->enqueueMessage(Text::_('COM_AUDATORIA_ERROR_CLI_SCRIPT_NOT_FOUND_OR_PHP_NOT_EXECUTABLE'), 'error');
                 Log::add("No se pudo ejecutar el script CLI: {$cliPath} o PHP: {$phpPath} no encontrado/ejecutable.", Log::ERROR, 'com_audatoria');
            }
        }
        
        // if ($successCount > 0) {
        //     $app->enqueueMessage(Text::sprintf('COM_AUDATORIA_IMPORT_SUCCESS_COUNT', $successCount), 'message');
        // }
        // foreach ($errorMessages as $errMsg) {
        //     $app->enqueueMessage($errMsg, 'error');
        // }

        $this->setRedirect(Route::_('index.php?option=com_audatoria&view=channels', false));
        return true; // O false dependiendo de si quieres detener la cadena de ejecución
    }
}