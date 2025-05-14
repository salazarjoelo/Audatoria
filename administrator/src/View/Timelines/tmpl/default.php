<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_audatoria
 * @view        Timelines
 * @年月日      2024-05-13 // Actualiza esta fecha si es necesario
 *
 * @copyright   Copyright (C) 2024 Joel Salazar. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session; // Para el token en saveOrderAjax
use Joomla\CMS\Factory;       // Para obtener el usuario para permisos granulares

// Cargar JScripts/CSS necesarios para el backend
HTMLHelper::_('bootstrap.tooltip'); // Para tooltips de Bootstrap
HTMLHelper::_('behavior.multiselect'); // Para la selección múltiple de ítems en la lista
HTMLHelper::_('formbehavior.chosen', 'select'); // Para mejorar los selects en los filtros (Search Tools)

$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));
$saveOrder     = $listOrder === 'a.ordering'; // Solo activar drag-n-drop si se ordena por 'a.ordering'

// Lógica para la ordenación drag-and-drop
if ($saveOrder && !empty($this->items)) // Asegurar que hay items antes de intentar habilitar sortable
{
    // El token de sesión es importante para la seguridad de la tarea AJAX
    $saveOrderingUrl = Route::_('index.php?option=com_audatoria&task=timelines.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1');
    HTMLHelper::_('sortablelist.sortable', 'timelineList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$user = Factory::getApplication()->getIdentity(); // Para verificar permisos por ítem
?>

<form action="<?php echo Route::_('index.php?option=com_audatoria&view=timelines'); ?>" method="post" name="adminForm" id="adminForm">
    <?php if (!empty($this->sidebar)) : ?>
        <div id="j-sidebar-container" class="col-md-2"> <?php // Clases de Bootstrap 5, ajusta si es necesario ?>
            <?php echo $this->sidebar; // El sidebar se prepara en la clase HtmlView ?>
        </div>
        <div id="j-main-container" class="col-md-10">
    <?php else : ?>
        <div id="j-main-container" class="col-md-12">
    <?php endif; ?>

        <?php // Mostrar filtros de búsqueda (Search Tools) ?>
        <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this, 'options' => ['filterButton' => true]]); ?>

        <?php if (empty($this->items)) : ?>
            <div class="alert alert-no-items">
                <?php echo Text::_($this->get('EmptyMessage', 'JGLOBAL_NO_MATCHING_RESULTS')); // Usa el método getEmptyMessage del modelo ?>
            </div>
        <?php else : ?>
            <table class="table table-striped table-hover" id="timelineList">
                <thead class="table-dark"> <?php // O la clase de cabecera que prefieras ?>
                    <tr>
                        <?php if ($saveOrder) : ?>
                            <th scope="col" style="width:1%" class="text-center d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
                            </th>
                        <?php endif; ?>
                        <th scope="col" style="width:1%" class="text-center">
                            <?php echo HTMLHelper::_('grid.checkall'); ?>
                        </th>
                        <th scope="col" style="min-width:100px" class="text-center">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" style="width:15%" class="text-center d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JFIELD_ACCESS_LABEL', 'access_level', $listDirn, $listOrder); // 'access_level' es el alias del join ?>
                        </th>
                        <th scope="col" style="width:15%" class="text-center d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JAUTHOR', 'author_name', $listDirn, $listOrder); // 'author_name' es el alias del join ?>
                        </th>
                        <th scope="col" style="width:15%" class="text-center d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JDATE_CREATED', 'a.created_time', $listDirn, $listOrder); ?>
                        </th>
                        <?php /* Para el campo de idioma, si lo tienes en tu $this->state->get('list.select') y modelo
                        <?php if ($this->state->get('list.select') && stripos($this->state->get('list.select'), 'a.language') !== false) : ?>
                            <th scope="col" style="width:5%" class="text-center d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'a.language', $listDirn, $listOrder); ?>
                            </th>
                        <?php endif; ?>
                        */ ?>
                        <th scope="col" style="width:5%" class="text-center d-none d-md-table-cell"> <?php // Reducido width para ID ?>
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($this->items as $i => $item) :
                    // Permisos específicos del ítem usando el helper.
                    // 'timeline' es el nombre de la sección en access.xml para los ítems de tipo Timeline.
                    $itemCanDo            = AudatoriaHelper::getActions('timeline', (int) $item->id);
                    $canEditItem        = $itemCanDo->get('core.edit');
                    $canEditOwnItem     = $itemCanDo->get('core.edit.own') && ($item->created_user_id == $user->id);
                    $canChangeItemState = $itemCanDo->get('core.edit.state');
                    // El permiso de checkin suele ser más general a nivel de componente o tipo.
                    $canCheckinItem     = $user->authorise('core.manage', 'com_audatoria') || $user->authorise('core.manage', 'com_audatoria.timeline');

                    ?>
                    <tr class="row<?php echo $i % 2; ?>" item-id="<?php echo $item->id; ?>">
                         <?php if ($saveOrder) : ?>
                            <td class="text-center d-none d-md-table-cell order">
                                <?php
                                // Solo mostrar el manejador de orden si el ordenamiento actual es por 'a.ordering'
                                // y el usuario tiene permiso para cambiar el estado (o un permiso específico de ordenamiento si lo defines).
                                if ($listOrder === 'a.ordering' && $canChangeItemState) : ?>
                                    <span class="sortable-handler inactive" title="<?php echo Text::_('JGRID_HEADING_ORDERING'); ?>">
                                        <i class="icon-menu"></i>
                                    </span>
                                    <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" />
                                <?php else : ?>
                                    <span class="sortable-handler inactive" title="<?php echo Text::_('JORDERINGDISABLED'); ?>">
                                        <i class="icon-menu"></i>
                                    </span>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                        <td class="text-center">
                            <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                        </td>
                        <td class="text-center">
                            <?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'timelines.', $canChangeItemState, 'cb', $item->publish_up ?? null, $item->publish_down ?? null); ?>
                        </td>
                        <td class="has-context">
                            <?php if ($item->checked_out && ($item->checked_out != $user->id)) : ?>
                                <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, HTMLHelper::_('date', $item->checked_out_time, Text::_('DATE_FORMAT_LC5')), 'timelines.', $canCheckinItem); ?>
                            <?php elseif ($canEditItem || $canEditOwnItem) : ?>
                                <a href="<?php echo Route::_('index.php?option=com_audatoria&task=timeline.edit&id=' . (int) $item->id); ?>">
                                    <?php echo $this->escape($item->title); ?>
                                </a>
                            <?php else : ?>
                                <?php echo $this->escape($item->title); ?>
                            <?php endif; ?>
                            <?php if (!empty($item->alias)) : ?>
                            <div class="small d-block">
                                <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias));?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td class="text-center d-none d-md-table-cell">
                            <?php echo $this->escape($item->access_level); ?>
                        </td>
                        <td class="text-center d-none d-md-table-cell">
                            <?php echo $this->escape($item->author_name); ?>
                        </td>
                        <td class="text-center d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('date', $item->created_time, Text::_('DATE_FORMAT_LC4')); ?>
                        </td>
                        <?php /*
                        <?php if (isset($item->language_title)) : // Asegúrate que language_title se seleccione en el modelo si usas esto ?>
                        <td class="text-center d-none d-md-table-cell">
                            <?php echo $item->language === '*' ? Text::_('JALL') : $this->escape($item->language_title); ?>
                        </td>
                        <?php endif; ?>
                        */ ?>
                        <td class="text-center d-none d-md-table-cell">
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