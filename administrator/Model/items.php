<?php
// Ubicación: administrator/models/items.php
namespace Salazarjoelo\Component\Audatoria\Administrator\Model; // NAMESPACE CORREGIDO

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
                'access', 'a.access',
                'access_level', 'ag.title', // Calificado con alias de tabla
                'created_user_id', 'a.created_user_id',
                'author_name', 'u.name', // Calificado con alias de tabla
                'created_time', 'a.created_time',
                'language', 'a.language',
                'ordering', 'a.ordering',
                'timeline_id', 'a.timeline_id',
                'timeline_title', 'tl.title', // Calificado con alias de tabla
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
                'a.timeline_id, a.start_date, a.media_type, a.checked_out, a.checked_out_time' // Añadido checked_out
            )
        )
        ->from($db->quoteName('#__audatoria_items', 'a'));

        $query->select('tl.title AS timeline_title')
            ->join('LEFT', $db->quoteName('#__audatoria_timelines', 'tl'), 'tl.id = a.timeline_id');

        $query->select('ag.title AS access_level')
            ->join('LEFT', $db->quoteName('#__viewlevels', 'ag'), 'ag.id = a.access');

        $query->select('u.name AS author_name')
            ->join('LEFT', $db->quoteName('#__users', 'u'), 'u.id = a.created_user_id');
            
        $query->select('uc.name AS editor') // Para checked_out
             ->join('LEFT', $db->quoteName('#__users', 'uc'), 'uc.id = a.checked_out');
            
        // $query->select('l.title AS language_title') // Descomentar si se necesita el título del idioma
        //    ->join('LEFT', $db->quoteName('#__languages', 'l'), 'l.lang_code = a.language');

        $state = $this->getState('filter.state');
        if (is_numeric($state)) {
            $query->where('a.state = ' . (int) $state);
        } elseif ($state === '') { // Mostrar publicados y no publicados por defecto
             $query->where('a.state IN (0, 1)');
        }

        $access = $this->getState('filter.access');
        if (is_numeric($access)) {
            $query->where('a.access = ' . (int) $access);
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
                $query->where('(' . $db->quoteName('a.title') . ' LIKE ' . $searchTerm . ' OR ' . $db->quoteName('a.description') . ' LIKE ' . $searchTerm . ')');
            }
        }
        
        $language = $this->getState('filter.language');
        if (!empty($language) && $language !== '*') { // Añadir comprobación para '*'
            $query->where('a.language = ' . $db->quote($language));
        }

        $listOrder = $this->getState('list.ordering', 'a.ordering'); 
        $listDirn  = $this->getState('list.direction', 'ASC');
        
        if (in_array($listOrder, $this->getFilterFields())) {
             $query->order($db->escape($listOrder) . ' ' . $db->escape($listDirn));
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

        $state = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'cmd'); // Usar cmd para estado
        $this->setState('filter.state', $state);
        
        $accessId = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', 0, 'int'); // 0 para "Todos los niveles"
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