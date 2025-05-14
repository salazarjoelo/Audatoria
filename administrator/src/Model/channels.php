<?php
// Ubicación: administrator/models/channels.php
namespace Salazarjoelo\Component\Audatoria\Administrator\Model; // NAMESPACE CORREGIDO

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class ChannelsModel extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'title', 'a.title',
                'channel_id', 'a.channel_id', // Cambiado de channel_id_search
                'state', 'a.state', 
                'timeline_id', 'a.timeline_id', 
                'timeline_title', 'tl.title', // Calificado con alias de tabla
                'last_checked', 'a.last_checked',
            ];
        }
        parent::__construct($config);
    }

    protected function getListQuery()
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.title, a.channel_id, a.state, a.timeline_id, a.last_checked'
            )
        )
        ->from($db->quoteName('#__audatoria_channels', 'a'));

        $query->select('tl.title AS timeline_title')
            ->join('LEFT', $db->quoteName('#__audatoria_timelines', 'tl'), 'tl.id = a.timeline_id');
         
        $query->select('u.name AS editor') // Para checked_out
             ->join('LEFT', $db->quoteName('#__users', 'u'), 'u.id = a.checked_out');


        $state = $this->getState('filter.state');
        if (is_numeric($state)) {
            $query->where('a.state = ' . (int) $state);
        }
        
        $timelineId = $this->getState('filter.timeline_id');
        if (is_numeric($timelineId) && $timelineId > 0) {
            $query->where('a.timeline_id = ' . (int) $timelineId);
        }

        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $searchTerm = $db->quote('%' . $db->escape($search, true) . '%');
                $query->where('(' . $db->quoteName('a.title') . ' LIKE ' . $searchTerm . ' OR ' . $db->quoteName('a.channel_id') . ' LIKE ' . $searchTerm . ')');
            }
        }

        $listOrder = $this->getState('list.ordering', 'a.title');
        $listDirn  = $this->getState('list.direction', 'ASC');
        
        // Validar la columna de ordenación para evitar SQL injection
        if (in_array($listOrder, $this->getFilterFields())) {
             $query->order($db->escape($listOrder) . ' ' . $db->escape($listDirn));
        } else {
             // Fallback a un ordenamiento por defecto seguro
             $query->order($db->quoteName('a.title') . ' ASC');
        }

        return $query;
    }

    protected function populateState($ordering = 'a.title', $direction = 'ASC')
    {
        $app = Factory::getApplication();

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
        $this->setState('filter.search', $search);

        $state = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'cmd'); // Usar cmd para estado
        $this->setState('filter.state', $state);
        
        $timelineId = $this->getUserStateFromRequest($this->context . '.filter.timeline_id', 'filter_timeline_id', 0, 'int');
        $this->setState('filter.timeline_id', $timelineId);

        parent::populateState($ordering, $direction);
    }
    
    public function getEmptyMessage()
    {
        return Text::_('COM_AUDATORIA_CHANNELS_NO_CHANNELS');
    }
}