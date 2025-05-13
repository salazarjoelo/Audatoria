<?php
// Ubicación: administrator/tables/channel.php
namespace Salazarjoelo\Component\Audatoria\Administrator\Table; // NAMESPACE CORREGIDO

\defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\CMS\Language\Text; // Para Text::_()

class ChannelTable extends Table
{
    public function __construct(DatabaseDriver &$db) // El tipado del parámetro $db es correcto
    {
        parent::__construct('#__audatoria_channels', 'id', $db);
        $this->setColumnAlias('published', 'state'); // Mapear 'published' (de JTable) a 'state' (de tu tabla)
    }

    public function check()
    {
        if (empty($this->channel_id)) {
            $this->setError(Text::_('COM_AUDATORIA_ERROR_CHANNEL_ID_REQUIRED'));
            return false;
        }

        if (empty($this->timeline_id) || (int) $this->timeline_id <= 0) {
            $this->setError(Text::_('COM_AUDATORIA_ERROR_CHANNEL_TIMELINE_ID_REQUIRED'));
            return false;
        }
        
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
            $this->setError(Text::_('COM_AUDATORIA_ERROR_CHANNEL_ID_DUPLICATE_FOR_TIMELINE'));
            return false;
        }

        return parent::check();
    }
}