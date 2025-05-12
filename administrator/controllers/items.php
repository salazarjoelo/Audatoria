<?php
// Ubicación: administrator/controllers/items.php
namespace Joomla\Component\Audatoria\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Factory;

class ItemsController extends AdminController
{
    protected $view_list = 'items';

    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    public function getModel($name = 'Item', $prefix = 'Joomla\\Component\\Audatoria\\Administrator\\Model', $config = ['ignore_request' => true])
    {
        if (empty($name)) {
            $name = 'Item'; // Para acciones de lote, usa el modelo singular
        }
        return parent::getModel($name, $prefix, $config);
    }
}