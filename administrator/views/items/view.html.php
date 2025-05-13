<?php
// Ubicación: administrator/views/items/view.html.php
namespace Salazarjoelo\Component\Audatoria\Administrator\View\Items; // NAMESPACE CORREGIDO

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\ListView as BaseListView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Factory;
use Salazarjoelo\Component\Audatoria\Administrator\Helper\AudatoriaHelper; // Namespace del Helper CORREGIDO

class ItemsView extends BaseListView
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
        $this->canDo         = AudatoriaHelper::getActions('com_audatoria');

        if (count($errors = $this->get('Errors'))) {
            \Joomla\CMS\Log\Log::add(implode("\n", $errors), \Joomla\CMS\Log\Log::ERROR, 'com_audatoria');
            throw new \Exception(implode("\n", $errors), 500);
        }

        $this->addToolbar();
        
        // Para el filtro de timeline en Search Tools
        // Esto asume que 'filter_timeline_id' es un campo en tu filter_items.xml de tipo 'sql'
        // y el modelo 'ItemsModel' (lista) lo maneja en populateState.
        // Si el campo no se popula automáticamente, puedes hacerlo aquí:
        /*
        if ($this->filterForm && $field = $this->filterForm->getField('timeline_id', 'filter')) {
             // Esta forma de rellenar dinámicamente un campo SQL es más compleja en J5.
             // Es mejor que el XML del filtro ya tenga la query o que el modelo prepare el campo.
             // $field->setOption('query', 'SELECT id AS value, title AS text FROM #__audatoria_timelines WHERE state = 1 ORDER BY title ASC');
        }
        */

        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        $user = Factory::getApplication()->getIdentity();
        ToolbarHelper::title(Text::_('COM_AUDATORIA_ITEMS_HEADING'), 'file-alt icon-audatoria-items');

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
            'timeline_title' => Text::_('COM_AUDATORIA_TIMELINE'), // Alias de la tabla unida
            'a.start_date' => Text::_('COM_AUDATORIA_FIELD_START_DATE'),
            'a.access' => Text::_('JFIELD_ACCESS_LABEL'),
            'author_name' => Text::_('JAUTHOR'), // Alias de la tabla unida
            'a.created_time' => Text::_('JDATE_CREATED'),
            'a.language' => Text::_('JGRID_HEADING_LANGUAGE'),
            'a.id' => Text::_('JGRID_HEADING_ID')
        ];
    }
}