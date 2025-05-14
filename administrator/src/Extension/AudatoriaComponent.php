<?php
namespace Salazarjoelo\Component\Audatoria\Administrator\Extension; // Namespace Correcto

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\DispatcherFactoryInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

class AudatoriaComponent extends MVCComponent
{
    /**
     * Constructor.
     *
     * @param   DispatcherFactoryInterface  $dispatcherFactory  The dispatcher factory.
     * @param   MVCFactoryInterface|null    $mvcFactory         The MVC factory.
     */
    public function __construct(DispatcherFactoryInterface $dispatcherFactory, ?MVCFactoryInterface $mvcFactory = null)
    {
        // El namespace base para controladores, modelos, vistas se inferirá como
        // Salazarjoelo\Component\Audatoria\Administrator
        // a partir del namespace de esta clase.
        parent::__construct($dispatcherFactory, $mvcFactory);
    }
}