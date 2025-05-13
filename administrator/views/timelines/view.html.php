<?php
// Ubicación: administrator/views/timelines/view.html.php
namespace Salazarjoelo\Component\Audatoria\Administrator\View\Timelines;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\ListView as BaseListView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper; // Para renderizar layouts como el sidebar
use Joomla\CMS\Factory;
use Salazarjoelo\Component\Audatoria\AudatoriaHelper; // Namespace del Helper CORREGIDO

class TimelinesView extends BaseListView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $filterForm;
    protected $activeFilters;
    protected $canDo;
    // El sidebar se renderiza directamente en la plantilla (tmpl) en J4/J5

    public function display($tpl = null): void
    {
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->canDo         = AudatoriaHelper::getActions('com_audatoria'); // Permisos a nivel de componente para acciones de lista

        // Comprobar errores.
        if (count($errors = $this->get('Errors'))) {
            \Joomla\CMS\Log\Log::add(implode("\n", $errors), \Joomla\CMS\Log\Log::ERROR, 'com_audatoria');
            throw new \Exception(implode("\n", $errors), 500);
        }

        $this->addToolbar();
        
        // Renderizar el sidebar para pasarlo a la plantilla, o renderizarlo directamente en la plantilla
        // $this->sidebar = LayoutHelper::render('joomla.sidebars.submenu', ['items' => AudatoriaHelper::getSidebarItems('timelines')]);
        // Es más común en J5 que la plantilla tmpl/default.php renderice el sidebar.

        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        $user = Factory::getApplication()->getIdentity();
        ToolbarHelper::title(Text::_('COM_AUDATORIA_TIMELINES_HEADING'), 'list icon-audatoria-timelines');

        if ($this->canDo->get('core.create')) {
            ToolbarHelper::addNew('timeline.add');
        }
        if ($this->canDo->get('core.edit') || $this->canDo->get('core.edit.own')) {
            ToolbarHelper::editList('timeline.edit');
        }
        if ($this->canDo->get('core.edit.state')) {
            ToolbarHelper::publish('timelines.publish', 'JTOOLBAR_PUBLISH', true);
            ToolbarHelper::unpublish('timelines.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            ToolbarHelper::archive('timelines.archive');
            ToolbarHelper::checkin('timelines.checkin');
        }
        if ($this->canDo->get('core.delete')) {
            ToolbarHelper::deleteList(Text::_('COM_AUDATORIA_CONFIRM_DELETE_TIMELINES_MSG'), 'timelines.delete', 'JTOOLBAR_DELETE');
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
            'a.access' => Text::_('JFIELD_ACCESS_LABEL'),
            'a.created_time' => Text::_('JDATE_CREATED'),
            'author_name' => Text::_('JAUTHOR'),
            'a.language' => Text::_('JGRID_HEADING_LANGUAGE'),
            'a.id' => Text::_('JGRID_HEADING_ID')
        ];
    }
}