<?php
// Ubicación: administrator/audatoria.php
\defined('_JEXEC') or die;

use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

/**
 * Punto de entrada principal para com_audatoria en el backend.
 */

// Opcional: Activar reporte de errores máximo SOLO para depuración
// if (Factory::getApplication()->get('debug') && Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_installer')) {
//     error_reporting(E_ALL);
//     ini_set('display_errors', 1);
// }

try {
    // Obtener el contenedor de servicios de Joomla
    $container = Factory::getContainer();

    // Solicitar la instancia del componente (esto activará el Service Provider si es necesario)
    // El tipo solicitado es ComponentInterface, y el contenedor devolverá la clase
    // registrada en provider.php (AudatoriaComponent).
    /** @var ComponentInterface $component */
    $component = $container->get(ComponentInterface::class);

    // Ejecutar el método dispatch del componente.
    // Este método (heredado de MVCComponent) manejará el enrutamiento
    // interno al controlador y tarea apropiados.
    $component->dispatch();

} catch (\Throwable $e) {
    // Capturar CUALQUIER error o excepción que ocurra durante la obtención o el despacho.

    // Registrar el error detallado en los logs de Joomla
    Log::add(
        sprintf(
            "Error Crítico al Despachar com_audatoria (Admin): %s en %s:%d\n%s",
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        ),
        Log::CRITICAL, // Usar CRITICAL para errores que impiden el funcionamiento
        'com_audatoria_dispatch_error' // Categoría de log específica
    );

    // Mostrar un mensaje de error apropiado al usuario
    $app = Factory::getApplication();
    if ($app->get('debug') && $app->getIdentity()->authorise('core.manage', 'com_installer')) {
        // Mensaje detallado para administradores en modo debug
        $error_message = '<strong>Error Crítico com_audatoria (Admin):</strong><br>'
            . 'Mensaje: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '<br>'
            . 'Archivo: ' . htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8') . ' (Línea: ' . $e->getLine() . ')<br>';
        if ($e->getPrevious()) {
            $prev = $e->getPrevious();
            $error_message .= 'Error Anterior: ' . htmlspecialchars($prev->getMessage(), ENT_QUOTES, 'UTF-8') . '<br>';
        }
        // Descomentar bajo tu propio riesgo para ver la traza completa en pantalla (¡NO en producción!)
        // $error_message .= '<pre>' . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8') . '</pre>';
        $app->enqueueMessage($error_message, 'error');

        // También puedes mostrar el error directamente si la plantilla lo permite
        // echo '<div class="alert alert-danger">' . $error_message . '</div>';

    } else {
        // Mensaje genérico para usuarios normales o modo no-debug
        $app->enqueueMessage(Joomla\CMS\Language\Text::_('COM_AUDATORIA_ERROR_UNEXPECTED_ADMIN'), 'error');
         // Asegúrate de definir esta clave de idioma:
         // COM_AUDATORIA_ERROR_UNEXPECTED_ADMIN="Ocurrió un error inesperado al cargar el componente. Por favor, contacta al administrador."
    }

    // Opcional: Redirigir a un lugar seguro si el error es irrecuperable
    // if (!$app->isAdmin()) { // Ejemplo: Si no estamos en admin, redirigir al inicio
    //     $app->redirect(Joomla\CMS\Router\Route::_('index.php'));
    // }
}