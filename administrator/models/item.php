<?php
// Ubicación: administrator/models/item.php
namespace Joomla\Component\Audatoria\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
use Joomla\Component\Audatoria\Administrator\Table\ItemTable; // Usar la clase Table

class ItemModel extends AdminModel
{
    public $typeAlias = 'com_audatoria.item';

    public function getTable($type = 'Item', $prefix = 'Joomla\\Component\\Audatoria\\Administrator\\Table', $config = [])
    {
        return parent::getTable('Item', $prefix, $config);
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

        // Modificar dinámicamente el campo timeline_id si es necesario
        // Por ejemplo, para filtrar timelines por permisos o estado
        // $user = Factory::getApplication()->getIdentity();
        // if (!$user->authorise('core.edit', 'com_audatoria')) {
        //    $form->setFieldAttribute('timeline_id', 'query', 'SELECT id AS value, title AS text FROM #__audatoria_timelines WHERE created_user_id = ' . (int) $user->id);
        // }

        return $form;
    }

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_audatoria.edit.item.data', []);

        if (empty($data)) {
            $data = $this->getItem();

            // Valores por defecto para un nuevo item
            if ($this->getState($this->getName() . '.id') == 0) {
                $app = Factory::getApplication();
                if (isset($data->created_user_id)) {
                    $data->created_user_id = $app->getIdentity()->id;
                }
                // Obtener timeline_id del filtro si está presente (útil al crear desde la lista de ítems filtrada por timeline)
                $timelineId = $app->input->getInt('filter_timeline_id', $app->getUserStateFromRequest($this->context . '.filter.timeline_id', 'filter_timeline_id', 0, 'int'));
                if ($timelineId) {
                    $data->timeline_id = $timelineId;
                }
            }
        }
        
        // Para nuevos ítems, asegurar que created_user_id está seteado
        if ($this->getState($this->getName() . '.id') == 0) {
            $user = Factory::getApplication()->getIdentity();
             if (!isset($data->created_user_id)) {
                 $data->created_user_id = $user->id;
            }
        }

        return $data;
    }

    protected function prepareTable($table, $data)
    {
        $user = Factory::getApplication()->getIdentity();
        $date = Factory::getDate();

        if (empty($table->id)) {
            if (empty($data['created_user_id'])) {
                 $table->created_user_id = $user->id;
            }
            if (empty($data['created_time'])) {
                $table->created_time = $date->toSql();
            }
        }

        $table->modified_user_id = $user->id;
        $table->modified_time = $date->toSql();
        
        // Convertir fechas del calendario a formato SQL si es necesario (AdminModel a veces lo maneja)
        // Si 'start_date' o 'end_date' vienen en un formato que necesita conversión:
        // if (!empty($data['start_date'])) {
        //     $table->start_date = Factory::getDate($data['start_date'])->toSql();
        // }
        // if (!empty($data['end_date'])) {
        //     $table->end_date = Factory::getDate($data['end_date'])->toSql();
        // }
    }

    public function save($data)
	{
		$table = $this->getTable();
		$pk    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;

		// Load the row if saving an existing record.
		if ($pk > 0)
		{
			$table->load($pk);
			$isNew = false;
		}

		// Bind the data.
		if (!$table->bind($data))
		{
			$this->setError($table->getError());
			return false;
		}

		// Check the data.
		if (!$table->check())
		{
			$this->setError($table->getError());
			return false;
		}

		// Store the data.
		if (!$table->store())
		{
			$this->setError($table->getError());
			return false;
		}

		$this->setState($this->getName() . '.id', $table->id);

		$this->cleanCache();
		return true;
	}
}