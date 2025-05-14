<?php
// Ubicación: administrator/views/item/tmpl/edit.php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('formbehavior.chosen', 'select');

// Cargar el editor configurado
$editor = Joomla\CMS\Editor\Editor::getInstance(Joomla\CMS\Factory::getConfig()->get('editor'));

// Si necesitas lógica JS específica para este formulario
// Factory::getDocument()->addScriptDeclaration('...');
?>
<form action="<?php echo Route::_('index.php?option=com_audatoria&view=item&layout=edit&id=' . (int) ($this->item->id ?? 0)); ?>"
      method="post" name="adminForm" id="item-form" class="form-validate">

    <div class="main-card">
        <?php echo HTMLHelper::_('bootstrap.startTabSet', 'itemTab', ['active' => 'basic']); ?>

        <?php echo HTMLHelper::_('bootstrap.addTab', 'itemTab', 'basic', Text::_('COM_AUDATORIA_FIELDSET_BASIC_ITEM_LABEL')); ?>
            <div class="row">
                <div class="col-md-9">
                    <?php echo $this->form->renderFieldset('basic'); ?>
                </div>
                <div class="col-md-3">
                     <?php // Puedes mover campos aquí si lo deseas ?>
                </div>
            </div>
        <?php echo HTMLHelper::_('bootstrap.endTab'); ?>

        <?php echo HTMLHelper::_('bootstrap.addTab', 'itemTab', 'media', Text::_('COM_AUDATORIA_FIELDSET_MEDIA_LABEL')); ?>
            <div class="row">
                <div class="col-md-9">
                    <?php echo $this->form->renderFieldset('media'); ?>
                </div>
            </div>
        <?php echo HTMLHelper::_('bootstrap.endTab'); ?>

        <?php echo HTMLHelper::_('bootstrap.addTab', 'itemTab', 'location', Text::_('COM_AUDATORIA_FIELDSET_LOCATION_LABEL')); ?>
            <div class="row">
                <div class="col-md-9">
                    <?php echo $this->form->renderFieldset('location'); ?>
                </div>
                 <div class="col-md-3">
                    <?php // Podrías añadir un mapa aquí para seleccionar ubicación ?>
                </div>
            </div>
        <?php echo HTMLHelper::_('bootstrap.endTab'); ?>

        <?php echo HTMLHelper::_('bootstrap.addTab', 'itemTab', 'publishing', Text::_('COM_AUDATORIA_FIELDSET_PUBLISHING_LABEL')); ?>
            <div class="row">
                <div class="col-md-9">
                    <?php echo $this->form->renderFieldset('publish'); ?>
                </div>
            </div>
        <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
        
        <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'params', Text::_('JGLOBAL_FIELDSET_PARAMS')); ?>
            <div class="row">
                <div class="col-md-9">
                    <?php echo $this->form->renderFieldset('params'); ?>
                </div>
            </div>
        <?php echo HTMLHelper::_('bootstrap.endTab'); ?>

         <?php // Renderizar campos ocultos ?>
         <div class="row">
            <div class="col-12">
                <?php echo $this->form->renderField('id'); ?>
                <?php echo $this->form->renderField('asset_id'); ?>
                <?php echo $this->form->renderField('created_time'); ?>
                 <?php echo $this->form->renderField('created_user_id'); ?>
                 <?php echo $this->form->renderField('modified_time'); ?>
                 <?php echo $this->form->renderField('modified_user_id'); ?>
                 <?php echo $this->form->renderField('checked_out'); ?>
                 <?php echo $this->form->renderField('checked_out_time'); ?>
                 <?php // No mostramos 'ordering' aquí usualmente, se maneja en la lista ?>
            </div>
        </div>

        <?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
    </div>

    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>