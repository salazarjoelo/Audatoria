<?php
// Ubicación: administrator/views/channel/view.html.php
namespace Salazarjoelo\Component\Audatoria\Administrator\View\Channel; // NAMESPACE CORREGIDO

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\FormView as BaseFormView; // CAMBIADO a FormView
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Salazarjoelo\Component\Audatoria\Administrator\Helper\AudatoriaHelper; // Namespace del Helper CORREGIDO

class ChannelView extends BaseFormView // Nombre de clase y herencia CORREGIDOS
{
    protected $form;
    protected $item;
    protected $state;
    protected $canDo;

    public function display($tpl = null): void
    {
        $this->form  = $this->get('Form');
        $this->item  = $this->get('Item');
        $this->state = $this->get('State');

        if (empty($this->item) || !is_object($this->item)) {
            $this->item = new \stdClass();
            $this->item->id = $this->state->get('channel.id', 0);
        }
         if (!isset($this->item->id)) {
             $this->item->id = 0;
        }

        $this->canDo = AudatoriaHelper::getActions('channel', (int) $this->item->id);

        if (empty($this->form)) {
            throw new \Exception(Text::_('COM_AUDATORIA_ERROR_FORM_NOT_LOADED'), 500);
        }
        if ($this->item->id == 0 && Factory::getApplication()->input->getInt('id', 0) != 0) {
             throw new \Exception(Text::_('COM_AUDATORIA_ERROR_CHANNEL_NOT_FOUND_ADMIN'), 404);
        }

        if (count($errors = $this->get('Errors'))) {
            \Joomla\CMS\Log\Log::add(implode("\n", $errors), \Joomla\CMS\Log\Log::ERROR, 'com_audatoria');
            throw new \Exception(implode("\n", $errors), 500);
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        Factory::getApplication()->input->set('hidemainmenu', true);
        $isNew = ($this->item->id == 0);
        $title = $isNew ? Text::_('COM_AUDATORIA_CHANNEL_NEW') : Text::_('COM_AUDATORIA_CHANNEL_EDIT');

        if (!$isNew && !empty($this->item->title)) {
             $title .= ': ' . $this->item->title;
        } elseif (!$isNew && empty($this->item->title) && !empty($this->item->channel_id)) {
             $title .= ': ' . $this->item->channel_id; // Mostrar ID de canal si no hay título
        }


        ToolbarHelper::title($title, 'podcast icon-audatoria-channel');

        if ($this->canDo->get('core.edit') || ($isNew && $this->canDo->get('core.create'))) {
            ToolbarHelper::apply('channel.apply');
            ToolbarHelper::save('channel.save');
        }
        if ($this->canDo->get('core.create')) {
            ToolbarHelper::save2new('channel.save2new');
        }

        if (!$isNew && $this->canDo->get('channel.import')) { 
            ToolbarHelper::custom('channel.importVideos&id='.(int)$this->item->id, 'cloud-upload', 'cloud-upload', 'COM_AUDATORIA_CHANNELS_IMPORT_VIDEOS_SINGLE', false);
        }

        ToolbarHelper::cancel('channel.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}