<?php
// Ubicación: administrator/views/channel/tmpl/edit.php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('formbehavior.chosen', 'select');
?>
<form action="<?php echo Route::_('index.php?option=com_audatoria&view=channel&layout=edit&id=' . (int) ($this->item->id ?? 0)); ?>"
      method="post" name="adminForm" id="channel-form" class="form-validate">

    <div class="main-card">
        <?php echo HTMLHelper::_('bootstrap.startTabSet', 'channelTab', ['active' => 'basic']); ?>

        <?php echo HTMLHelper::_('bootstrap.addTab', 'channelTab', 'basic', Text::_('COM_AUDATORIA_FIELDSET_BASIC_CHANNEL_LABEL')); ?>
            <div class="row">
                <div class="col-md-9">
                    <?php echo $this->form->renderFieldset('basic'); ?>
                </div>
                 <div class="col-md-3">
                    <?php // Muestra campos adicionales o información aquí si lo necesitas ?>
                    <div class="control-group">
                         <div class="control-label">
                            <?php echo Text::_('COM_AUDATORIA_FIELD_LAST_CHECKED'); ?>
                         </div>
                         <div class="controls">
                            <span class="readonly">
                                <?php echo $this->item->last_checked && $this->item->last_checked !== $this->getDbo()->getNullDate() ? HTMLHelper::_('date', $this->item->last_checked, Text::_('DATE_FORMAT_LC4')) : Text::_('COM_AUDATORIA_NEVER_CHECKED'); ?>
                            </span>
                        </div>
                    </div>
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
                 <?php echo $this->form->renderField('modified_time'); ?>
                 <?php echo $this->form->renderField('checked_out'); ?>
                 <?php echo $this->form->renderField('checked_out_time'); ?>
            </div>
        </div>


        <?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
    </div>

    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>