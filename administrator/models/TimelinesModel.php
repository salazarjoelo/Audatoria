<?php
// Ubicación: administrator/models/timelines.php
namespace Joomla\Component\Audatoria\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text; // Para usar Text

class TimelinesModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  Un array de configuración opcional.
     */
    public function __construct($config = [])
    {
        // Habilitar filtros de búsqueda si están definidos en el XML del filtro
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'title', 'a.title',
                'alias', 'a.alias',
                'state', 'a.state', // Usar 'state' en lugar de 'published'
                'access', 'a.access', 'access_level',
                'created_user_id', 'a.created_user_id', 'author_id',
                'created_time', 'a.created_time',
                'language', 'a.language',
                'ordering', 'a.ordering',
            ];
        }

        parent::__construct($config);
    }

    /**
     * Método para construir una consulta SQL para cargar los datos de la lista.
     *
     * @return  \Joomla\Database\Query  Un objeto \Joomla\Database\Query.
     */
    protected function getListQuery()
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // Seleccionar los campos necesarios de la tabla de timelines.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.title, a.alias, a.state, a.access, a.created_time, a.created_user_id, a.language, a.ordering'
            )
        );
        $query->from($db->quoteName('#__audatoria_timelines', 'a'));

        // Unir con la tabla de niveles de acceso para mostrar el nombre del nivel.
        $query->select('ag.title AS access_level')
            ->join('LEFT', $db->quoteName('#__viewlevels', 'ag'), 'ag.id = a.access');

        // Unir con la tabla de usuarios para mostrar el nombre del autor.
        $query->select('u.name AS author_name')
            ->join('LEFT', $db->quoteName('#__users', 'u'), 'u.id = a.created_user_id');

        // Unir con la tabla de idiomas si es necesario (no siempre se muestra el título del idioma).
        // $query->select('l.title AS language_title')
        //    ->join('LEFT', $db->quoteName('#__languages', 'l'), 'l.lang_code = a.language');

        // Filtrar por nivel de acceso.
        $access = $this->getState('filter.access');
        if (is_numeric($access)) {
            $query->where('a.access = ' . (int) $access);
        }

        // Filtrar por estado publicado.
        $published = $this->getState('filter.state'); // Usar 'state'
        if (is_numeric($published)) {
            $query->where('a.state = ' . (int) $published);
        } elseif ($published === '') { // Por defecto, mostrar publicados y no publicados, pero no archivados ni eliminados
             $query->where('a.state IN (0, 1)');
        }


        // Filtrar por búsqueda en el título o alias.
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } elseif (stripos($search, 'author:') === 0) {
                $searchAuthor = $db->quote('%' . $db->escape(substr($search, 7), true) . '%');
                $query->where('u.name LIKE ' . $searchAuthor . ' OR u.username LIKE ' . $searchAuthor);
            } else {
                $searchTerm = $db->quote('%' . $db->escape($search, true) . '%');
                $query->where('(' . $db->quoteName('a.title') . ' LIKE ' . $searchTerm . ' OR ' . $db->quoteName('a.alias') . ' LIKE ' . $searchTerm . ')');
            }
        }
        
        // Filtrar por idioma
        $language = $this->getState('filter.language');
        if (!empty($language)) {
            $query->where('a.language = ' . $db->quote($language));
        }


        // Añadir la ordenación de la lista.
        $listOrder = $this->getState('list.ordering', 'a.title');
        $listDirn  = $this->getState('list.direction', 'ASC');
        
        // Validar la columna de ordenación
        $validOrderCols = $this->getFilterFields(); // Obtiene los campos de filtro definidos en el constructor
        if (isset($validOrderCols[$listOrder])) {
             $query->order($db->escape($listOrder) . ' ' . $db->escape($listDirn));
        } else {
             // Fallback a un ordenamiento por defecto si la columna no es válida
             $query->order($db->quoteName('a.title') . ' ASC');
        }


        return $query;
    }

    /**
     * Método para popular los datos del estado.
     * Nota: el filtro de categoría no se aplica aquí, pero se puede añadir si es necesario.
     */
    protected function populateState($ordering = 'a.title', $direction = 'ASC') // Cambiado el orden por defecto
    {
        $app = Factory::getApplication();

        // Cargar los filtros.
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
        $this->setState('filter.search', $search);

        $accessId = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', null, 'int');
        $this->setState('filter.access', $accessId);

        $state = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string'); // 'filter_state' o 'filter_published'
        $this->setState('filter.state', $state);
        
        $language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '', 'string');
        $this->setState('filter.language', $language);


        // Cargar parámetros de la lista.
        parent::populateState($ordering, $direction);
    }

    /**
     * Define el mensaje que se mostrará cuando la tabla esté vacía
     *
     * @return  string
     * @since   3.0
     */
    public function getEmptyMessage()
    {
        return Text::_('COM_AUDATORIA_TIMELINES_NO_TIMELINES');
    }
}