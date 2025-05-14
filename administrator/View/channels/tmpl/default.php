<?php
// Ubicación: administrator/views/channels/tmpl/default.php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session; // Para token en enlaces de acción

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));
$sortFields    = $this->getSortFields();
?>
<form action="<?php echo Route::_('index.php?option=com_audatoria&view=channels'); ?>" method="post" name="adminForm" id="adminForm">
    <?php if (!empty($this->sidebar)) : ?>
        <div id="j-sidebar-container" class="span2 col-md-2">
            <?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="span10 col-md-10">
    <?php else : ?>
        <div id="j-main-container">
    <?php endif; ?>

        <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this, 'options' => ['filterButton' => true]]); ?>

        <?php if (empty($this->items)) : ?>
            <div class="alert alert-no-items">
                 <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
            </div>
        <?php else : ?>
            <table class="table table-striped" id="channelList">
                <thead>
                    <tr>
                        <th width="1%" class="center">
                            <?php echo HTMLHelper::_('grid.checkall'); ?>
                        </th>
                         <th style="min-width:85px" class="nowrap center">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
                        </th>
                        <th class="title">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
                        </th>
                         <th width="20%" class="nowrap hidden-phone">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_AUDATORIA_FIELD_CHANNEL_ID_LABEL', 'a.channel_id', $listDirn, $listOrder); ?>
                        </th>
                        <th width="20%" class="nowrap hidden-phone">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_AUDATORIA_TIMELINE', 'timeline_title', $listDirn, $listOrder); ?>
                        </th>
                         <th width="15%" class="nowrap hidden-phone">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_AUDATORIA_FIELD_LAST_CHECKED', 'a.last_checked', $listDirn, $listOrder); ?>
                        </th>
                        <th width="1%" class="nowrap center hidden-phone">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($this->items as $i => $item) :
                     // Asumiendo permisos básicos aquí, ajusta según tu helper si es necesario
                    $canEdit   = $this->canDo->get('core.edit');
                    $canChange = $this->canDo->get('core.edit.state');
                    $canImport = $this->canDo->get('channel.import'); // Permiso personalizado
                    ?>
                    <tr class="row<?php echo $i % 2; ?>">
                        <td class="center">
                            <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                        </td>
                         <td class="center">
                            <?php // Usar state y las tareas enable/disable personalizadas ?>
                            <?php echo HTMLHelper::_('jgrid.state', [1 => 'COM_AUDATORIA_ENABLED', 0 => 'COM_AUDATORIA_DISABLED'], $item->state, $i, 'channels.', $canChange); ?>
                         </td>
                        <td class="has-context">
                            <?php // TODO: Checked out handling if needed ?>
                            <?php if ($canEdit) : ?>
                                <a href="<?php echo Route::_('index.php?option=com_audatoria&task=channel.edit&id=' . (int) $item->id); ?>">
                                    <?php echo $this->escape($item->title ?: Text::_('COM_AUDATORIA_CHANNEL_UNTITLED')); ?>
                                </a>
                            <?php else : ?>
                                <?php echo $this->escape($item->title ?: Text::_('COM_AUDATORIA_CHANNEL_UNTITLED')); ?>
                            <?php endif; ?>
                             <div class="small">
                                ID: <?php echo $this->escape($item->channel_id); ?>
                            </div>
                        </td>
                        <td class="small hidden-phone">
                             <?php echo $this->escape($item->channel_id); ?>
                        </td>
                        <td class="small hidden-phone">
                             <?php echo $this->escape($item->timeline_title); ?> (ID: <?php echo (int) $item->timeline_id; ?>)
                        </td>
                        <td class="small hidden-phone">
                            <?php echo $item->last_checked && $item->last_checked !== $this->getDbo()->getNullDate() ? HTMLHelper::_('date', $item->last_checked, Text::_('DATE_FORMAT_LC4')) : Text::_('COM_AUDATORIA_NEVER_CHECKED'); ?>
                        </td>
                        <td class="center hidden-phone">
                            <?php echo (int) $item->id; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?php echo $this->pagination->getListFooter(); ?>

        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>