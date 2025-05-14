<?php
// Ubicación: administrator/models/channel.php
namespace Salazarjoelo\Component\Audatoria\Administrator\Model; // NAMESPACE CORREGIDO

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
// No es necesario 'use Joomla\Component\Audatoria\Administrator\Table\ChannelTable;' explícitamente
// si el prefijo en getTable es correcto y la clase Table está en el namespace correcto.

class ChannelModel extends AdminModel
{
    public $typeAlias = 'com_audatoria.channel';

    public function getTable($type = 'Channel', $prefix = 'Salazarjoelo\Component\Audatoria\Administrator\Table', $config = []) // Namespace de la tabla CORREGIDO
    {
        return parent::getTable($type, $prefix, $config);
    }

    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm(
            'com_audatoria.channel',
            'channel',
            ['control' => 'jform', 'load_data' => $loadData]
        );

        if (empty($form)) {
            return false;
        }
        return $form;
    }

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_audatoria.edit.channel.data', []);

        if (empty($data)) {
            $data = $this->getItem();
            if ($this->getState($this->getName() . '.id') == 0) {
                 if (!isset($data->state)) { // Habilitado por defecto para nuevos items
                     $data->state = 1;
                 }
            }
        }
        return $data;
    }
    
    protected function prepareTable(&$table) // Pasamos $table por referencia
    {
        $date = Factory::getDate();
        $user = Factory::getApplication()->getIdentity();

        // For new records, set the created time and user if not set.
        if (!$table->id) {
            if (empty($table->created_time)) {
                $table->created_time = $date->toSql();
            }
             // No seteamos created_user_id aquí, usualmente se maneja en FormModel o AdminModel al obtener el item
             // o al validar el formulario si el campo está presente.
        }

        // Set the modified time and user
        $table->modified_time = $date->toSql();
        // $table->modified_user_id = $user->id; // AdminModel se encarga de esto si es necesario.

        // state (enabled) en la tabla channel se llama 'state', no 'enabled'.
        // El form usa 'enabled' como nombre de campo, así que el bind debería manejarlo si el nombre coincide.
        // Si el campo en la tabla es 'enabled', entonces está bien. Si es 'state', el XML del form debe usar 'state'.
        // Viendo tu form channel.xml, usa <field name="enabled"...> pero la tabla channel usa `state` para enabled/disabled.
        // Esto debe ser consistente o mapeado en el bind/save.
        // Asumiré que $table->bind($data) mapeará 'enabled' del form a 'state' en la tabla si
        // el XML del formulario tiene el campo <field name="state" ... type="radio" ...> y no <field name="enabled"...>
        // O que la tabla tiene una propiedad `enabled`.
        // Tu tabla `#__audatoria_channels` usa `state TINYINT NOT NULL DEFAULT 0 COMMENT '1 = enabled for import, 0 = disabled.'`
        // Tu `channel.xml` del formulario usa `<field name="enabled" type="radio"...>`
        // Esta es una inconsistencia. Para que `bind` funcione directamente, el nombre del campo XML
        // debe ser `state`. O debes mapearlo manualmente.
        // $table->state = $table->enabled; // Si el bind ya puso 'enabled' en la tabla y necesitas moverlo a 'state'.
    }


    public function save($data)
    {
        $table = $this->getTable();
        $pk    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
        $isNew = true;

        if ($pk > 0) {
            $table->load($pk);
            $isNew = false;
        }

         // El campo del formulario es 'enabled', pero el de la tabla es 'state'.
         // Mapear manualmente antes del bind.
         if (isset($data['enabled'])) {
             $data['state'] = (int) $data['enabled'];
             unset($data['enabled']); // Para evitar que bind intente encontrar un campo 'enabled' en la tabla
         }


        if (!$table->bind($data)) {
            $this->setError($table->getError());
            return false;
        }
        
        // prepareTable se llama internamente por AdminModel::save()
        // $this->prepareTable($table); // No llamar directamente aquí si AdminModel lo hace.

        if (!$table->check()) {
            $this->setError($table->getError());
            return false;
        }

        if (!$table->store()) {
            $this->setError($table->getError());
            return false;
        }

        $this->setState($this->getName() . '.id', $table->id);
        $this->cleanCache();
        return true;
    }

     /**
      * Method to change the enabled state of one or more records.
      *
      * @param   array    $pks    A list of the primary keys to change.
      * @param   integer  $value  The value of the enabled state.
      *
      * @return  boolean  True on success.
      */
     public function enable(array $pks, int $value = 1): bool
     {
         return $this->publish($pks, $value); // Reutiliza el método publish de AdminModel que cambia 'state'
     }

     /**
      * Method to change the disabled state of one or more records.
      *
      * @param   array    $pks    A list of the primary keys to change.
      * @param   integer  $value  The value of the disabled state.
      *
      * @return  boolean  True on success.
      */
     public function disable(array $pks, int $value = 0): bool
     {
         return $this->publish($pks, $value); // Reutiliza el método publish de AdminModel que cambia 'state'
     }
}