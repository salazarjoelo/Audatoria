<?php
// Ubicación: administrator/models/items.php
namespace Joomla\Component\Audatoria\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class ItemsModel extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'title', 'a.title',
                'state', 'a.state',
                'access', 'a.access', 'access_level',
                'created_user_id', 'a.created_user_id', 'author_id',
                'created_time', 'a.created_time',
                'language', 'a.language',
                'ordering', 'a.ordering',
                'timeline_id', 'a.timeline_id', 'timeline_title', // Para filtrar y ordenar por timeline
                'start_date', 'a.start_date',
                'media_type', 'a.media_type',
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
                'a.id, a.title, a.state, a.access, a.created_time, a.created_user_id, a.language, a.ordering, ' .
                'a.timeline_id, a.start_date, a.media_type'
            )
        )
        ->from($db->quoteName('#__audatoria_items', 'a'));

        // Uniones para filtros y visualización
        $query->select('tl.title AS timeline_title')
            ->join('LEFT', $db->quoteName('#__audatoria_timelines', 'tl'), 'tl.id = a.timeline_id');

        $query->select('ag.title AS access_level')
            ->join('LEFT', $db->quoteName('#__viewlevels', 'ag'), 'ag.id = a.access');

        $query->select('u.name AS author_name')
            ->join('LEFT', $db->quoteName('#__users', 'u'), 'u.id = a.created_user_id');
            
        // $query->select('l.title AS language_title')
        //    ->join('LEFT', $db->quoteName('#__languages', 'l'), 'l.lang_code = a.language');

        // Filtrar por estado
        $state = $this->getState('filter.state');
        if (is_numeric($state)) {
            $query->where('a.state = ' . (int) $state);
        } elseif ($state === '') {
             $query->where('a.state IN (0, 1)');
        }


        // Filtrar por acceso
        $access = $this->getState('filter.access');
        if (is_numeric($access)) {
            $query->where('a.access = ' . (int) $access);
        }
        
        // Filtrar por timeline_id
        $timelineId = $this->getState('filter.timeline_id');
        if (is_numeric($timelineId) && $timelineId > 0) {
            $query->where('a.timeline_id = ' . (int) $timelineId);
        }

        // Filtrar por búsqueda
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $searchTerm = $db->quote('%' . $db->escape($search, true) . '%');
                $query->where('(' . $db->quoteName('a.title') . ' LIKE ' . $searchTerm . ' OR ' . $db->quoteName('a.description') . ' LIKE ' . $searchTerm . ')');
            }
        }
        
        $language = $this->getState('filter.language');
        if (!empty($language)) {
            $query->where('a.language = ' . $db->quote($language));
        }


        // Ordenación
        $listOrder = $this->getState('list.ordering', 'a.ordering'); // Orden por defecto
        $listDirn  = $this->getState('list.direction', 'ASC');
        
        $validOrderCols = $this->getFilterFields();
        if (isset($validOrderCols[$listOrder])) {
            if ($listOrder === 'timeline_title') { // Ordenar por el título de la tabla unida
                $query->order($db->quoteName('tl.title') . ' ' . $db->escape($listDirn));
            } else {
                $query->order($db->escape($listOrder) . ' ' . $db->escape($listDirn));
            }
        } else {
             $query->order($db->quoteName('a.ordering') . ' ASC, ' . $db->quoteName('a.start_date') . ' ASC');
        }


        return $query;
    }

    protected function populateState($ordering = 'a.ordering', $direction = 'ASC')
    {
        $app = Factory::getApplication();

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
        $this->setState('filter.search', $search);

        $state = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string');
        $this->setState('filter.state', $state);
        
        $accessId = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', null, 'int');
        $this->setState('filter.access', $accessId);
        
        $language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '', 'string');
        $this->setState('filter.language', $language);

        $timelineId = $this->getUserStateFromRequest($this->context . '.filter.timeline_id', 'filter_timeline_id', 0, 'int');
        $this->setState('filter.timeline_id', $timelineId);


        parent::populateState($ordering, $direction);
    }
    
    public function getEmptyMessage()
    {
        return Text::_('COM_AUDATORIA_ITEMS_NO_ITEMS');
    }
}