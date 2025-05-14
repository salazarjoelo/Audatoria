<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Factory;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('formbehavior.chosen', 'select'); // Para mejorar los selects si los usas

$app = Factory::getApplication();
// $input = $app->input; // No se usa directamente aquí usualmente
?>

<form action="<?php echo Route::_('index.php?option=com_audatoria&view=timeline&layout=edit&id=' . (int) ($this->item->id ?? 0)); ?>"
      method="post" name="adminForm" id="timeline-form" class="form-validate">

    <?php if (!empty($this->sidebar)) : ?>
        <div id="j-sidebar-container" class="col-md-2">
            <?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="col-md-10">
    <?php else : ?>
        <div id="j-main-container" class="col-md-12">
    <?php endif; ?>

        <?php // Renderiza los campos comunes de título y alias si están en el form XML ?>
        <?php // Asegúrate que timeline.xml tenga los campos 'title' y 'alias' si usas esto.
              // Si no, puedes quitarlos del layout y ponerlos directamente en tu fieldset.
        // echo LayoutHelper::render('joomla.edit.title_alias', $this); 
        ?>

        <div class="main-card"> <?php // Clase para el estilo de tarjeta de Joomla 4/5 ?>
            <?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTabSetID', ['active' => 'detailsTab']); // ID único para el TabSet ?>

            <?php echo HTMLHelper::_('bootstrap.addTab', 'myTabSetID', 'detailsTab', Text::_('COM_AUDATORIA_FIELDSET_BASIC_TIMELINE_LABEL')); ?>
                <div class="row">
                    <div class="col-md-9">
                        <?php // Renderiza el fieldset 'basic' del formulario XML (timeline.xml) ?>
                        <?php echo $this->form->renderFieldset('basic'); ?>
                    </div>
                    <div class="col-md-3">
                        <?php // Puedes poner aquí los campos de publicación o metadatos si están en un fieldset diferente ?>
                        <?php // echo $this->form->renderFieldset('publish_side'); // Ejemplo ?>
                    </div>
                </div>
            <?php echo HTMLHelper::_('bootstrap.endTab'); ?>

            <?php echo HTMLHelper::_('bootstrap.addTab', 'myTabSetID', 'publishingTab', Text::_('COM_AUDATORIA_FIELDSET_PUBLISHING_LABEL')); ?>
                <div class="row">
                    <div class="col-md-9">
                        <?php echo $this->form->renderFieldset('publish'); ?>
                    </div>
                </div>
            <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
            
            <?php /* Si tienes un fieldset 'params' en tu timeline.xml
            <?php echo HTMLHelper::_('bootstrap.addTab', 'myTabSetID', 'paramsTab', Text::_('JGLOBAL_FIELDSET_PARAMS')); ?>
                <div class="row">
                    <div class="col-md-9">
                        <?php echo $this->form->renderFieldset('params'); ?>
                    </div>
                </div>
            <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
            */ ?>
            
            <?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
        </div>

        <?php // Campos ocultos estándar que AdminModel y FormController esperan ?>
        <input type="hidden" name="task" value="" /> <?php // La tarea se establece por los botones del toolbar ?>
        <input type="hidden" name="return" value="" /> <?php // Para el botón "Guardar y Cerrar" si se usa un 'return' específico ?>
        <?php echo HTMLHelper::_('form.token'); ?>
        <?php // El campo ID ya es renderizado por el formulario si está en el XML (como type="hidden")
              // o puedes añadirlo explícitamente si es necesario:
              // echo $this->form->getInput('id');
        ?>
        </div> </form>