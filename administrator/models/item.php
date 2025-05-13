<?php
// Ubicación: administrator/models/item.php
namespace Salazarjoelo\Component\Audatoria\Administrator\Model; // NAMESPACE CORREGIDO

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
// No es necesario 'use Joomla\Component\Audatoria\Administrator\Table\ItemTable;' aquí

class ItemModel extends AdminModel
{
    public $typeAlias = 'com_audatoria.item';

    public function getTable($type = 'Item', $prefix = 'Salazarjoelo\Component\Audatoria\Administrator\Table', $config = []) // Namespace de la tabla CORREGIDO
    {
        return parent::getTable($type, $prefix, $config);
    }

    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm(
            'com_audatoria.item',
            'item',
            ['control' => 'jform', 'load_data' => $loadData]
        );

        if (empty($form)) {
            return false;
        }
        return $form;
    }

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_audatoria.edit.item.data', []);

        if (empty($data)) {
            $data = $this->getItem();

            if ($this->getState($this->getName() . '.id') == 0) {
                $app = Factory::getApplication();
                // Set created_user_id for new items if not already set (e.g. from copy)
                if (!isset($data->created_user_id)) {
                    $data->created_user_id = $app->getIdentity()->id;
                }
                $timelineId = $app->input->getInt('filter_timeline_id', $app->getUserStateFromRequest($this->context . '.filter.timeline_id', 'filter_timeline_id', 0, 'int'));
                if ($timelineId && !isset($data->timeline_id)) { // Set timeline_id if creating from a filtered list
                    $data->timeline_id = $timelineId;
                }
            }
        }
        return $data;
    }

    protected function prepareTable(&$table) // Pasamos $table por referencia
    {
        $user = Factory::getApplication()->getIdentity();
        $date = Factory::getDate();

        if (empty($table->id)) { // Nuevo item
            if (empty($table->created_user_id)) { // Solo si no fue seteado (ej. al copiar)
                 $table->created_user_id = $user->id;
            }
            if (empty($table->created_time)) {
                $table->created_time = $date->toSql();
            }
        }

        $table->modified_user_id = $user->id;
        $table->modified_time = $date->toSql();
        
        // Las fechas del calendario (start_date, end_date) son manejadas por JForm/JTable
        // si el formato de la base de datos es DATETIME y el del formulario es 'Y-m-d H:i:s'.
        // Si no, necesitarías convertirlas aquí.
        // Ejemplo: if (!empty($table->start_date)) $table->start_date = Factory::getDate($table->start_date)->toSql();
    }

    public function save($data)
    {
        // AdminModel::save maneja la mayoría de la lógica, incluyendo prepareTable y reglas ACL si se configuran.
        return parent::save($data);
    }
}