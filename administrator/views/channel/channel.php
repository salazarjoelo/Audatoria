<?php
// Ubicación: administrator/views/channel/view.html.php
namespace Joomla\Component\Audatoria\Administrator\View\Channel;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\FormView as BaseFormView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\Component\Audatoria\Administrator\Helper\AudatoriaHelper;

class ChannelView extends BaseFormView // Renombrar la clase
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
        $this->canDo = AudatoriaHelper::getActions('channel', $this->item->id ?? 0);

        if (empty($this->form)) {
            throw new \Exception(Text::_('COM_AUDATORIA_ERROR_FORM_NOT_LOADED'), 500);
        }
         if (empty($this->item) && Factory::getApplication()->input->getInt('id', 0) != 0) {
             throw new \Exception(Text::_('COM_AUDATORIA_ERROR_CHANNEL_NOT_FOUND_ADMIN'), 404);
        }

        if (count($errors = $this->get('Errors'))) {
            throw new \Exception(implode("\n", $errors), 500);
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        Factory::getApplication()->input->set('hidemainmenu', true);
        $isNew = ($this->item->id == 0);

        ToolbarHelper::title(
            Text::_($isNew ? 'COM_AUDATORIA_CHANNEL_NEW' : 'COM_AUDATORIA_CHANNEL_EDIT')
             . ($isNew || empty($this->item->title) ? '' : ': ' . $this->item->title),
            'podcast icon-audatoria-channel'
        );

        if ($this->canDo->get('core.edit') || ($isNew && $this->canDo->get('core.create'))) {
            ToolbarHelper::apply('channel.apply');
            ToolbarHelper::save('channel.save');
        }
        if ($this->canDo->get('core.create')) {
            ToolbarHelper::save2new('channel.save2new');
        }

        // Botón de importación individual si se está editando un canal existente y tiene permiso
        if (!$isNew && $this->canDo->get('channel.import')) { // channel.import es un permiso personalizado de access.xml
            ToolbarHelper::custom('channel.importVideos', 'cloud-upload', 'cloud-upload', 'COM_AUDATORIA_CHANNELS_IMPORT_VIDEOS_SINGLE', false);
        }


        ToolbarHelper::cancel('channel.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}