<?php
// Ubicación: administrator/models/timeline.php
namespace Joomla\Component\Audatoria\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\Component\Audatoria\Administrator\Table\TimelineTable; // Usar la clase Table con namespace

class TimelineModel extends AdminModel
{
    /**
     * El prefijo para cargar el idioma.
     *
     * @var    string
     * @since  1.6
     */
    public $typeAlias = 'com_audatoria.timeline'; // Ayuda a cargar archivos de idioma y layouts

    /**
     * Método para obtener una instancia de tabla, creando una si es necesario.
     *
     * @param   string $type    El tipo de tabla a instanciar. Opcional.
     * @param   string $prefix  Un prefijo para el nombre de clase. Opcional.
     * @param   array  $config  Array de configuración. Opcional.
     *
     * @return  TimelineTable|false La instancia de la tabla o false si hay error.
     */
    public function getTable($type = 'Timeline', $prefix = 'Joomla\\Component\\Audatoria\\Administrator\\Table', $config = [])
    {
        // Asegurarse que el $type es 'Timeline' para cargar nuestra tabla específica
        return parent::getTable('Timeline', $prefix, $config);
    }

    /**
     * Método para obtener el formulario.
     *
     * @param   array   $data      Datos para el formulario.
     * @param   boolean $loadData  True si el formulario debe cargar sus datos (por defecto), false si no.
     *
     * @return  \Joomla\CMS\Form\Form|false  Objeto Form en éxito, false en error.
     */
    public function getForm($data = [], $loadData = true)
    {
        // Obtener el formulario.
        $form = $this->loadForm(
            'com_audatoria.timeline', // Nombre único del formulario (contexto.nombre)
            'timeline',               // Nombre del archivo XML del formulario (sin .xml)
            ['control' => 'jform', 'load_data' => $loadData]
        );

        if (empty($form)) {
            return false;
        }
        
        // En Joomla 5, para formularios con campos de 'rules', a veces es necesario forzar
        // la carga de reglas de campos si no se hace automáticamente.
        // $this->preprocessForm($form, $data); // Si tienes un método preprocessForm personalizado

        return $form;
    }

    /**
     * Método para obtener los datos que deben ser cargados en el formulario.
     *
     * @return  mixed  Los datos para el formulario.
     */
    protected function loadFormData()
    {
        // Comprobar la sesión para datos previamente introducidos.
        $data = Factory::getApplication()->getUserState('com_audatoria.edit.timeline.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        // Para nuevos ítems, asegurar que created_user_id está seteado
        if ($this->getState($this->getName() . '.id') == 0) {
            $user = Factory::getApplication()->getIdentity();
            if (!isset($data->created_user_id)) { // Prevenir sobreescribir si ya existe (ej. copiando)
                 $data->created_user_id = $user->id;
            }
        }

        return $data;
    }

    /**
     * Prepara y limpia los datos antes de guardarlos.
     *
     * @param   TimelineTable  $table  Una instancia de JTable.
     * @param   array          $data   Un array con los datos del formulario.
     *
     * @return  array  El array de datos saneados.
     *
     * @since   1.0.0
     */
    protected function prepareTable($table, $data)
    {
        // Establecer el user ID del creador si es un nuevo ítem.
        if (empty($table->id)) {
            $user = Factory::getApplication()->getIdentity();
            if (empty($data['created_user_id'])) { // Solo si no se ha seteado explícitamente
                $table->created_user_id = $user->id;
            }
             if (empty($data['created_time'])) { // Establecer fecha de creación si no está
                $table->created_time = Factory::getDate()->toSql();
            }
        }

        // Establecer el user ID y fecha de modificación.
        $user = Factory::getApplication()->getIdentity();
        $table->modified_user_id = $user->id;
        $table->modified_time = Factory::getDate()->toSql();
        
        // Generar alias si está vacío
        if (empty($data['alias']) && !empty($data['title'])) {
             $data['alias'] = ApplicationHelper::stringURLSafe($data['title']);
        }
        if (empty($data['alias'])) { // Si sigue vacío (ej. solo símbolos en el título)
            $data['alias'] = ApplicationHelper::stringURLSafe(Factory::getDate()->format('Y-m-d-H-i-s'));
        } else {
            $data['alias'] = ApplicationHelper::stringURLSafe($data['alias']);
        }


        // Los campos como 'asset_id' y 'params' se manejan a menudo en el método save() o
        // mediante comportamientos de tabla (observers).

        return $data; // $data se pasará al método bind() de la tabla.
    }
    
    /**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
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

        // Ajustar el contexto para las reglas ACL
        $assetParentId = $this->getAssetParentId($table, $data);
        if ($isNew && $assetParentId) {
            // No estoy seguro si esto sigue siendo necesario explícitamente en J5
            // $data['asset_id'] = $this->getAssetId($table->asset_id, $assetParentId);
        }


        // Manejo de alias
        if (property_exists($table, 'alias') && empty($data['alias']) && !empty($data['title'])) {
            $data['alias'] = \Joomla\CMS\Application\ApplicationHelper::stringURLSafe($data['title']);
        }
        if (property_exists($table, 'alias') && empty($data['alias'])) {
             $data['alias'] = \Joomla\CMS\Application\ApplicationHelper::stringURLSafe(\Joomla\CMS\Date\Date::getInstance()->format('Y-m-d-H-i-s'));
        }
        if(property_exists($table, 'alias')) {
            // Bind los datos al objeto tabla
            $data['alias'] = $table->generateUniqueAlias($data['alias'], $table->id);
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

        // Actualizar el asset_id en la sesión si es un nuevo ítem
        if (isset($data['asset_id'])) {
            $this->setState($this->getName() . '.newasset_id', $data['asset_id']);
        }


		$this->setState($this->getName() . '.id', $table->id);


		// Limpiar la caché
		$this->cleanCache();

		return true;
	}
}