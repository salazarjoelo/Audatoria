<?php
// Ubicación: administrator/views/items/view.html.php
namespace Joomla\Component\Audatoria\Administrator\View\Items;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\ListView as BaseListView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Factory;
use Joomla\Component\Audatoria\Administrator\Helper\AudatoriaHelper;

class ItemsView extends BaseListView
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
        $this->canDo         = AudatoriaHelper::getActions('component'); // O 'item' si los permisos son más granulares para la lista

        if (count($errors = $this->get('Errors'))) {
            throw new \Exception(implode("\n", $errors), 500);
        }

        $this->addToolbar();
        $this->sidebar = LayoutHelper::render('joomla.sidebars.submenu', ['items' => AudatoriaHelper::getSidebarItems('items')]);
        
        // Cargar el campo de filtro de timeline para las Search Tools
        if ($this->filterForm) {
            $this->filterForm->prepare('filter_timeline_id', 'SELECT id AS value, title AS text FROM #__audatoria_timelines WHERE state = 1 ORDER BY title ASC');
        }


        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        $user = Factory::getApplication()->getIdentity();
        ToolbarHelper::title(Text::_('COM_AUDATORIA_ITEMS_HEADING'), 'list icon-audatoria-items');

        if ($this->canDo->get('core.create')) {
            ToolbarHelper::addNew('item.add');
        }
        if ($this->canDo->get('core.edit') || $this->canDo->get('core.edit.own')) {
            ToolbarHelper::editList('item.edit');
        }
        if ($this->canDo->get('core.edit.state')) {
            ToolbarHelper::publish('items.publish', 'JTOOLBAR_PUBLISH', true);
            ToolbarHelper::unpublish('items.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            ToolbarHelper::archive('items.archive');
            ToolbarHelper::checkin('items.checkin');
        }
        if ($this->canDo->get('core.delete')) {
            ToolbarHelper::deleteList(Text::_('COM_AUDATORIA_CONFIRM_DELETE_ITEMS_MSG'), 'items.delete', 'JTOOLBAR_DELETE');
        }
        if ($user->authorise('core.admin', 'com_audatoria') || $user->authorise('core.options', 'com_audatoria')) {
            ToolbarHelper::preferences('com_audatoria');
        }
    }

    protected function getSortFields(): array
    {
        return [
            'a.ordering' => Text::_('JGRID_HEADING_ORDERING'),
            'a.state' => Text::_('JSTATUS'),
            'a.title' => Text::_('JGLOBAL_TITLE'),
            'timeline_title' => Text::_('COM_AUDATORIA_TIMELINE'),
            'a.start_date' => Text::_('COM_AUDATORIA_FIELD_START_DATE'),
            'a.access' => Text::_('JFIELD_ACCESS_LABEL'),
            'author_name' => Text::_('JAUTHOR'),
            'a.created_time' => Text::_('JDATE_CREATED'),
            'a.language' => Text::_('JGRID_HEADING_LANGUAGE'),
            'a.id' => Text::_('JGRID_HEADING_ID')
        ];
    }
}