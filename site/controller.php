<?php
// Ubicación: site/controller.php
namespace Joomla\Component\Audatoria\Site\Controller; // Namespace corregido

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Controlador principal (fallback) para el componente Audatoria en el sitio.
 */
class BaseController extends BaseController // Renombrar la clase
{
    /**
     * La vista por defecto para este componente si no se especifica ninguna.
     *
     * @var string
     */
    protected $default_view = 'timeline'; // O cambia a una vista de lista si la creas
}