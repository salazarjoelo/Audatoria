<?php
// Ubicación: administrator/tables/timeline.php
namespace Joomla\Component\Audatoria\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

class TimelineTable extends Table // Renombrar a TimelineTable para convención
{
    /**
     * Constructor
     *
     * @param   DatabaseDriver  &$db  A database connector object
     */
    public function __construct(DatabaseDriver &$db)
    {
        parent::__construct('#__audatoria_timelines', 'id', $db);
        // Habilita el uso de JTableObserver_ContentHistory si necesitas historial de contenido
        // $this->setSupportsContentHistory(true);
    }

    /**
     * Asegura que el alias sea único.
     *
     * @param   array   $pks    An array of primary key values.
     * @param   boolean $optimise  True if storing fields from within the form.
     *
     * @return  boolean  True if validated.
     */
    public function store($updateNulls = false)
    {
        // Generar alias si está vacío y hay un título.
        if (empty($this->alias) && !empty($this->title)) {
            $this->alias = \Joomla\CMS\Application\ApplicationHelper::stringURLSafe($this->title);
        }
        // Si el alias sigue vacío (ej. solo caracteres especiales en el título), genera uno único.
        if (empty($this->alias)) {
            $this->alias = \Joomla\CMS\Application\ApplicationHelper::stringURLSafe(\Joomla\CMS\Date\Date::getInstance()->format('Y-m-d-H-i-s'));
        }

        // Asegurar que el alias sea único para esta tabla
        $table = Table::getInstance('Timeline', 'Joomla\\Component\\Audatoria\\Administrator\\Table');
        $this->alias = $table->generateUniqueAlias($this->alias, $this->id);


        return parent::store($updateNulls);
    }

    /**
     * Generates a unique alias for an item.
     *
     * @param   string  $alias  The seed alias.
     * @param   int     $id     The ID of the item being saved. If 0, a new item is assumed.
     *
     * @return  string  The unique alias.
     */
    public function generateUniqueAlias(string $alias, int $id = 0): string
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName('alias'))
            ->from($db->quoteName($this->_tbl))
            ->where($db->quoteName('alias') . ' = ' . $db->quote($alias));

        if ($id) {
            $query->where($db->quoteName($this->_tbl_key) . ' <> ' . (int) $id);
        }

        $db->setQuery($query);
        $existingAlias = $db->loadResult();

        if ($existingAlias) {
            // Alias existe, intentar añadir un sufijo numérico
            $counter = 2;
            $baseAlias = $alias;
            do {
                $alias = $baseAlias . '-' . $counter;
                $query->clear('where')
                    ->where($db->quoteName('alias') . ' = ' . $db->quote($alias));
                if ($id) {
                    $query->where($db->quoteName($this->_tbl_key) . ' <> ' . (int) $id);
                }
                $db->setQuery($query);
                $existingAlias = $db->loadResult();
                $counter++;
            } while ($existingAlias);
        }

        return $alias;
    }

    /**
     * Define a an alias for an item if it does not exist.
     * If it does exist, it will ensure it is unique.
     *
     * @return bool
     */
    public function check()
    {
        if (empty($this->alias)) {
            if (!empty($this->title)) {
                $this->alias = \Joomla\CMS\Application\ApplicationHelper::stringURLSafe($this->title);
            } else {
                 // Si no hay título, crea un alias basado en la fecha para asegurar que no esté vacío
                $this->alias = \Joomla\CMS\Application\ApplicationHelper::stringURLSafe(\Joomla\CMS\Date\Date::getInstance()->format('Y-m-d-H-i-s'));
            }
        } else {
             // Si el alias existe, asegúrate de que es seguro para URL
            $this->alias = \Joomla\CMS\Application\ApplicationHelper::stringURLSafe($this->alias);
        }


        // Asegurar unicidad del alias si existe una función para ello en el modelo o aquí directamente
        // $this->alias = $this->generateUniqueAlias($this->alias, $this->id);

        return parent::check();
    }
}