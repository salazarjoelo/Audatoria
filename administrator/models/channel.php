<?php
// Ubicación: administrator/models/channel.php
namespace Joomla\Component\Audatoria\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
use Joomla\Component\Audatoria\Administrator\Table\ChannelTable; // Usar la clase Table

class ChannelModel extends AdminModel
{
    public $typeAlias = 'com_audatoria.channel';

    public function getTable($type = 'Channel', $prefix = 'Joomla\\Component\\Audatoria\\Administrator\\Table', $config = [])
    {
        return parent::getTable('Channel', $prefix, $config);
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
            // Valores por defecto para un nuevo canal
            if ($this->getState($this->getName() . '.id') == 0) {
                 // $data->state = 1; // Habilitado por defecto
            }
        }
        return $data;
    }
    
    protected function prepareTable($table, $data)
    {
        $date = Factory::getDate();

        if (empty($table->id)) {
            if (empty($data['created_time'])) { // Establecer fecha de creación si no está
                $table->created_time = $date->toSql();
            }
        }
        $table->modified_time = $date->toSql();

        // Si 'state' viene de un radio y no como numérico, ajustarlo (aunque el form field debería manejarlo)
        if (isset($data['state'])) {
            $table->state = (int)$data['state'];
        }
    }

    public function save($data)
	{
		$table = $this->getTable();
		$pk    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;

		if ($pk > 0)
		{
			$table->load($pk);
			$isNew = false;
		}

		if (!$table->bind($data))
		{
			$this->setError($table->getError());
			return false;
		}

		if (!$table->check())
		{
			$this->setError($table->getError());
			return false;
		}

		if (!$table->store())
		{
			$this->setError($table->getError());
			return false;
		}

		$this->setState($this->getName() . '.id', $table->id);
		$this->cleanCache();
		return true;
	}

    // Si tienes lógica de importación que se activa desde el modelo:
    // public function importVideos($channelId)
    // {
    //     // Lógica para llamar al script CLI o ejecutar la importación directamente
    //     // ...
    //     return ['success' => true, 'count' => 0, 'message' => 'Importación iniciada...'];
    // }
}