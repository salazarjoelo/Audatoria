<?php
// Ubicación: administrator/views/channels/view.html.php
namespace Salazarjoelo\Component\Audatoria\Administrator\View\Channels; // NAMESPACE CORREGIDO

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\ListView as BaseListView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Factory;
use Salazarjoelo\Component\Audatoria\Administrator\Helper\AudatoriaHelper; // Namespace del Helper CORREGIDO

class ChannelsView extends BaseListView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $filterForm;
    protected $activeFilters;
    protected $canDo;

    public function display($tpl = null): void
    {
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->canDo         = AudatoriaHelper::getActions('com_audatoria'); // O 'channel.list' si es más granular

        if (count($errors = $this->get('Errors'))) {
            \Joomla\CMS\Log\Log::add(implode("\n", $errors), \Joomla\CMS\Log\Log::ERROR, 'com_audatoria');
            throw new \Exception(implode("\n", $errors), 500);
        }

        $this->addToolbar();
        
        // $this->sidebar = LayoutHelper::render('joomla.sidebars.submenu', ['items' => AudatoriaHelper::getSidebarItems('channels')]);
        // Se renderiza en la plantilla tmpl/default.php

        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        $user = Factory::getApplication()->getIdentity();
        // Usar un ícono que represente canales o YouTube
        ToolbarHelper::title(Text::_('COM_AUDATORIA_CHANNELS_HEADING'), 'youtube icon-audatoria-channels');

        // Permiso para crear canales
        if (AudatoriaHelper::getActions('channel')->get('core.create')) {
            ToolbarHelper::addNew('channel.add');
        }
        // Permiso para editar canales (la lista lleva a la vista de edición individual)
        if (AudatoriaHelper::getActions('channel')->get('core.edit')) { 
            ToolbarHelper::editList('channel.edit');
        }
        // Permiso para cambiar el estado (enable/disable)
        if (AudatoriaHelper::getActions('channel')->get('core.edit.state')) {
             ToolbarHelper::custom('channels.enable', 'publish', 'publish', 'COM_AUDATORIA_TOOLBAR_ENABLE_IMPORT', true);
             ToolbarHelper::custom('channels.disable', 'unpublish', 'unpublish', 'COM_AUDATORIA_TOOLBAR_DISABLE_IMPORT', true);
        }
        // Permiso personalizado para importar
        if (AudatoriaHelper::getActions('channel')->get('channel.import')) { 
             ToolbarHelper::custom('channels.importVideos', 'cloud-upload', 'cloud-upload', 'COM_AUDATORIA_CHANNELS_IMPORT_VIDEOS_SELECTED', true);
        }
        // Permiso para eliminar canales
        if (AudatoriaHelper::getActions('channel')->get('core.delete')) {
            ToolbarHelper::deleteList(Text::_('COM_AUDATORIA_CONFIRM_DELETE_CHANNELS_MSG'), 'channels.delete', 'JTOOLBAR_DELETE');
        }
        if ($user->authorise('core.admin', 'com_audatoria') || $user->authorise('core.options', 'com_audatoria')) {
            ToolbarHelper::preferences('com_audatoria');
        }
    }

    protected function getSortFields(): array
    {
        return [
            'a.title' => Text::_('JGLOBAL_TITLE'),
            'a.channel_id' => Text::_('COM_AUDATORIA_FIELD_CHANNEL_ID_LABEL'),
            'timeline_title' => Text::_('COM_AUDATORIA_TIMELINE'),
            'a.state' => Text::_('COM_AUDATORIA_FIELD_ENABLED_LABEL_COLUMN'), 
            'a.last_checked' => Text::_('COM_AUDATORIA_FIELD_LAST_CHECKED'),
            'a.id' => Text::_('JGRID_HEADING_ID')
        ];
    }
}