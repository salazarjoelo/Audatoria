<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('formbehavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
?>
<div class="audatoria-component">
    <h2><?php echo $this->item->title; ?></h2>
    
    <form action="<?php echo JRoute::_('index.php?option=com_audatoria'); ?>" method="post" class="form-validate">
        <div class="mb-3">
            <label for="inputField"><?php echo JText::_('COM_AUDATORIA_FIELD_LABEL'); ?></label>
            <input type="text" name="inputField" class="form-control required" required>
        </div>
        
        <button type="submit" class="btn btn-primary">
            <?php echo JText::_('COM_AUDATORIA_SUBMIT_BUTTON'); ?>
        </button>
        
        <input type="hidden" name="task" value="form.process" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>