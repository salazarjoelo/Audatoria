<?php
namespace Salazarjoelo\Component\Audatoria;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
<?php
// Ubicación: administrator/src/Helper/AudatoriaHelper.php
namespace Salazarjoelo\Component\Audatoria\Administrator\Helper; // NAMESPACE CORREGIDO

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Object\CMSObject; // Para el tipado de retorno de getActions
// No necesitas use Joomla\CMS\Component\ComponentHelper; aquí si no lo usas directamente.

class AudatoriaHelper // El nombre de la clase sigue siendo AudatoriaHelper
{
    public static function getActions(string $assetPrefix = 'com_audatoria', int $id = 0): CMSObject // Cambiado $assetName a $assetPrefix para claridad
    {
        $user   = Factory::getApplication()->getIdentity();
        $result = new CMSObject;

        // Construir el nombre completo del asset
        // Ej: com_audatoria (para el componente)
        // Ej: com_audatoria.timeline (para la sección timeline)
        // Ej: com_audatoria.timeline.1 (para el item timeline específico con id 1)
        $assetName = $assetPrefix;
        if ($id > 0) {
            // Si se proporciona un $id, y $assetPrefix no es ya el nombre del componente base,
            // podría ser com_audatoria.TIPO_DE_ITEM.ID
            // Ejemplo: si $assetPrefix es 'timeline', $assetName se convierte en 'com_audatoria.timeline.ID'
            // Si $assetPrefix ya es 'com_audatoria.timeline', entonces '.ID'
            if (strpos($assetPrefix, 'com_audatoria.') !== 0) { // Si no empieza con com_audatoria.
                $assetName = 'com_audatoria.' . $assetPrefix . '.' . $id;
            } elseif (count(explode('.', $assetPrefix)) == 2) { // Si es como com_audatoria.timeline
                 $assetName = $assetPrefix . '.' . $id;
            }
            // Si $assetPrefix es solo 'com_audatoria' y hay ID, se asume que el ID es de un tipo no especificado aquí.
            // Para ACL granular, es mejor que la vista pase el tipo correcto: 'timeline', 'item', 'channel'.
        }
        // Ejemplo de llamada desde una vista de timeline: AudatoriaHelper::getActions('timeline', $this->item->id)
        // Ejemplo de llamada para el componente: AudatoriaHelper::getActions() o AudatoriaHelper::getActions('com_audatoria')

        $actions = [
            'core.admin', 'core.manage', 'core.create', 'core.delete',
            'core.edit', 'core.edit.state', 'core.edit.own',
        ];

        // Permisos personalizados específicos. El assetName debe coincidir con access.xml
        // <section name="channel"> <action name="channel.import" ... /> </section>
        // El asset para esto sería "com_audatoria.channel"
        if ($assetPrefix === 'channel' || $assetName === 'com_audatoria.channel') {
             $actions[] = 'channel.import';
        }
        // O si el $assetPrefix es el asset de sección directamente (ej. 'com_audatoria.channel')
        // if (strpos($assetName, '.channel') !== false) {
        //    $actions[] = 'channel.import';
        // }


        foreach ($actions as $action) {
            $result->set($action, $user->authorise($action, $assetName));
        }

        return $result;
    }

    public static function getSidebarItems(string $activeView = 'timelines'): array
    {
        $items = [];
        // $user = Factory::getApplication()->getIdentity(); // Para comprobar permisos si es necesario

        // Los títulos de los submenús deben estar en los archivos .sys.ini
        // COM_AUDATORIA_SUBMENU_TIMELINES, COM_AUDATORIA_SUBMENU_ITEMS, etc.

        $items[] = [
            'title' => Text::_('COM_AUDATORIA_SUBMENU_TIMELINES'),
            'link' => 'index.php?option=com_audatoria&view=timelines',
            'active' => ($activeView === 'timelines' || $activeView === 'timeline'),
            'icon' => 'icon-list', // O un icono más específico como 'icon-stopwatch'
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
            'icon' => 'icon-podcast', // icon-youtube es una opción, podcast es más genérico
        ];

        // Ejemplo para opciones del componente:
        // if (Factory::getApplication()->getIdentity()->authorise('core.admin', 'com_audatoria')) {
        //     $items[] = [
        //        'title'    => Text::_('JOPTIONS'),
        //        'link'     => 'index.php?option=com_config&view=component&component=com_audatoria',
        //        'active'   => (Factory::getApplication()->input->get('view') === 'component' && Factory::getApplication()->input->get('component') === 'com_audatoria'),
        //        'icon'     => 'icon-cog',
        //    ];
        // }

        return $items;
    }