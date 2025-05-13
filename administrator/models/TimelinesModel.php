<?php
// Ubicación: administrator/models/TimelinesModel.php
namespace Salazarjoelo\Component\Audatoria\Administrator\Model; // NAMESPACE CORREGIDO

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class TimelinesModel extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'title', 'a.title',
                'alias', 'a.alias',
                'state', 'a.state',
                'access', 'a.access',
                'access_level', 'ag.title', // Calificado
                'created_user_id', 'a.created_user_id',
                'author_name', 'u.name', // Calificado
                'created_time', 'a.created_time',
                'language', 'a.language',
                'ordering', 'a.ordering',
                'checked_out', 'a.checked_out' // Añadido
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
                'a.id, a.title, a.alias, a.state, a.access, a.created_time, a.created_user_id, a.language, a.ordering, a.checked_out, a.checked_out_time' // Añadido checked_out
            )
        );
        $query->from($db->quoteName('#__audatoria_timelines', 'a'));

        $query->select('ag.title AS access_level')
            ->join('LEFT', $db->quoteName('#__viewlevels', 'ag'), 'ag.id = a.access');

        $query->select('u.name AS author_name')
            ->join('LEFT', $db->quoteName('#__users', 'u'), 'u.id = a.created_user_id');

        $query->select('uc.name AS editor') // Para checked_out
             ->join('LEFT', $db->quoteName('#__users', 'uc'), 'uc.id = a.checked_out');

        // $query->select('l.title AS language_title')
        //    ->join('LEFT', $db->quoteName('#__languages', 'l'), 'l.lang_code = a.language');

        $access = $this->getState('filter.access');
        if (is_numeric($access)) {
            $query->where('a.access = ' . (int) $access);
        }

        $state = $this->getState('filter.state'); // 'state' en lugar de 'published'
        if (is_numeric($state)) {
            $query->where('a.state = ' . (int) $state);
        } elseif ($state === '') { 
             $query->where('a.state IN (0, 1)');
        }

        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } elseif (stripos($search, 'author:') === 0) {
                $searchAuthor = $db->quote('%' . $db->escape(substr($search, 7), true) . '%');
                $query->where('(u.name LIKE ' . $searchAuthor . ' OR u.username LIKE ' . $searchAuthor . ')');
            } else {
                $searchTerm = $db->quote('%' . $db->escape($search, true) . '%');
                $query->where('(' . $db->quoteName('a.title') . ' LIKE ' . $searchTerm . ' OR ' . $db->quoteName('a.alias') . ' LIKE ' . $searchTerm . ')');
            }
        }
        
        $language = $this->getState('filter.language');
        if (!empty($language) && $language !== '*') {
            $query->where('a.language = ' . $db->quote($language));
        }

        $listOrder = $this->getState('list.ordering', 'a.title');
        $listDirn  = $this->getState('list.direction', 'ASC');
        
        if (in_array($listOrder, $this->getFilterFields())) {
             $query->order($db->escape($listOrder) . ' ' . $db->escape($listDirn));
        } else {
             $query->order($db->quoteName('a.title') . ' ASC');
        }

        return $query;
    }

    protected function populateState($ordering = 'a.title', $direction = 'ASC')
    {
        $app = Factory::getApplication();

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
        $this->setState('filter.search', $search);

        $accessId = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', 0, 'int');
        $this->setState('filter.access', $accessId);

        $state = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'cmd'); 
        $this->setState('filter.state', $state);
        
        $language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '', 'string');
        $this->setState('filter.language', $language);

        parent::populateState($ordering, $direction);
    }

    public function getEmptyMessage()
    {
        return Text::_('COM_AUDATORIA_TIMELINES_NO_TIMELINES');
    }
}