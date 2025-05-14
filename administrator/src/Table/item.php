<?php
// Ubicación: administrator/tables/item.php
namespace Salazarjoelo\Component\Audatoria\Administrator\Table; // NAMESPACE CORREGIDO

\defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\CMS\Language\Text; // Para Text::_()
use Joomla\CMS\Factory; // Para Factory::getDate()

class ItemTable extends Table
{
    public function __construct(DatabaseDriver &$db)
    {
        parent::__construct('#__audatoria_items', 'id', $db);
        $this->setColumnAlias('published', 'state');
    }

    public function check()
    {
        if (empty($this->title)) { 
            $this->setError(Text::_('COM_AUDATORIA_ERROR_ITEM_TITLE_REQUIRED'));
            return false;
        }

        if (!empty($this->start_date) && $this->start_date != $this->_db->getNullDate()) {
             try {
                 $startDate = Factory::getDate($this->start_date);
                 if (!empty($this->end_date) && $this->end_date != $this->_db->getNullDate()) {
                     $endDate   = Factory::getDate($this->end_date);
                     if ($endDate < $startDate) {
                         $this->setError(Text::_('COM_AUDATORIA_ERROR_END_DATE_BEFORE_START_DATE'));
                         return false;
                     }
                 }
             } catch (\Exception $e) {
                 $this->setError(Text::sprintf('JLIB_DATABASE_ERROR_INVALID_DATE_FIELD', 'start_date/end_date'));
                 return false;
             }
        } elseif (empty($this->start_date) || $this->start_date == $this->_db->getNullDate()){
             // Start date is required by TimelineJS in the frontend
             $this->setError(Text::_('COM_AUDATORIA_FIELD_START_DATE') . ' ' . Text::_('JLIB_FORM_VALIDATE_FIELD_REQUIRED'));
             return false;
        }
        
        if (empty($this->timeline_id) || (int) $this->timeline_id <= 0) {
            $this->setError(Text::_('COM_AUDATORIA_ERROR_ITEM_TIMELINE_ID_REQUIRED'));
            return false;
        }
        
        // Check asset_id (manejo básico, JTableAsset se encarga de la creación compleja)
        if (empty($this->asset_id)) {
             $this->asset_id = 0; // O manejarlo según las reglas de JTableAsset
        }


        return parent::check();
    }
}