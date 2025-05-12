<?php
// Ubicación: administrator/src/Helper/AudatoriaHelper.php
namespace Joomla\Component\Audatoria\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Component\ComponentHelper;

class AudatoriaHelper
{
    /**
     * Obtiene las acciones permitidas para una sección del componente.
     *
     * @param   string  $section  La sección (ej. 'component', 'timeline', 'item', 'channel').
     * @param   int     $id       El ID del ítem específico (para permisos de ítem).
     *
     * @return  \Joomla\CMS\Object\CMSObject  Un objeto con las acciones permitidas.
     */
    public static function getActions(string $section = 'component', int $id = 0): \Joomla\CMS\Object\CMSObject
    {
        $user      = Factory::getApplication()->getIdentity();
        $component = 'com_audatoria';
        $assetName = $component;

        if ($id && $section !== 'component') {
            $assetName .= '.' . $section . '.' . $id;
        } elseif ($section !== 'component') {
            $assetName .= '.' . $section; // Para categorías o tipos generales
        }
        // Para 'component', assetName es solo 'com_audatoria'

        $actions = new \Joomla\CMS\Object\CMSObject;

        $standard_actions = [
            'core.admin', 'core.manage', 'core.create', 'core.edit',
            'core.edit.state', 'core.edit.own', 'core.delete',
        ];
        // Permisos personalizados
        $custom_actions = [];
        if ($section === 'channel') {
            $custom_actions[] = 'channel.import';
        }


        foreach ($standard_actions as $action) {
            $actions->set($action, $user->authorise($action, $assetName));
        }
        foreach ($custom_actions as $action) {
            $actions->set($action, $user->authorise($action, $assetName));
        }

        return $actions;
    }

    /**
     * Configura el menú lateral (submenu).
     *
     * @param   string  $activeView  El nombre de la vista activa actual.
     *
     * @return  array  Un array de ítems para el menú lateral.
     */
    public static function getSidebarItems(string $activeView = 'timelines'): array
    {
        $items = [];

        $items[] = [
            'title' => Text::_('COM_AUDATORIA_SUBMENU_TIMELINES'),
            'link' => 'index.php?option=com_audatoria&view=timelines',
            'active' => ($activeView === 'timelines' || $activeView === 'timeline'),
        ];
        $items[] = [
            'title' => Text::_('COM_AUDATORIA_SUBMENU_ITEMS'),
            'link' => 'index.php?option=com_audatoria&view=items',
            'active' => ($activeView === 'items' || $activeView === 'item'),
        ];
        $items[] = [
            'title' => Text::_('COM_AUDATORIA_SUBMENU_CHANNELS'),
            'link' => 'index.php?option=com_audatoria&view=channels',
            'active' => ($activeView === 'channels' || $activeView === 'channel'),
        ];

        // Permitir a otros componentes añadir a este menú lateral
        // Factory::getApplication()->triggerEvent('onGetAudatoriaSidebarItems', [&$items, $activeView]);

        return $items;
    }

    /**
     * Obtiene los campos de filtro para una vista específica.
     *
     * @param string $viewName El nombre de la vista (ej. 'timelines', 'items').
     *
     * @return string El XML de los campos de filtro.
     */
    public static function getFilterFields(string $viewName): string
    {
        $path = ComponentHelper::getXmlPath('com_audatoria') . '/forms/filter_' . $viewName . '.xml';

        if (file_exists($path)) {
            return $path;
        }

        return ''; // O un XML por defecto
    }
}