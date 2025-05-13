<?php
// Ubicación: administrator/tables/timeline.php
namespace Salazarjoelo\Component\Audatoria\Administrator\Table; // NAMESPACE CORREGIDO

\defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory; // Para Factory::getDate()
use Joomla\CMS\Language\Text; // Para Text::_()

class TimelineTable extends Table
{
    public function __construct(DatabaseDriver &$db)
    {
        parent::__construct('#__audatoria_timelines', 'id', $db);
        $this->setColumnAlias('published', 'state');
        // $this->setSupportsContentHistory(true); // Descomentar si se usa historial
    }

    public function check() : bool
    {
        if (empty($this->title)) {
            $this->setError(Text::_('JLIB_DATABASE_ERROR_MUSTCONTAIN_A_TITLE')); // Usar clave de idioma estándar
            return false;
        }

        if (empty($this->alias)) {
            $this->alias = ApplicationHelper::stringURLSafe($this->title);
            if (empty($this->alias)) { // Si el título solo tenía caracteres no alfanuméricos
                $this->alias = ApplicationHelper::stringURLSafe(Factory::getDate()->format('Y-m-d-H-i-s'));
            }
        } else {
            $this->alias = ApplicationHelper::stringURLSafe($this->alias);
        }
        
        // Check asset_id
        if (empty($this->asset_id)) {
             $this->asset_id = 0;
        }

        return parent::check();
    }

    public function store($updateNulls = false) : bool
    {
        // Asegurar alias único ANTES de llamar al store() padre.
        // El método check() ya prepara el alias.
        // Aquí nos aseguramos de que si el alias ya existe, se genere uno único.
        if (property_exists($this, 'alias')) {
             $this->alias = $this->generateUniqueAlias($this->alias, $this->id);
        }

        return parent::store($updateNulls);
    }

    public function generateUniqueAlias(string $aliasSeed, int $id = 0) : string
    {
        $alias = $aliasSeed;
        $db    = $this->getDbo();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($this->getTableName())
            ->where($db->quoteName('alias') . ' = ' . $db->quote($alias));

        if ($id) {
            $query->where($db->quoteName($this->getKeyName()) . ' != ' . (int) $id);
        }

        $counter = 2;
        while ($db->setQuery($query)->loadResult()) {
            $alias   = $aliasSeed . '-' . $counter;
            $query->clear('where') // Limpiar solo la condición del alias
                  ->where($db->quoteName('alias') . ' = ' . $db->quote($alias));
            if ($id) {
                $query->where($db->quoteName($this->getKeyName()) . ' != ' . (int) $id);
            }
            $counter++;
        }

        return $alias;
    }
}