<?php
// Ubicación: administrator/views/timeline/view.html.php
namespace Joomla\Component\Audatoria\Administrator\View\Timeline;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\FormView as BaseFormView; // Usar FormView para vistas de edición
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\Component\Audatoria\Administrator\Helper\AudatoriaHelper; // Asumiendo que crearás este helper

class TimelineView extends BaseFormView
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
        $this->canDo = AudatoriaHelper::getActions('timeline', $this->item->id ?? 0); // Usar un helper para permisos

        // Validar el formulario y el ítem.
        if (empty($this->form)) {
            throw new \Exception(Text::_('COM_AUDATORIA_ERROR_FORM_NOT_LOADED'), 500);
        }
        if (empty($this->item) && $this->getLayout() !== 'edit') { // Si es nuevo, item puede estar vacío inicialmente
             // No lanzar error si es un nuevo item (id=0), pero si para editar un item no encontrado
            if (Factory::getApplication()->input->getInt('id', 0) != 0) {
                throw new \Exception(Text::_('COM_AUDATORIA_ERROR_TIMELINE_NOT_FOUND_ADMIN'), 404);
            }
        }


        // Comprobar errores.
        if (count($errors = $this->get('Errors'))) {
            throw new \Exception(implode("\n", $errors), 500);
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        Factory::getApplication()->input->set('hidemainmenu', true); // Ocultar menú principal de Joomla
        $isNew = ($this->item->id == 0);

        ToolbarHelper::title(
            Text::_($isNew ? 'COM_AUDATORIA_TIMELINE_NEW' : 'COM_AUDATORIA_TIMELINE_EDIT')
            . ($isNew || empty($this->item->title) ? '' : ': ' . $this->item->title), // Mostrar título si existe
            'stopwatch icon-audatoria-timeline' // Añade una clase CSS para un ícono personalizado
        );

        // Botones estándar de FormView (Guardar, Aplicar, Cancelar)
        // Los permisos se verifican con $this->canDo
        if ($this->canDo->get('core.edit') || ($isNew && $this->canDo->get('core.create'))) {
            ToolbarHelper::apply('timeline.apply');
            ToolbarHelper::save('timeline.save');
        }
        if ($this->canDo->get('core.create')) { // Solo mostrar si se puede crear
            ToolbarHelper::save2new('timeline.save2new');
            if (!$isNew) { // Solo mostrar si se está editando para copiar
                 ToolbarHelper::save2copy('timeline.save2copy');
            }
        }

        ToolbarHelper::cancel('timeline.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}