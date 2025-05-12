<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
// HTMLHelper::_('formbehavior.chosen', 'select'); // Si usas selects que quieras mejorar

$app = Joomla\CMS\Factory::getApplication();
$input = $app->input;
?>
<form action="<?php echo Route::_('index.php?option=com_audatoria&layout=edit&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="adminForm" class="form-validate">

    <?php echo LayoutHelper::render('joomla.edit.title_alias', $this); // Para título y alias si lo tuvieras ?>

    <div class="form-horizontal">
        <?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', ['active' => 'details']); ?>

        <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'details', Text::_('COM_AUDATORIA_FIELDSET_BASIC_TIMELINE_LABEL')); ?>
            <div class="row-fluid">
                <div class="span9">
                    <?php echo $this->form->renderFieldset('basic'); ?>
                </div>
                 <div class="span3">
                    <?php // echo $this->form->renderFieldset('publish'); // Si moviste los campos de publicación a un fieldset 'publish' ?>
                </div>
            </div>
        <?php echo HTMLHelper::_('bootstrap.endTab'); ?>

        <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'publishing', Text::_('COM_AUDATORIA_FIELDSET_PUBLISHING_LABEL')); ?>
            <div class="row-fluid">
                <div class="span9">
                     <?php echo $this->form->renderFieldset('publish'); ?>
                </div>
            </div>
        <?php echo HTMLHelper::_('bootstrap.endTab'); ?>

        <?php // Puedes añadir más pestañas aquí para otros fieldsets ?>

        <?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
    </div>

    <input type="hidden" name="task" value="" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>