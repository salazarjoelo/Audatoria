<?php
namespace Salazarjoelo\Component\Audatoria\Administrator\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Extension\MVCComponent;
// No es necesario importar DispatcherFactoryInterface ni MVCFactoryInterface aquí
// si no sobrescribes el constructor para usarlos explícitamente.

class AudatoriaComponent extends MVCComponent
{
    // El constructor por defecto de MVCComponent es generalmente suficiente.
    // Este se encarga de la configuración básica del componente usando las factorías
    // que se le pasan (o que obtiene del contenedor) y el namespace base.
}