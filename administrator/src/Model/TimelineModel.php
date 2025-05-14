<?php
namespace Salazarjoelo\Component\Audatoria\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Date\Date; // Para Factory::getApplication()->getDate()

class TimelineModel extends AdminModel
{
    public $typeAlias = 'com_audatoria.timeline';

    public function getTable($name = 'Timeline', $prefix = 'Salazarjoelo\\Component\\Audatoria\\Administrator\\Table', $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }

    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm(
            'com_audatoria.timeline',
            'timeline',
            ['control' => 'jform', 'load_data' => $loadData]
        );

        if (empty($form)) {
            $this->setError(Text::_('COM_AUDATORIA_ERROR_FORM_NOT_LOADED'));
            return false;
        }
        return $form;
    }

    protected function loadFormData()
    {
        $app  = Factory::getApplication();
        $data = $app->getUserState('com_audatoria.edit.timeline.data', []);

        if (empty($data)) {
            $data = $this->getItem();
            if ($this->getState($this->getName() . '.id') == 0) {
                if (!isset($data->created_user_id)) {
                    $data->created_user_id = $app->getIdentity()->id; // CORREGIDO
                }
                if (!isset($data->state)) {
                     $data->state = 1;
                }
            }
        }
        return $data;
    }

    protected function prepareTable($table)
    {
        $app  = Factory::getApplication();
        $user = $app->getIdentity(); // CORREGIDO
        $date = $app->getDate();   // CORREGIDO

        if (empty($table->id)) { 
            if (empty($table->created_user_id)) {
                 $table->created_user_id = $user->id;
            }
            if (empty($table->created_time) || $table->created_time == $this->getDbo()->getNullDate()) {
                $table->created_time = $date->toSql();
            }
             if (property_exists($table, 'alias') && empty($table->alias) && !empty($table->title)) {
                 $table->alias = ApplicationHelper::stringURLSafe($table->title);
             }
             if (property_exists($table, 'alias') && empty($table->alias)) {
                 $table->alias = ApplicationHelper::stringURLSafe($date->format('Y-m-d-H-i-s'));
             }
        } else { 
             if (property_exists($table, 'alias') && empty($table->alias) && !empty($table->title)) {
                 $table->alias = ApplicationHelper::stringURLSafe($table->title);
             }
        }
         if (property_exists($table, 'alias') && method_exists($table, 'generateUniqueAlias')) {
              $table->alias = $table->generateUniqueAlias($table->alias, $table->id);
         }

        $table->modified_user_id = $user->id;
        $table->modified_time = $date->toSql();
    }
    
    public function save($data)
    {
        return parent::save($data);
    }

    public function validate($form, $data, $group = null)
    {
        $validatedData = parent::validate($form, $data, $group);
        if ($validatedData === false) {
            return false;
        }
        return $validatedData;
    }
}