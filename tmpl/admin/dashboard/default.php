<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('formbehavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$app = Factory::getApplication();
$document = $app->getDocument();
?>
<div class="container-fluid">
    <h2 class="mb-4"><?php echo Text::_('COM_AUDATORIA_DASHBOARD_TITLE'); ?></h2>
    
    <div class="row">
        <div class="col-md-8">
            <form action="<?php echo JRoute::_('index.php?option=com_audatoria'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
                
                <div class="card mb-4">
                    <div class="card-header">
                        <?php echo Text::_('COM_AUDATORIA_MAIN_SETTINGS'); ?>
                    </div>
                    <div class="card-body">
                        <!-- Tus campos del formulario aquí -->
                    </div>
                </div>

                <input type="hidden" name="task" value="" />
                <?php echo HTMLHelper::_('form.token'); ?>
            </form>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <?php echo Text::_('COM_AUDATORIA_SIDEBAR'); ?>
                </div>
                <div class="card-body">
                    <!-- Contenido sidebar -->
                </div>
            </div>
        </div>
    </div>
</div>
