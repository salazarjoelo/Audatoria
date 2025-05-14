<?php
// Ubicación: administrator/models/timeline.php
namespace Salazarjoelo\Component\Audatoria\Administrator\Model; // NAMESPACE CORREGIDO

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
// No es necesario 'use Joomla\Component\Audatoria\Administrator\Table\TimelineTable;' aquí

class TimelineModel extends AdminModel
{
    public $typeAlias = 'com_audatoria.timeline';

    public function getTable($type = 'Timeline', $prefix = 'Salazarjoelo\Component\Audatoria\Administrator\Table', $config = []) // Namespace de la tabla CORREGIDO
    {
        return parent::getTable($type, $prefix, $config);
    }

    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm(
            'com_audatoria.timeline',
            'timeline',
            ['control' => 'jform', 'load_data' => $loadData]
        );

        if (empty($form)) {
            return false;
        }
        return $form;
    }

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_audatoria.edit.timeline.data', []);

        if (empty($data)) {
            $data = $this->getItem();
             if ($this->getState($this->getName() . '.id') == 0) { // Nuevo item
                 if (!isset($data->created_user_id)) {
                     $data->created_user_id = Factory::getApplication()->getIdentity()->id;
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
            if (empty($table->created_user_id)) {
                 $table->created_user_id = $user->id;
            }
            if (empty($table->created_time)) {
                $table->created_time = $date->toSql();
            }
            // Generar alias si está vacío
             if (property_exists($table, 'alias') && empty($table->alias) && !empty($table->title)) {
                 $table->alias = ApplicationHelper::stringURLSafe($table->title);
             }
             if (property_exists($table, 'alias') && empty($table->alias)) {
                 $table->alias = ApplicationHelper::stringURLSafe($date->format('Y-m-d-H-i-s'));
             }
        } else { // Item existente
             if (property_exists($table, 'alias') && empty($table->alias) && !empty($table->title)) {
                 $table->alias = ApplicationHelper::stringURLSafe($table->title);
             }
        }
         // Asegurar alias único si la tabla tiene el método (lo añadimos a la tabla)
         if (property_exists($table, 'alias') && method_exists($table, 'generateUniqueAlias')) {
              $table->alias = $table->generateUniqueAlias($table->alias, $table->id);
         }


        $table->modified_user_id = $user->id;
        $table->modified_time = $date->toSql();
    }
    
    public function save($data)
    {
        // AdminModel::save maneja la mayoría de la lógica
        return parent::save($data);
    }
}