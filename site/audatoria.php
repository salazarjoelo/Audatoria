<?php
// Ubicación: site/audatoria.php
\defined('_JEXEC') or die;

use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

/**
 * Punto de entrada principal para com_audatoria en el frontend.
 */

try {
    // Obtener el contenedor de servicios
    $container = Factory::getContainer();

    // Obtener la instancia del componente del sitio (usará site/services/provider.php)
    /** @var ComponentInterface $component */
    $component = $container->get(ComponentInterface::class);

    // Despachar la solicitud al controlador/vista apropiado del sitio
    $component->dispatch();

} catch (\Throwable $e) {
    // Capturar cualquier error durante la carga o despacho del componente del sitio

    Log::add(
        sprintf(
            "Error Crítico al Despachar com_audatoria (Sitio): %s en %s:%d\n%s",
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        ),
        Log::CRITICAL,
        'com_audatoria_dispatch_error_site'
    );

    // Mostrar un mensaje de error apropiado
    $app = Factory::getApplication();
    if ($app->get('debug') && $app->getIdentity()->authorise('core.manage', 'com_installer')) {
        // Mensaje detallado para administradores en modo debug
        $error_message = '<strong>Error Crítico com_audatoria (Sitio):</strong><br>'
            . 'Mensaje: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '<br>'
            . 'Archivo: ' . htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8') . ' (Línea: ' . $e->getLine() . ')<br>';
        // Descomentar con precaución para ver traza:
        // $error_message .= '<pre>' . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8') . '</pre>';
        // Mostrar el error directamente en el sitio puede romper el layout.
        // Considera solo loguearlo o mostrar un mensaje genérico.
        echo '<div class="alert alert-danger">' . $error_message . '</div>';
        // O usar JError (aunque es más antiguo)
        // \JError::raiseError(500, $error_message);

    } else {
        // Mensaje genérico. Puedes mostrarlo en la plantilla si lo capturas.
        // O redirigir a una página de error.
        // Por simplicidad, podríamos no mostrar nada o un mensaje simple.
        // echo '<div class="alert alert-danger">'.Text::_('COM_AUDATORIA_ERROR_UNEXPECTED_SITE').'</div>';
        // O simplemente loguearlo y mostrar una página en blanco o redirigir.
        // header("HTTP/1.1 500 Internal Server Error"); // Opcional
        echo "Se produjo un error al cargar el contenido."; // Mensaje muy genérico
    }

    // Detener la ejecución normal si ocurre un error crítico aquí
    return; // O exit;
}