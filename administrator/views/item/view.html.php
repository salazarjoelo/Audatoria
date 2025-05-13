<?php
// Ubicación: administrator/views/item/view.html.php
namespace Salazarjoelo\Component\Audatoria\Administrator\View\Item; // NAMESPACE CORREGIDO

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\FormView as BaseFormView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Salazarjoelo\Component\Audatoria\Administrator\Helper\AudatoriaHelper; // Namespace del Helper CORREGIDO

class ItemView extends BaseFormView
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

        if (empty($this->item) || !is_object($this->item)) {
            $this->item = new \stdClass();
            $this->item->id = $this->state->get('item.id', 0);
        }
        if (!isset($this->item->id)) {
             $this->item->id = 0;
        }
        
        $this->canDo = AudatoriaHelper::getActions('item', (int) $this->item->id);

        if (empty($this->form)) {
             throw new \Exception(Text::_('COM_AUDATORIA_ERROR_FORM_NOT_LOADED'), 500);
        }
        if ($this->item->id == 0 && Factory::getApplication()->input->getInt('id', 0) != 0) {
             throw new \Exception(Text::_('COM_AUDATORIA_ERROR_ITEM_NOT_FOUND_ADMIN'), 404);
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
        $title = $isNew ? Text::_('COM_AUDATORIA_ITEM_NEW') : Text::_('COM_AUDATORIA_ITEM_EDIT');

        if (!$isNew && !empty($this->item->title)) {
             $title .= ': ' . $this->item->title;
        }

        ToolbarHelper::title($title, 'file-alt icon-audatoria-item'); // 'file-alt' o 'file-plus' para nuevo

        if ($this->canDo->get('core.edit') || ($isNew && $this->canDo->get('core.create'))) {
            ToolbarHelper::apply('item.apply');
            ToolbarHelper::save('item.save');
        }
        if ($this->canDo->get('core.create')) {
            ToolbarHelper::save2new('item.save2new');
             // save2copy no es estándar en FormController, necesitaría implementación en el controlador.
             // if (!$isNew && $this->canDo->get('core.create')) {
             //     ToolbarHelper::save2copy('item.save2copy');
             // }
        }

        ToolbarHelper::cancel('item.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}