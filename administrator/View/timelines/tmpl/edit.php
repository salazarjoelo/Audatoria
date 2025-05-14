<?php
// Ubicación: administrator/views/timeline/tmpl/edit.php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Factory;

// Cargar JScripts/CSS necesarios
HTMLHelper::_('behavior.formvalidator'); // Validación de formulario
HTMLHelper::_('behavior.keepalive'); // Mantener sesión viva
HTMLHelper::_('formbehavior.chosen', 'select'); // Mejorar selects

$app = Factory::getApplication();
$input = $app->input;
?>
<form action="<?php echo Route::_('index.php?option=com_audatoria&view=timeline&layout=edit&id=' . (int) ($this->item->id ?? 0)); ?>"
      method="post" name="adminForm" id="timeline-form" class="form-validate"> <?php // Cambiar id del form a algo específico ?>

    <?php // Renderiza los campos comunes de título y alias si están en el form XML ?>
    <?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

    <div class="main-card"> <?php // Clase para el estilo de tarjeta de Joomla 4/5 ?>
        <?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', ['active' => 'details']); ?>

        <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'details', Text::_('COM_AUDATORIA_FIELDSET_BASIC_TIMELINE_LABEL')); ?>
            <div class="row">
                <div class="col-md-9">
                    <?php // Renderiza el fieldset 'basic' del formulario XML ?>
                    <?php echo $this->form->renderFieldset('basic'); ?>
                </div>
            </div>
        <?php echo HTMLHelper::_('bootstrap.endTab'); ?>

        <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'publishing', Text::_('COM_AUDATORIA_FIELDSET_PUBLISHING_LABEL')); ?>
            <div class="row">
                <div class="col-md-9">
                    <?php // Renderiza el fieldset 'publish' del formulario XML ?>
                    <?php echo $this->form->renderFieldset('publish'); ?>
                </div>
            </div>
        <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
        
        <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'params', Text::_('JGLOBAL_FIELDSET_PARAMS')); ?>
            <div class="row">
                <div class="col-md-9">
                    <?php // Renderiza el fieldset 'params' si existe en el XML ?>
                    <?php echo $this->form->renderFieldset('params'); ?>
                </div>
            </div>
        <?php echo HTMLHelper::_('bootstrap.endTab'); ?>

        <?php // Puedes añadir más pestañas y fieldsets aquí si los necesitas ?>
        
        <?php // Renderizar campos ocultos ?>
         <div class="row">
            <div class="col-12">
                <?php echo $this->form->renderField('id'); ?>
                <?php echo $this->form->renderField('asset_id'); // Si manejas assets ?>
                 <?php echo $this->form->renderField('created_time'); ?>
                 <?php echo $this->form->renderField('created_user_id'); ?>
                 <?php echo $this->form->renderField('modified_time'); ?>
                 <?php echo $this->form->renderField('modified_user_id'); ?>
                 <?php echo $this->form->renderField('checked_out'); ?>
                 <?php echo $this->form->renderField('checked_out_time'); ?>
                 <?php echo $this->form->renderField('ordering'); ?>
            </div>
        </div>


        <?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
    </div>

    <input type="hidden" name="task" value="" /> <?php // La tarea se establece por los botones del toolbar ?>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>