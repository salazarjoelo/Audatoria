<?php
namespace Salazarjoelo\Component\Audatoria\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Date\Date; // Para el alias con fecha

class TimelineTable extends Table
{
    public function __construct(DatabaseDriver &$db)
    {
        parent::__construct('#__audatoria_timelines', 'id', $db);
        // Si tu campo de estado en la BD se llama 'state', y el XML del formulario también usa 'state',
        // no necesitas setColumnAlias('published', 'state') a menos que uses lógica de JTable
        // que específicamente busque la propiedad 'published'.
    }

    public function check(): bool
    {
        if (empty($this->title)) {
            $this->setError(Text::_('COM_AUDATORIA_ERROR_TIMELINE_TITLE_REQUIRED')); // Necesitas esta constante
            return false;
        }

        if (empty($this->alias)) {
            $this->alias = ApplicationHelper::stringURLSafe($this->title);
            if (empty($this->alias)) {
                // CORREGIDO: Usar Date
                $this->alias = ApplicationHelper::stringURLSafe(Factory::getApplication()->getDate()->format('Y-m-d-H-i-s'));
            }
        } else {
            $this->alias = ApplicationHelper::stringURLSafe($this->alias);
        }
        
        if (empty($this->asset_id)) {
             $this->asset_id = 0;
        }

        return parent::check();
    }

    public function store($updateNulls = false): bool
    {
        if (property_exists($this, 'alias')) {
             $this->alias = $this->generateUniqueAlias($this->alias, (int) $this->id);
        }
        return parent::store($updateNulls);
    }

    public function generateUniqueAlias(string $aliasSeed, int $id = 0): string
    {
        $alias = $aliasSeed;
        $db    = $this->getDbo();
        $query = $db->getQuery(); // CORREGIDO
        $query->select($db->quoteName($this->getKeyName()))
            ->from($db->quoteName($this->_tbl))
            ->where($db->quoteName('alias') . ' = ' . $db->quote($alias));

        if ($id) {
            $query->where($db->quoteName($this->getKeyName()) . ' != ' . (int) $id);
        }

        $counter = 2;
        $this->_db->setQuery($query);
        while ($this->_db->loadResult()) {
            $alias   = $aliasSeed . '-' . $counter;
            $query->clear('where')
                  ->where($db->quoteName('alias') . ' = ' . $db->quote($alias));
            if ($id) {
                $query->where($db->quoteName($this->getKeyName()) . ' != ' . (int) $id);
            }
            $this->_db->setQuery($query);
            $counter++;
        }
        return $alias;
    }
}