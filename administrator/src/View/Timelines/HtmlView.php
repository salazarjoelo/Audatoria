<?php
namespace Salazarjoelo\Component\Audatoria\Administrator\View\Timelines;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;
use Salazarjoelo\Component\Audatoria\Administrator\Helper\AudatoriaHelper;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $filterForm;
    protected $activeFilters;
    protected $canDo;
    protected $sidebar;

    public function display($tpl = null): void
    {
        $app = Factory::getApplication();

        try {
            $this->items         = $this->get('Items');
            $this->pagination    = $this->get('Pagination');
            $this->state         = $this->get('State');
            $this->filterForm    = $this->get('FilterForm');
            $this->activeFilters = $this->get('ActiveFilters');
            
            $this->canDo = AudatoriaHelper::getActions('component');

            if (count($errors = $this->get('Errors'))) {
                foreach ($errors as $error) {
                    $app->enqueueMessage($error, 'error');
                }
                 \Joomla\CMS\Log\Log::add("Errores cargando datos para la vista Timelines: " . implode(", ", $errors), \Joomla\CMS\Log\Log::WARNING, 'com_audatoria');
            }

            $this->addToolbar();
            
            $this->sidebar = LayoutHelper::render('joomla.sidebars.submenu',
                ['items' => AudatoriaHelper::getSidebarItems($app->input->getCmd('view', 'timelines'))]
            );

        } catch (\Throwable $e) {
            $app->enqueueMessage('Error crítico preparando la vista Timelines: ' . $e->getMessage(), 'error');
            \Joomla\CMS\Log\Log::add(
                'Error en Timelines HtmlView (preparación de display): ' . $e->getMessage() . "\n" . $e->getTraceAsString(),
                \Joomla\CMS\Log\Log::CRITICAL, 'com_audatoria'
            );
            if ($app->get('debug')) {
                echo "<h1>Error Preparando Vista Timelines</h1><p>" . $e->getMessage() . "</p><pre>" . $e->getTraceAsString() . "</pre>";
            }
            return; // No intentar renderizar si la preparación falla catastróficamente
        }

        try {
            parent::display($tpl);
        } catch (\Throwable $e) {
            $app->enqueueMessage('Error crítico renderizando la plantilla de Timelines: ' . $e->getMessage(), 'error');
            \Joomla\CMS\Log\Log::add(
                'Error en Timelines HtmlView (renderizando plantilla): ' . $e->getMessage() . "\n" . $e->getTraceAsString(),
                \Joomla\CMS\Log\Log::CRITICAL, 'com_audatoria'
            );
            if ($app->get('debug')) {
                echo "<h1>Error Renderizando Plantilla Timelines</h1><p>" . $e->getMessage() . "</p><pre>" . $e->getTraceAsString() . "</pre>";
            }
        }
    }

    protected function addToolbar(): void
    {
        $app    = Factory::getApplication();
        $user   = $app->getIdentity();
        $option = $app->input->getCmd('option', 'com_audatoria');

        ToolbarHelper::title(Text::_('COM_AUDATORIA_TIMELINES_HEADING'), 'list-alt icon-audatoria-timelines'); // Puedes usar 'list-alt' o tu ícono

        $canCreateTimeline = AudatoriaHelper::getActions('timeline')->get('core.create');
        if ($canCreateTimeline) {
            ToolbarHelper::addNew('timeline.add', 'JTOOLBAR_NEW');
        }
        
        if ($this->canDo->get('core.edit') || $this->canDo->get('core.edit.own')) {
            ToolbarHelper::editList('timeline.edit', 'JTOOLBAR_EDIT');
        }
        
        if ($this->canDo->get('core.edit.state')) {
            ToolbarHelper::publish('timelines.publish', 'JTOOLBAR_PUBLISH', true);
            ToolbarHelper::unpublish('timelines.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            ToolbarHelper::archive('timelines.archive', 'JTOOLBAR_ARCHIVE', true);
            ToolbarHelper::checkin('timelines.checkin', 'JTOOLBAR_CHECKIN', true);
        }
        
        if ($this->canDo->get('core.delete')) {
            ToolbarHelper::deleteList(Text::_('COM_AUDATORIA_CONFIRM_DELETE_TIMELINES_MSG'), 'timelines.delete', 'JTOOLBAR_DELETE');
        }
        
        if ($user->authorise('core.admin', $option) || $user->authorise('core.options', $option)) {
            ToolbarHelper::preferences($option);
        }
    }

    protected function getSortFields(): array
    {
        return [
            'a.ordering'    => Text::_('JGRID_HEADING_ORDERING'),
            'a.state'       => Text::_('JSTATUS'),
            'a.title'       => Text::_('JGLOBAL_TITLE'),
            'access_level'  => Text::_('JFIELD_ACCESS_LABEL'),
            'author_name'   => Text::_('JAUTHOR'),
            'a.created_time'=> Text::_('JDATE_CREATED'),
            'a.language'    => Text::_('JGRID_HEADING_LANGUAGE'),
            'a.id'          => Text::_('JGRID_HEADING_ID')
        ];
    }
}