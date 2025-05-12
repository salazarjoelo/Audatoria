<?php
// Ubicación: administrator/src/Extension/AudatoriaComponent.php
namespace Joomla\Component\Audatoria\Administrator\Extension;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\DispatcherFactoryInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Factory; // Solo si necesitas usar Factory directamente aquí
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Clase de extensión del componente Audatoria para el Administrador.
 */
class AudatoriaComponent extends MVCComponent
{
    /**
     * Constructor.
     * Se asegura de que las fábricas necesarias sean recibidas.
     *
     * @param   DispatcherFactoryInterface  $dispatcherFactory  La fábrica de despachadores.
     * @param   MVCFactoryInterface|null    $mvcFactory         La fábrica MVC (puede ser null en casos raros, aunque no debería en J5).
     */
    public function __construct(DispatcherFactoryInterface $dispatcherFactory, ?MVCFactoryInterface $mvcFactory = null)
    {
        // Llama al constructor padre pasando las fábricas.
        // MVCComponent se encarga de almacenarlas y usarlas en dispatch().
        parent::__construct($dispatcherFactory, $mvcFactory);

        // Puedes establecer el nombre base para buscar controladores/modelos/vistas
        // si difiere del nombre del componente (ej. 'Audatoria').
        // Por defecto, MVCComponent intenta derivarlo del namespace.
        // $this->base_path = __DIR__ . '/../'; // Opcional si la estructura no es estándar
        // $this->namespace = 'Joomla\\Component\\Audatoria\\Administrator'; // Opcional
    }

    // El método `dispatch()` es heredado de MVCComponent.
    // No necesitas redefinirlo a menos que quieras añadir lógica
    // personalizada antes o después del despacho MVC estándar.
    // public function dispatch(): void
    // {
    //     // Lógica personalizada antes del dispatch
    //     parent::dispatch();
    //     // Lógica personalizada después del dispatch
    // }
}