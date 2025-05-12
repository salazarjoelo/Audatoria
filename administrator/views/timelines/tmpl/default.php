<?php
// Ubicación: administrator/views/timelines/tmpl/default.php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Text;

// Cargar JScripts/CSS necesarios
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect'); // Para la selección múltiple de ítems
HTMLHelper::_('formbehavior.chosen', 'select'); // Para mejorar selects en filtros

$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));
$saveOrder     = $listOrder === 'a.ordering'; // Solo activar si se ordena por 'ordering'

// Lógica para la ordenación drag-and-drop (si está habilitada)
if ($saveOrder && $this->items) // Asegurar que hay items antes de llamar a sortable
{
    $saveOrderingUrl = 'index.php?option=com_audatoria&task=timelines.saveOrderAjax&tmpl=component&' . Joomla\CMS\Session\Session::getFormToken() . '=1';
    HTMLHelper::_('sortablelist.sortable', 'timelineList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
$sortFields    = $this->getSortFields(); // Obtener campos de ordenamiento desde la vista
$assoc         = $this->state->get('filter. associação') ?? false; // Asumiendo para asociaciones multilingües
?>
<form action="<?php echo Route::_('index.php?option=com_audatoria&view=timelines'); ?>" method="post" name="adminForm" id="adminForm">
    <?php if (!empty($this->sidebar)) : ?>
        <div id="j-sidebar-container" class="span2 col-md-2">
            <?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="span10 col-md-10">
    <?php else : ?>
        <div id="j-main-container">
    <?php endif; ?>

        <?php // Mostrar filtros de búsqueda ?>
        <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this, 'options' => ['filterButton' => true]]); ?>

        <?php if (empty($this->items)) : ?>
            <div class="alert alert-no-items">
                <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
            </div>
        <?php else : ?>
            <table class="table table-striped" id="timelineList">
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
                        <th scope="col" style="min-width:85px" class="nowrap center">
							<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
						</th>
                        <th class="title">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
                        </th>
                        <th width="10%" class="nowrap center hidden-phone">
                            <?php echo HTMLHelper::_('searchtools.sort',  'JFIELD_ACCESS_LABEL', 'a.access', $listDirn, $listOrder); ?>
                        </th>
                         <th width="10%" class="nowrap center hidden-phone">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JAUTHOR', 'author_name', $listDirn, $listOrder); ?>
                        </th>
                        <th width="10%" class="nowrap center hidden-phone">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JDATE_CREATED', 'a.created_time', $listDirn, $listOrder); ?>
                        </th>
                        <?php if ($this->state->get('list.select') && strpos($this->state->get('list.select'), 'a.language') !== false) : ?>
                            <th class="nowrap center hidden-phone">
                                <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'a.language', $listDirn, $listOrder); ?>
                            </th>
                        <?php endif; ?>
                        <th width="1%" class="nowrap center hidden-phone">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($this->items as $i => $item) :
                    // Calcular permisos para este item específico
                    $item->canChange = $this->canDo->get('core.edit.state'); // Simplificado, puedes hacer lógica más fina aquí
                    $item->canEdit   = $this->canDo->get('core.edit'); // Simplificado
                    $item->canCheckin = $this->canDo->get('core.manage'); // Simplificado
                    ?>
                    <tr class="row<?php echo $i % 2; ?>" sortable-group-id="1">
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
							<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'timelines.', $item->canChange, 'cb', $item->publish_up ?? null, $item->publish_down ?? null); ?>
                            <?php // TODO: Integrar Access y Featured si es necesario ?>
						</td>
                        <td class="has-context">
                            <?php if ($item->checked_out) : ?>
                                <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'timelines.', $item->canCheckin); ?>
                            <?php endif; ?>
                            <?php if ($item->canEdit) : ?>
                                <a href="<?php echo Route::_('index.php?option=com_audatoria&task=timeline.edit&id=' . (int) $item->id); ?>">
                                    <?php echo $this->escape($item->title); ?>
                                </a>
                            <?php else : ?>
                                <?php echo $this->escape($item->title); ?>
                            <?php endif; ?>
                            <div class="small">
                                <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias));?>
                            </div>
                        </td>
                        <td class="small hidden-phone center">
                            <?php echo $this->escape($item->access_level); ?>
                        </td>
                         <td class="small hidden-phone center">
                            <?php echo $this->escape($item->author_name); ?>
                        </td>
                        <td class="small hidden-phone center">
                            <?php echo HTMLHelper::_('date', $item->created_time, Text::_('DATE_FORMAT_LC4')); ?>
                        </td>
                        <?php if ($this->state->get('list.select') && strpos($this->state->get('list.select'), 'a.language') !== false) : ?>
                        <td class="center hidden-phone">
							<?php echo $item->language === '*' ? Text::_('JALL') : ($item->language_title ?: $this->escape($item->language)); ?>
						</td>
                         <?php endif; ?>
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