<?php
namespace Salazarjoelo\Component\Audatoria\Administrator\Helper; // Namespace Correcto

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Access\Access;         // Para Access::getActionsFromFile
use Joomla\CMS\Component\ComponentHelper; // Para los parámetros del componente si es necesario aquí

class AudatoriaHelper
{
    /**
     * Obtiene las acciones permitidas para el usuario actual sobre un asset.
     *
     * @param   string  $assetSection  La sección del asset (ej. 'component', 'timeline', 'item', 'channel').
     * @param   int     $id            El ID del ítem específico (opcional).
     *
     * @return  CMSObject  Un objeto con las acciones y sus valores booleanos.
     */
    public static function getActions(string $assetSection = 'component', int $id = 0): CMSObject
    {
        $user   = Factory::getApplication()->getIdentity();
        $result = new CMSObject;

        // Construir el nombre completo del asset
        $assetName = 'com_audatoria';
        if (!empty($assetSection) && $assetSection !== 'component') {
            $assetName .= '.' . $assetSection;
        }

        if ($id > 0 && $assetSection !== 'component') {
            $assetName .= '.' . (int) $id;
        }

        // Definir las acciones a verificar. Es mejor leerlas desde access.xml si es posible.
        // O definir un array base y añadir específicas de sección.
        $actions = Access::getActionsFromFile(
            JPATH_ADMINISTRATOR . '/components/com_audatoria/access.xml',
            "/access/section[@name='" . ($assetSection ?: 'component') . "']/"
        );

        if (empty($actions) && $assetSection === 'component') { // Fallback para la sección 'component' si no se lee de XML
             $actions = Access::getActionsFromFile(
                 JPATH_ADMINISTRATOR . '/components/com_audatoria/access.xml',
                 "/access/section[@name='component']/"
             );
             // Si aún está vacío, usar un conjunto por defecto de acciones core
             if (empty($actions)) {
                $coreActions = ['core.admin', 'core.manage', 'core.create', 'core.delete', 'core.edit', 'core.edit.state', 'core.edit.own'];
                foreach ($coreActions as $action) {
                    $result->set($action, $user->authorise($action, $assetName));
                }
                return $result;
             }
        }


        if (!empty($actions)) {
            foreach ($actions as $action) {
                $result->set($action->name, $user->authorise($action->name, $assetName));
            }
        }

        return $result;
    }

    /**
     * Obtiene los ítems para el submenú/sidebar de administración.
     *
     * @param   string  $activeView  La vista actualmente activa.
     *
     * @return  array  Array de ítems de menú.
     */
    public static function getSidebarItems(string $activeView = ''): array
    {
        $app = Factory::getApplication();
        $input = $app->input; 
        if (empty($activeView)) {
            $activeView = $input->getCmd('view', 'timelines'); // Vista por defecto si no se pasa
        }

        $items = [];

        // Ítem para Líneas de Tiempo
        $items[] = [
            'title' => Text::_('COM_AUDATORIA_SUBMENU_TIMELINES'), // Debe estar en es-ES.com_audatoria.sys.ini
            'link' => 'index.php?option=com_audatoria&view=timelines',
            'active' => ($activeView === 'timelines' || $activeView === 'timeline'), // Activo si la vista es 'timelines' o 'timeline' (singular)
            'icon' => 'icon-list-alt', // O un icono más específico como 'icon-stopwatch' de FontAwesome si tu plantilla lo soporta
        ];

        // Ítem para Ítems de Línea de Tiempo
        $items[] = [
            'title' => Text::_('COM_AUDATORIA_SUBMENU_ITEMS'),
            'link' => 'index.php?option=com_audatoria&view=items',
            'active' => ($activeView === 'items' || $activeView === 'item'),
            'icon' => 'icon-file-alt',
        ];

        // Ítem para Canales de Importación
        $items[] = [
            'title' => Text::_('COM_AUDATORIA_SUBMENU_CHANNELS'),
            'link' => 'index.php?option=com_audatoria&view=channels',
            'active' => ($activeView === 'channels' || $activeView === 'channel'),
            'icon' => 'icon-rss', // O 'icon-youtube', 'icon-cloud-upload'
        ];

        // Opcional: Enlace a la configuración del componente si el usuario tiene permisos
        $user = $app->getIdentity();
        if ($user->authorise('core.admin', 'com_audatoria') || $user->authorise('core.options', 'com_audatoria')) {
            $items[] = [
               'title'    => Text::_('JOPTIONS'), // Constante global de Joomla
               'link'     => 'index.php?option=com_config&view=component&component=com_audatoria',
               'active'   => ($input->getCmd('option') === 'com_config' && $input->getCmd('view') === 'component' && $input->getCmd('component') === 'com_audatoria'),
               'icon'     => 'icon-cog',
           ];
        }
        return $items;
    }
}