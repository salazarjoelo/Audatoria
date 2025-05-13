<?php
// Ubicación: administrator/controllers/channel.php
namespace Salazarjoelo\Component\Audatoria\Administrator\Controller; // NAMESPACE CORREGIDO

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;

class ChannelController extends FormController
{
    protected $view_item = 'channel';
    protected $view_list = 'channels';

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->registerTask('importVideos', 'importVideos'); // Tarea renombrada para coincidir con el toolbar
    }
    
    public function save($key = null, $urlVar = 'id')
    {
        $this->checkToken();
        $app   = Factory::getApplication();
        $model = $this->getModel('Channel', 'Salazarjoelo\Component\Audatoria\Administrator\Model'); // Namespace del modelo
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
            default: // save
                $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend(), false));
                break;
        }
        
        $app->setUserState('com_audatoria.edit.' . $this->view_item . '.data', null);
        return true;
    }

    public function importVideos()
    {
        Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));

        $app = Factory::getApplication();
        $cid = $this->input->get('cid', [], 'array'); 
        
        if (empty($cid)) {
             $id = $this->input->getInt('id'); 
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
        
        foreach ($channelIds as $channelId) {
            if ($channelId <= 0) continue;

            $cliPath = JPATH_ROOT . '/cli/aud