<?php
namespace Salazarjoelo\Component\Audatoria\Administrator\View\Timelines; // Namespace Correcto

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView; // O ListView si prefieres
use Joomla\CMS\MVC\View\ListView as BaseListView; // Mejor para vistas de lista
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;
use Salazarjoelo\Component\Audatoria\Administrator\Helper\AudatoriaHelper; // Namespace del Helper Correcto

class HtmlView extends BaseListView // Cambiado a BaseListView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $filterForm;
    protected $activeFilters;
    protected $canDo;
    public $sidebar; // Hacerlo público para que la plantilla pueda accederlo

    public function display($tpl = null): void
    {
        $app = Factory::getApplication();

        try {
            $this->items         = $this->get('Items');
            $this->pagination    = $this->get('Pagination');
            $this->state         = $this->get('State'); // Contiene filtros, ordenación, etc.
            $this->filterForm    = $this->get('FilterForm');
            $this->activeFilters = $this->get('ActiveFilters');

            // Obtener permisos a nivel de componente para las acciones generales de la lista
            $this->canDo = AudatoriaHelper::getActions('com_audatoria'); // O simplemente AudatoriaHelper::getActions() si el default es com_audatoria

            if (count($errors = $this->get('Errors'))) {
                foreach ($errors as $error) {
                    $app->enqueueMessage($error, 'error');
                }
                \Joomla\CMS\Log\Log::add("Errores cargando datos para la vista Timelines: " . implode(", ", $errors), \Joomla\CMS\Log\Log::WARNING, 'com_audatoria');
            }

            $this->addToolbar(); // Añadir botones de la barra de herramientas

            // Generar el sidebar/submenú
            // El segundo parámetro de getSidebarItems es la vista activa
            $this->sidebar = LayoutHelper::render('joomla.sidebars.submenu',
                ['items' => AudatoriaHelper::getSidebarItems($app->input->getCmd('view', 'timelines'))]
            );

        } catch (\Throwable $e) { // Capturar cualquier error durante la preparación
            $app->enqueueMessage('Error crítico preparando la vista Timelines: ' . $e->getMessage(), 'error');
            \Joomla\CMS\Log\Log::add(
                'Error en Timelines HtmlView (preparación de display): ' . $e->getMessage() . "\n" . $e->getTraceAsString(),
                \Joomla\CMS\Log\Log::CRITICAL, 'com_audatoria'
            );
            if ($app->get('debug')) {
                echo "<h1>Error Preparando Vista Timelines</h1><p>" . $e->getMessage() . "</p><pre>" . $e->getTraceAsString() . "</pre>";
            }
            return; // No intentar renderizar si la preparación falla
        }

        // Llamar al método display de la clase padre para renderizar la plantilla
        try {
            parent::display($tpl);
        } catch (\Throwable $e) { // Capturar errores durante la renderización de la plantilla
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
        $option = $app->input->getCmd('option', 'com_audatoria'); // O $this->option

        // Título de la vista
        ToolbarHelper::title(Text::_('COM_AUDATORIA_TIMELINES_HEADING'), 'list-alt icon-audatoria-timelines'); // Puedes usar un icono tuyo

        // Permisos para crear
        // Para la acción 'core.create' sobre 'timeline' (el tipo de ítem, no el componente general)
        $canCreateTimeline = AudatoriaHelper::getActions('timeline')->get('core.create');
        if ($canCreateTimeline) {
            ToolbarHelper::addNew('timeline.add', 'JTOOLBAR_NEW'); // Tarea: timeline.add
        }

        // Permisos para editar (usa los permisos a nivel de componente para la lista)
        if ($this->canDo->get('core.edit') || $this->canDo->get('core.edit.own')) {
            ToolbarHelper::editList('timeline.edit', 'JTOOLBAR_EDIT'); // Tarea: timeline.edit
        }

        // Permisos para cambiar estado (usa los permisos a nivel de componente para la lista)
        if ($this->canDo->get('core.edit.state')) {
            ToolbarHelper::publish('timelines.publish', 'JTOOLBAR_PUBLISH', true);
            ToolbarHelper::unpublish('timelines.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            ToolbarHelper::archive('timelines.archive', 'JTOOLBAR_ARCHIVE', true); // Asegúrate de tener un modelo que maneje 'archive'
            ToolbarHelper::checkin('timelines.checkin', 'JTOOLBAR_CHECKIN', true); // Para desbloquear items
        }

        // Permisos para eliminar (usa los permisos a nivel de componente para la lista)
        if ($this->canDo->get('core.delete')) {
            ToolbarHelper::deleteList(Text::_('COM_AUDATORIA_CONFIRM_DELETE_TIMELINES_MSG'), 'timelines.delete', 'JTOOLBAR_DELETE');
        }

        // Opciones del componente
        if ($user->authorise('core.admin', $option) || $user->authorise('core.options', $option)) {
            ToolbarHelper::preferences($option);
        }
    }

    // getSortFields es heredado de ListView y usado por la plantilla de SearchTools
    // Si no lo defines, searchtools podría no mostrar las opciones de ordenamiento.
    // protected function getSortFields(): array
    // {
    //     return [
    //         'a.ordering'    => Text::_('JGRID_HEADING_ORDERING'),
    //         'a.state'       => Text::_('JSTATUS'),
    //         'a.title'       => Text::_('JGLOBAL_TITLE'),
    //         'access_level'  => Text::_('JFIELD_ACCESS_LABEL'), // Asume que 'access_level' es un alias en tu query
    //         'author_name'   => Text::_('JAUTHOR'), // Asume que 'author_name' es un alias en tu query
    //         'a.created_time'=> Text::_('JDATE_CREATED'),
    //         'a.language'    => Text::_('JGRID_HEADING_LANGUAGE'),
    //         'a.id'          => Text::_('JGRID_HEADING_ID')
    //     ];
    // }
}