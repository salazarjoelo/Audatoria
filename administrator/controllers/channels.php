<?php
// Ubicación: administrator/controllers/channels.php
namespace Salazarjoelo\Component\Audatoria\Administrator\Controller; // NAMESPACE CORREGIDO

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
// No necesitas Factory, Route, Text aquí si no los usas directamente en este archivo

class ChannelsController extends AdminController
{
    protected $view_list = 'channels';

    public function __construct($config = [])
    {
        parent::__construct($config);
        // Tareas personalizadas para habilitar/deshabilitar canales ya que 'state' no es publicación estándar
        $this->registerTask('enable', 'enable');
        $this->registerTask('disable', 'disable');
    }

    public function getModel($name = 'Channel', $prefix = 'Salazarjoelo\Component\Audatoria\Administrator\Model', $config = ['ignore_request' => true]) // Namespace del modelo CORREGIDO
    {
        // Para acciones de lote como enable/disable, AdminController usa el modelo singular.
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Custom task to enable channels.
     *
     * @return void
     */
    public function enable(): void
    {
        $this->checkToken();
        $cid = $this->input->get('cid', [], 'array');
        \Joomla\Utilities\ArrayHelper::toInteger($cid);

        if (empty($cid)) {
            \Joomla\CMS\Log\Log::add(Text::_('JLIB_HTML_NO_ITEMS_SELECTED'), \Joomla\CMS\Log\Log::WARNING, 'jerror');
        } else {
            /** @var \Salazarjoelo\Component\Audatoria\Administrator\Model\ChannelModel $model */
            $model = $this->getModel();
            if (!$model->enable($cid)) {
                $this->setMessage($model->getError(), 'error');
            } else {
                $this->setMessage(Text::plural('COM_AUDATORIA_N_CHANNELS_ENABLED', count($cid)));
            }
        }

        $this->setRedirect(\Joomla\CMS\Router\Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
    }

    /**
     * Custom task to disable channels.
     *
     * @return void
     */
    public function disable(): void
    {
        $this->checkToken();
        $cid = $this->input->get('cid', [], 'array');
        \Joomla\Utilities\ArrayHelper::toInteger($cid);

        if (empty($cid)) {
            \Joomla\CMS\Log\Log::add(Text::_('JLIB_HTML_NO_ITEMS_SELECTED'), \Joomla\CMS\Log\Log::WARNING, 'jerror');
        } else {
            /** @var \Salazarjoelo\Component\Audatoria\Administrator\Model\ChannelModel $model */
            $model = $this->getModel();
            if (!$model->disable($cid)) {
                $this->setMessage($model->getError(), 'error');
            } else {
                $this->setMessage(Text::plural('COM_AUDATORIA_N_CHANNELS_DISABLED', count($cid)));
            }
        }
        $this->setRedirect(\Joomla\CMS\Router\Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
    }
}