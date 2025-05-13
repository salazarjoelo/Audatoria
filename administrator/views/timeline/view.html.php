<?php
// Ubicación: administrator/views/timeline/view.html.php
namespace Salazarjoelo\Component\Audatoria\Administrator\View\Timeline; // NAMESPACE CORREGIDO

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\FormView as BaseFormView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Salazarjoelo\Component\Audatoria\AudatoriaHelper; // Namespace del Helper CORREGIDO

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
        
        // Asegurar que $this->item sea un objeto, incluso si es nuevo, para evitar errores en $this->item->id
        if (empty($this->item) || !is_object($this->item)) {
            $this->item = new \stdClass(); // O tu objeto de tabla vacío
            $this->item->id = $this->state->get('timeline.id', 0); // Tomar ID del estado si existe
        }
        // Si aún no tiene id, asegurar que sea 0
        if (!isset($this->item->id)) {
             $this->item->id = 0;
        }

        $this->canDo = AudatoriaHelper::getActions('timeline', (int) $this->item->id);

        if (empty($this->form)) {
            throw new \Exception(Text::_('COM_AUDATORIA_ERROR_FORM_NOT_LOADED'), 500);
        }
        
        // No lanzar error si es un nuevo item (id=0), pero si para editar un item no encontrado
        if ($this->item->id == 0 && Factory::getApplication()->input->getInt('id', 0) != 0) {
             // Intentando editar un item que no existe
             throw new \Exception(Text::_('COM_AUDATORIA_ERROR_TIMELINE_NOT_FOUND_ADMIN'), 404);
        }

        if (count($errors = $this->get('Errors'))) {
            \Joomla\CMS\Log\Log::add(implode("\n", $errors), \Joomla\CMS\Log\Log::ERROR, 'com_audatoria');
            throw new \Exception(implode("\n", $errors), 500);
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        Factory::getApplication()->input->set('hidemainmenu', true);
        $isNew = ($this->item->id == 0);
        $title = $isNew ? Text::_('COM_AUDATORIA_TIMELINE_NEW') : Text::_('COM_AUDATORIA_TIMELINE_EDIT');
        
        // Añadir título del ítem si se está editando y tiene título
         if (!$isNew && !empty($this->item->title)) {
             $title .= ': ' . $this->item->title;
         }

        ToolbarHelper::title($title, 'stopwatch icon-audatoria-timeline');

        if ($this->canDo->get('core.edit') || ($isNew && $this->canDo->get('core.create'))) {
            ToolbarHelper::apply('timeline.apply');
            ToolbarHelper::save('timeline.save');
        }
        if ($this->canDo->get('core.create')) { 
            ToolbarHelper::save2new('timeline.save2new');
            if (!$isNew && $this->canDo->get('core.create')) { // Save as copy solo si se puede crear
                 ToolbarHelper::save2copy('timeline.save2copy');
            }
        }

        ToolbarHelper::cancel('timeline.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}