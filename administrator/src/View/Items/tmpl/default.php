<?php
// Ubicación: administrator/views/items/tmpl/default.php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));
$saveOrder     = $listOrder === 'a.ordering'; // Habilitar drag-and-drop si se ordena por 'ordering'

if ($saveOrder && $this->items)
{
    $saveOrderingUrl = 'index.php?option=com_audatoria&task=items.saveOrderAjax&tmpl=component&' . Joomla\CMS\Session\Session::getFormToken() . '=1';
    HTMLHelper::_('sortablelist.sortable', 'itemList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
$sortFields    = $this->getSortFields();
?>
<form action="<?php echo Route::_('index.php?option=com_audatoria&view=items'); ?>" method="post" name="adminForm" id="adminForm">
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
            <table class="table table-striped" id="itemList">
                <thead>
                    <tr>
                        <?php if ($saveOrder) : ?>
                             <th width="1%" class="nowrap center hidden-phone">
                                <?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
                            </th>
                        <?php endif; ?>
                        <th width="1%" class="center">
                            <?php echo HTMLHelper::_('grid.checkall'); ?>
                        </th>
                        <th style="min-width:85px" class="nowrap center">
							<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
						</th>
                        <th class="title">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
                        </th>
                        <th width="15%" class="nowrap hidden-phone">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_AUDATORIA_TIMELINE', 'timeline_title', $listDirn, $listOrder); ?>
                        </th>
                         <th width="10%" class="nowrap hidden-phone">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_AUDATORIA_FIELD_START_DATE', 'a.start_date', $listDirn, $listOrder); ?>
                        </th>
                        <th width="10%" class="nowrap center hidden-phone">
                            <?php echo HTMLHelper::_('searchtools.sort',  'JFIELD_ACCESS_LABEL', 'a.access', $listDirn, $listOrder); ?>
                        </th>
                         <th width="5%" class="nowrap center hidden-phone">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'a.language', $listDirn, $listOrder); ?>
                        </th>
                        <th width="1%" class="nowrap center hidden-phone">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($this->items as $i => $item) :
                    $item->canChange = $this->canDo->get('core.edit.state');
                    $item->canEdit   = $this->canDo->get('core.edit');
                    $item->canCheckin = $this->canDo->get('core.manage');
                    ?>
                    <tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->timeline_id; ?>"> <?php // Ordenar dentro de cada timeline ?>
                        <?php if ($saveOrder) : ?>
                             <td class="order nowrap center hidden-phone">
                                <?php if ($item->canChange) :
                                    $disableClassName = '';
                                    $disabledLabel = '';
                                    if (!$saveOrder) :
                                        $disabledLabel = Text::_('JORDERINGDISABLED');
                                        $disableClassName = 'inactive tip-top hasTooltip';
                                    endif; ?>
                                    <span class="sortable-handler <?php echo $disableClassName;?>" title="<?php echo $disabledLabel;?>">
                                        <span class="icon-menu"></span>
                                    </span>
                                    <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering;?>" class="width-20 text-area-order" />
                                <?php else : ?>
                                    <span class="sortable-handler inactive">
                                        <span class="icon-menu"></span>
                                    </span>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                        <td class="center">
                            <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                        </td>
                        <td class="center">
							<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'items.', $item->canChange, 'cb'); ?>
						</td>
                        <td class="has-context">
                             <?php if ($item->checked_out) : ?>
                                <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'items.', $item->canCheckin); ?>
                            <?php endif; ?>
                            <?php if ($item->canEdit) : ?>
                                <a href="<?php echo Route::_('index.php?option=com_audatoria&task=item.edit&id=' . (int) $item->id); ?>">
                                    <?php echo $this->escape($item->title); ?>
                                </a>
                            <?php else : ?>
                                <?php echo $this->escape($item->title); ?>
                            <?php endif; ?>
                        </td>
                        <td class="small hidden-phone">
                             <?php echo $this->escape($item->timeline_title); ?> (ID: <?php echo (int) $item->timeline_id; ?>)
                        </td>
                        <td class="small hidden-phone">
                            <?php echo HTMLHelper::_('date', $item->start_date, Text::_('DATE_FORMAT_LC4')); ?>
                        </td>
                        <td class="small hidden-phone center">
                            <?php echo $this->escape($item->access_level); ?>
                        </td>
                        <td class="small center hidden-phone">
							<?php echo $item->language === '*' ? Text::_('JALL') : ($item->language_title ?: $this->escape($item->language)); ?>
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