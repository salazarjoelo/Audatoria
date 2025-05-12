<?php
// Ubicación: administrator/tables/channel.php
namespace Joomla\Component\Audatoria\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

class ChannelTable extends Table // Renombrar a ChannelTable
{
    /**
     * Constructor
     *
     * @param   DatabaseDriver  &$db  A database connector object
     */
    public function __construct(DatabaseDriver &$db)
    {
        parent::__construct('#__audatoria_channels', 'id', $db);
    }

    public function check()
    {
        if (empty($this->channel_id)) {
            $this->setError(\Joomla\CMS\Language\Text::_('COM_AUDATORIA_ERROR_CHANNEL_ID_REQUIRED'));
            return false;
        }

        if (empty($this->timeline_id) || (int) $this->timeline_id <= 0) {
            $this->setError(\Joomla\CMS\Language\Text::_('COM_AUDATORIA_ERROR_CHANNEL_TIMELINE_ID_REQUIRED'));
            return false;
        }
        
        // Validar que el channel_id no esté duplicado para la misma timeline_id
        // Esto ya está cubierto por el UNIQUE INDEX en la BD, pero una comprobación aquí puede dar un error más amigable.
        $query = $this->_db->getQuery(true)
            ->select($this->_db->quoteName('id'))
            ->from($this->_db->quoteName($this->_tbl))
            ->where($this->_db->quoteName('channel_id') . ' = ' . $this->_db->quote($this->channel_id))
            ->where($this->_db->quoteName('timeline_id') . ' = ' . (int) $this->timeline_id);

        if ($this->id) {
            $query->where($this->_db->quoteName('id') . ' != ' . (int) $this->id);
        }
        $this->_db->setQuery($query);

        if ($this->_db->loadResult()) {
            $this->setError(\Joomla\CMS\Language\Text::_('COM_AUDATORIA_ERROR_CHANNEL_ID_DUPLICATE_FOR_TIMELINE'));
            return false;
        }


        return parent::check();
    }
}