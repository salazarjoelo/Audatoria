<?php
// Ubicación: administrator/tables/item.php
namespace Joomla\Component\Audatoria\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

class ItemTable extends Table // Renombrar a ItemTable
{
    /**
     * Constructor
     *
     * @param   DatabaseDriver  &$db  A database connector object
     */
    public function __construct(DatabaseDriver &$db)
    {
        parent::__construct('#__audatoria_items', 'id', $db);
        // $this->setSupportsContentHistory(true);
    }

    // Puedes añadir aquí validaciones específicas (método check())
    // o lógica de almacenamiento (método store()) si es necesario.
    // Por ejemplo, asegurar que start_date sea anterior a end_date, etc.

    public function check()
    {
        // Ejemplo de validación: el título no puede estar vacío si no es una importación de YouTube (o algún otro criterio)
        if (empty($this->title) && $this->media_type !== 'youtube') { // Asumiendo que las importaciones pueden no tener título inicialmente
            $this->setError(\Joomla\CMS\Language\Text::_('COM_AUDATORIA_ERROR_ITEM_TITLE_REQUIRED'));
            return false;
        }

        if (!empty($this->start_date) && !empty($this->end_date)) {
            $startDate = new \Joomla\CMS\Date\Date($this->start_date);
            $endDate   = new \Joomla\CMS\Date\Date($this->end_date);
            if ($endDate < $startDate) {
                $this->setError(\Joomla\CMS\Language\Text::_('COM_AUDATORIA_ERROR_END_DATE_BEFORE_START_DATE'));
                return false;
            }
        }
        
        // Asegurar que timeline_id es válido
        if (empty($this->timeline_id) || (int) $this->timeline_id <= 0) {
            $this->setError(\Joomla\CMS\Language\Text::_('COM_AUDATORIA_ERROR_ITEM_TIMELINE_ID_REQUIRED'));
            return false;
        }

        return parent::check();
    }
}