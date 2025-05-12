<?php
// Ubicación: administrator/views/channels/view.html.php
namespace Joomla\Component\Audatoria\Administrator\View\Channels;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\ListView as BaseListView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Factory;
use Joomla\Component\Audatoria\Administrator\Helper\AudatoriaHelper;

class ChannelsView extends BaseListView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $filterForm;
    protected $activeFilters;
    protected $sidebar;
    protected $canDo;

    public function display($tpl = null): void
    {
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->canDo         = AudatoriaHelper::getActions('component'); // O 'channel' para permisos de lista

        if (count($errors = $this->get('Errors'))) {
            throw new \Exception(implode("\n", $errors), 500);
        }

        $this->addToolbar();
        $this->sidebar = LayoutHelper::render('joomla.sidebars.submenu', ['items' => AudatoriaHelper::getSidebarItems('channels')]);
        
        if ($this->filterForm) {
            $this->filterForm->prepare('filter_timeline_id', 'SELECT id AS value, title AS text FROM #__audatoria_timelines WHERE state = 1 ORDER BY title ASC');
        }

        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        $user = Factory::getApplication()->getIdentity();
        ToolbarHelper::title(Text::_('COM_AUDATORIA_CHANNELS_HEADING'), 'list icon-audatoria-channels');

        if ($this->canDo->get('core.create')) {
            ToolbarHelper::addNew('channel.add');
        }
        if ($this->canDo->get('core.edit')) { // Asumiendo que la edición de un channel se hace en su vista individual
            ToolbarHelper::editList('channel.edit');
        }
        if ($this->canDo->get('core.edit.state')) { // Para habilitar/deshabilitar canales
             // Tareas personalizadas si 'state' de canal no es publish/unpublish estándar
             ToolbarHelper::custom('channels.enable', 'publish', 'publish', 'COM_AUDATORIA_TOOLBAR_ENABLE_IMPORT', true);
             ToolbarHelper::custom('channels.disable', 'unpublish', 'unpublish', 'COM_AUDATORIA_TOOLBAR_DISABLE_IMPORT', true);
        }
        if (AudatoriaHelper::getActions('channel')->get('channel.import')) { // Permiso personalizado
             ToolbarHelper::custom('channels.importVideos', 'cloud-upload', 'cloud-upload', 'COM_AUDATORIA_CHANNELS_IMPORT_VIDEOS_SELECTED', true);
        }
        if ($this->canDo->get('core.delete')) {
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
            'a.state' => Text::_('COM_AUDATORIA_FIELD_ENABLED_LABEL_COLUMN'), // Para 'Habilitado'
            'a.last_checked' => Text::_('COM_AUDATORIA_FIELD_LAST_CHECKED'),
            'a.id' => Text::_('JGRID_HEADING_ID')
        ];
    }
}