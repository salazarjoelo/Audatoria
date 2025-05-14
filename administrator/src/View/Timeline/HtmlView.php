<?php
namespace Salazarjoelo\Component\Audatoria\Administrator\View\Timeline;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView; // Correcto para J5, FormView hereda de HtmlView
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper; // Para el sidebar
use Salazarjoelo\Component\Audatoria\Administrator\Helper\AudatoriaHelper;

// Para vistas de formulario, es común heredar de FormView
use Joomla\CMS\MVC\View\FormView as BaseFormView;

class HtmlView extends BaseFormView // Heredar de FormView
{
    protected $form;
    protected $item;
    protected $state;
    protected $canDo;
    protected $sidebar;

    public function display($tpl = null): void
    {
        $app = Factory::getApplication();

        try {
            $this->form  = $this->get('Form');
            $this->item  = $this->get('Item');
            $this->state = $this->get('State');

            if (empty($this->item)) { // Puede ser un objeto stdClass vacío si es nuevo
                $this->item = new \stdClass();
                $this->item->id = 0; // Asegurar que id está presente
            }
            // Asegurar que id es un entero
            $this->item->id = (int) ($this->item->id ?? 0);
            
            // Obtener permisos para el ítem actual o para crear uno nuevo
            $this->canDo = AudatoriaHelper::getActions('timeline', $this->item->id);

            if (empty($this->form)) {
                throw new \RuntimeException(Text::_('COM_AUDATORIA_ERROR_FORM_NOT_LOADED'), 500);
            }
            
            // Si se intenta editar un ítem que no existe (ID en URL pero no se carga el ítem)
            if ($this->item->id == 0 && $app->input->getInt('id', 0) != 0) {
                 throw new \RuntimeException(Text::_('COM_AUDATORIA_ERROR_TIMELINE_NOT_FOUND_ADMIN'), 404);
            }

            if (count($errors = $this->get('Errors'))) {
                foreach ($errors as $error) {
                    $app->enqueueMessage($error, 'error');
                }
                 \Joomla\CMS\Log\Log::add("Errores cargando datos para el formulario Timeline: " . implode(", ", $errors), \Joomla\CMS\Log\Log::WARNING, 'com_audatoria');
            }

            $this->addToolbar();

            // Preparar el sidebar
            $this->sidebar = LayoutHelper::render('joomla.sidebars.submenu', 
                ['items' => AudatoriaHelper::getSidebarItems($app->input->getCmd('view', 'timeline'))]
            );

        } catch (\Throwable $e) {
            $app->enqueueMessage('Error crítico preparando el formulario Timeline: ' . $e->getMessage(), 'error');
            \Joomla\CMS\Log\Log::add(
                'Error en Timeline FormView (preparación de display): ' . $e->getMessage() . "\n" . $e->getTraceAsString(),
                \Joomla\CMS\Log\Log::CRITICAL, 'com_audatoria'
            );
            if ($app->get('debug')) {
                echo "<h1>Error Preparando Formulario Timeline</h1><p>" . $e->getMessage() . "</p><pre>" . $e->getTraceAsString() . "</pre>";
            }
            return;
        }

        try {
            parent::display($tpl);
        } catch (\Throwable $e) {
            $app->enqueueMessage('Error crítico renderizando el formulario Timeline: ' . $e->getMessage(), 'error');
            \Joomla\CMS\Log\Log::add(
                'Error en Timeline FormView (renderizando plantilla): ' . $e->getMessage() . "\n" . $e->getTraceAsString(),
                \Joomla\CMS\Log\Log::CRITICAL, 'com_audatoria'
            );
            if ($app->get('debug')) {
                echo "<h1>Error Renderizando Formulario Timeline</h1><p>" . $e->getMessage() . "</p><pre>" . $e->getTraceAsString() . "</pre>";
            }
        }
    }

    protected function addToolbar(): void
    {
        $app = Factory::getApplication();
        $app->input->set('hidemainmenu', true); // Ocultar el menú principal de Joomla
        
        $isNew = ($this->item->id == 0);
        $title = $isNew ? Text::_('COM_AUDATORIA_TIMELINE_NEW') : Text::_('COM_AUDATORIA_TIMELINE_EDIT');
        
        if (!$isNew && !empty($this->item->title)) {
             $title .= ': ' . $this->escape($this->item->title);
        }

        ToolbarHelper::title($title, 'stopwatch icon-audatoria-timeline');

        // Permisos $this->canDo son para ESTE timeline específico (o para crear si es nuevo)
        if ($this->canDo->get('core.edit') || ($isNew && $this->canDo->get('core.create'))) {
            ToolbarHelper::apply('timeline.apply');
            ToolbarHelper::save('timeline.save');
        }
        // Solo mostrar "Guardar y Nuevo" si se tiene permiso para crear
        if ($this->canDo->get('core.create')) { 
            ToolbarHelper::save2new('timeline.save2new');
        }
        // Solo mostrar "Guardar como Copia" si se está editando un existente y se tiene permiso para crear
        if (!$isNew && $this->canDo->get('core.create')) {
            ToolbarHelper::save2copy('timeline.save2copy');
        }

        ToolbarHelper::cancel('timeline.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}