<?php
// Ubicación: administrator/views/item/view.html.php
namespace Joomla\Component\Audatoria\Administrator\View\Item;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\FormView as BaseFormView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\Component\Audatoria\Administrator\Helper\AudatoriaHelper;

class ItemView extends BaseFormView // Renombrar la clase
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
        $this->canDo = AudatoriaHelper::getActions('item', $this->item->id ?? 0);

        if (empty($this->form)) {
             throw new \Exception(Text::_('COM_AUDATORIA_ERROR_FORM_NOT_LOADED'), 500);
        }
        if (empty($this->item) && Factory::getApplication()->input->getInt('id', 0) != 0) {
             throw new \Exception(Text::_('COM_AUDATORIA_ERROR_ITEM_NOT_FOUND_ADMIN'), 404);
        }

        if (count($errors = $this->get('Errors'))) {
            throw new \Exception(implode("\n", $errors), 500);
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        Factory::getApplication()->input->set('hidemainmenu', true);
        $isNew = ($this->item->id == 0);

        ToolbarHelper::title(
            Text::_($isNew ? 'COM_AUDATORIA_ITEM_NEW' : 'COM_AUDATORIA_ITEM_EDIT')
             . ($isNew || empty($this->item->title) ? '' : ': ' . $this->item->title),
            'file-add icon-audatoria-item'
        );

        if ($this->canDo->get('core.edit') || ($isNew && $this->canDo->get('core.create'))) {
            ToolbarHelper::apply('item.apply');
            ToolbarHelper::save('item.save');
        }
        if ($this->canDo->get('core.create')) {
            ToolbarHelper::save2new('item.save2new');
             // No hay save2copy por defecto en FormController base, tendrías que implementarlo si lo necesitas
             // if (!$isNew) ToolbarHelper::save2copy('item.save2copy');
        }

        ToolbarHelper::cancel('item.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}