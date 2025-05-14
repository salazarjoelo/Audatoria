<?php
// Ubicación: administrator/controllers/items.php
namespace Salazarjoelo\Component\Audatoria\Administrator\Controller; // NAMESPACE CORREGIDO

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
// No necesitas Factory aquí si no lo usas directamente.

class ItemsController extends AdminController
{
    protected $view_list = 'items';

    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    public function getModel($name = 'Item', $prefix = 'Salazarjoelo\Component\Audatoria\Administrator\Model', $config = ['ignore_request' => true]) // Namespace del modelo CORREGIDO
    {
        // Para acciones de lote, AdminController usa el modelo singular.
        return parent::getModel($name, $prefix, $config);
    }
}