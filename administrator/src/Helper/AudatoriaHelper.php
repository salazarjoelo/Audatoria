<?php
namespace Salazarjoelo\Component\Audatoria\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Access\Access;

class AudatoriaHelper
{
    public static function getActions(string $assetSection = '', int $id = 0): CMSObject
    {
        $app    = Factory::getApplication();
        $user   = $app->getIdentity(); // CORREGIDO
        $result = new CMSObject;
        $assetName = 'com_audatoria';

        if (!empty($assetSection) && $assetSection !== 'component') {
            $assetName .= '.' . $assetSection;
        }

        if ($id > 0 && $assetSection !== 'component') {
            $assetName .= '.' . (int) $id;
        }

        $path = JPATH_ADMINISTRATOR . '/components/com_audatoria/access.xml';
        $xpathSectionNode = ($assetSection && $assetSection !== 'component') ? $assetSection : 'component';
        $actionsFromFile = Access::getActionsFromFile($path, "/access/section[@name='" . $xpathSectionNode . "']/");

        $actionsToAuthorize = [];
        if (!empty($actionsFromFile)) {
            foreach ($actionsFromFile as $action) {
                $actionsToAuthorize[] = $action->name;
            }
        } elseif ($xpathSectionNode === 'component') {
             $actionsToAuthorize = ['core.admin', 'core.manage', 'core.create', 'core.delete', 'core.edit', 'core.edit.state', 'core.edit.own'];
        }
        // Asegurar que 'channel.import' se evalúe si la sección es 'channel' y la acción existe en access.xml
        if ($assetSection === 'channel' && $xpathSectionNode === 'channel') {
             // Access::getActionsFromFile ya debería haber cargado 'channel.import' si está en la sección channel.
             // Si necesitas añadirla explícitamente SIEMPRE para la sección 'channel', puedes hacerlo,
             // pero es mejor que esté definida en access.xml.
             // Ejemplo: si channel.import está en access.xml:
             // if (!in_array('channel.import', $actionsToAuthorize)) { /* ...podrías añadirlo o no, según tu lógica */ }
        }

        foreach ($actionsToAuthorize as $actionName) {
            $result->set($actionName, $user->authorise($actionName, $assetName));
        }
        
        return $result;
    }

    public static function getSidebarItems(string $activeView = ''): array
    {
        $app = Factory::getApplication();
        $input = $app->input; 
        if (empty($activeView)) {
            $activeView = $input->getCmd('view', 'timelines');
        }

        $items = [];

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
            'icon' => 'icon-podcast',
        ];
        
        $user = $app->getIdentity(); // CORREGIDO
        if ($user->authorise('core.admin', 'com_audatoria')) {
            $items[] = [
               'title'    => Text::_('JOPTIONS'),
               'link'     => 'index.php?option=com_config&view=component&component=com_audatoria',
               'active'   => ($input->getCmd('option') === 'com_config' && $input->getCmd('view') === 'component' && $input->getCmd('component') === 'com_audatoria'),
               'icon'     => 'icon-cog',
           ];
        }
        return $items;
    }
}