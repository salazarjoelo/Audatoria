<?php
// Ubicación: site/src/Extension/AudatoriaComponent.php
namespace Joomla\Component\Audatoria\Site\Extension; // Namespace para el sitio

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\DispatcherFactoryInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Clase de extensión del componente Audatoria para el Sitio.
 */
class AudatoriaComponent extends MVCComponent
{
    /**
     * Constructor.
     *
     * @param   DispatcherFactoryInterface  $dispatcherFactory  La fábrica de despachadores.
     * @param   MVCFactoryInterface|null    $mvcFactory         La fábrica MVC.
     */
    public function __construct(DispatcherFactoryInterface $dispatcherFactory, ?MVCFactoryInterface $mvcFactory = null)
    {
        parent::__construct($dispatcherFactory, $mvcFactory);
    }

    // El método dispatch() es heredado y manejará la carga del controlador/vista del sitio.
}