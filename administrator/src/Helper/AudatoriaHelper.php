<?php
namespace Salazarjoelo\Component\Audatoria;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
// use Joomla\CMS\Access\Access; // No se usa directamente Access aquí si getActions ya lo hace
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Object\CMSObject; // Para el tipado de retorno de getActions

class AudatoriaHelper
{
    public static function getActions(string $assetName = 'com_audatoria', int $id = 0): CMSObject
    {
        $user   = Factory::getApplication()->getIdentity();
        $result = new CMSObject;

        if ($id) {
            // Para un ítem específico, ej. com_audatoria.timeline.1
            $assetName = 'com_audatoria.' . $assetName . '.' . $id;
        } elseif ($assetName !== 'com_audatoria') {
             // Para una categoría/tipo, ej. com_audatoria.timeline
            $assetName = 'com_audatoria.' . $assetName;
        }
        // Si $assetName es 'com_audatoria', es para el componente en general.

        $actions = [
            'core.admin', 'core.manage', 'core.create', 'core.delete',
            'core.edit', 'core.edit.state', 'core.edit.own',
        ];

        // Permisos personalizados específicos de tu componente
        // Ejemplo: si $assetName se refiere a la sección 'channel'
        if (strpos($assetName, 'com_audatoria.channel') === 0 || $assetName === 'com_audatoria.channel') {
             $actions[] = 'channel.import'; // Asegúrate que esta acción esté en access.xml
        }


        foreach ($actions as $action) {
            $result->set($action, $user->authorise($action, $assetName));
        }

        return $result;
    }

    public static function getSidebarItems(string $activeView = 'timelines'): array
    {
        $items = [];
        $user = Factory::getApplication()->getIdentity(); // Para comprobar permisos si es necesario

        // Dashboard (si existe)
        // if ($user->authorise('core.manage', 'com_audatoria')) { // Ejemplo de permiso
        //     $items[] = [
        //         'title' => Text::_('COM_AUDATORIA_SUBMENU_DASHBOARD'), // Necesitas esta cadena de idioma
        //         'link' => 'index.php?option=com_audatoria&view=dashboard', // Asume que tienes una vista 'dashboard'
        //         'active' => ($activeView === 'dashboard'),
        //         'icon' => 'icon-home', // O el icono que prefieras
        //     ];
        // }

        $items[] = [
            'title' => Text::_('COM_AUDATORIA_SUBMENU_TIMELINES'),
            'link' => 'index.php?option=com_audatoria&view=timelines',
            'active' => ($activeView === 'timelines' || $activeView === 'timeline'),
            'icon' => 'icon-list',
        ];
        $items[] = [
            'title' => Text::_('COM_AUDATORIA_SUBMENU_ITEMS'),
            'link' => 'index.php?option=com_audatoria&view=items',
            'active' => ($activeView === 'items' || $activeView === 'item'),
            'icon' => 'icon-file-alt',
        ];
        $items[] = [
            'title' => Text::_('COM_AUDATORIA_SUBMENU_CHANNELS'),
            'link' => 'index.php?option=com_audatoria&view=channels',
            'active' => ($activeView === 'channels' || $activeView === 'channel'),
            'icon' => 'icon-youtube', // O 'icon-podcast' si es más genérico
        ];
        
        // Ejemplo de añadir condicionalmente basado en permisos
        // if ($user->authorise('core.options', 'com_audatoria')) {
        //     $items[] = [
        //        'title'    => Text::_('JGLOBAL_CONFIGURATION'),
        //        'link'     => 'index.php?option=com_config&view=component&component=com_audatoria',
        //        'active'   => ($activeView === 'config'),
        //        'icon'     => 'icon-cogs',
        //    ];
        // }

        // Permitir a otros componentes añadir a este menú lateral (opcional)
        // Factory::getApplication()->triggerEvent('onGetAudatoriaSidebarItems', [&$items, $activeView]);

        return $items;
    }

    // getFilterFields no es tan común aquí, usualmente se define en el modelo (getFilterForm).
    // Pero si lo usas, asegúrate que el path sea correcto.
    /*
    public static function getFilterFields(string $viewName): string
    {
        // Esto debería apuntar a administrator/forms/filter_VIEWNAME.xml
        $path = JPATH_COMPONENT_ADMINISTRATOR . '/forms/filter_' . $viewName . '.xml';

        if (file_exists($path)) {
            return $path;
        }
        return ''; 
    }
    */
}