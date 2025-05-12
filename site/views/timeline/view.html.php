<?php
// Ubicación: site/views/timeline/view.html.php
namespace Joomla\Component\Audatoria\Site\View\Timeline; // Namespace correcto

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Document\Document; // Para el WebAssetManager
use Joomla\CMS\Component\ComponentHelper; // Para obtener parámetros

class TimelineView extends BaseHtmlView // Renombrar clase
{
    protected ?object $timeline = null; // Permitir que sea null inicialmente
    protected array $items = [];
    protected ?object $params = null; // Para parámetros del componente/menú

    /**
     * Método Display.
     * Prepara los datos y carga los assets necesarios para la vista Timeline.
     *
     * @param   string  $tpl  El sufijo de la plantilla a usar.
     *
     * @return  void
     */
    public function display($tpl = null): void
    {
        $app = Factory::getApplication();
        $doc = $app->getDocument();
        $wa  = $doc->getWebAssetManager();

        // Obtener el ID de la timeline desde la solicitud
        // Podría venir de la URL directamente o de un parámetro de menú
        $id = $app->input->getInt('id', $app->getParams()->get('timeline_id', 0));

        if ($id <= 0) {
            // Si no hay ID, ¿qué mostrar? ¿Una lista? ¿Un error?
            // Por ahora, lanzaremos un error 404.
            throw new \Exception(Text::_('COM_AUDATORIA_ERROR_TIMELINE_ID_MISSING'), 404);
        }

        // Obtener el modelo
        try {
            /** @var \Joomla\Component\Audatoria\Site\Model\TimelineModel $model */
            $model = $this->getModel();
        } catch (\Exception $e) {
            Log::add('Error al obtener el modelo Timeline: ' . $e->getMessage(), Log::ERROR, 'com_audatoria');
            $app->enqueueMessage(Text::_('COM_AUDATORIA_ERROR_MODEL_NOT_LOADED_FRONTEND'), 'error');
            $this->timeline = null;
            $this->items = [];
            parent::display($tpl); // Mostrar plantilla con datos vacíos/error
            return;
        }


        // Cargar datos de la timeline y sus items
        $this->timeline = $model->getTimeline($id);

        if (!$this->timeline) {
            // Timeline no encontrada, no publicada, sin acceso o idioma incorrecto.
             Log::add('Timeline no encontrada o acceso denegado. ID: ' . $id, Log::WARNING, 'com_audatoria');
             throw new \Exception(Text::_('COM_AUDATORIA_ERROR_TIMELINE_NOT_FOUND_OR_ACCESS_DENIED'), 404);
             // Alternativamente, mostrar un mensaje en la plantilla en lugar de error 404:
             // $app->enqueueMessage(Text::_('COM_AUDATORIA_ERROR_TIMELINE_NOT_FOUND_OR_ACCESS_DENIED'), 'warning');
             // $this->items = [];
        } else {
            // Cargar items solo si la timeline existe
            $this->items = $model->getItems($this->timeline->id);
            // Aquí podrías también cargar parámetros específicos de esta timeline si los tienes
            // $this->params = new \Joomla\Registry\Registry($this->timeline->params);
        }

        // Cargar parámetros globales del componente
        $this->params = $app->getParams('com_audatoria');


        // Cargar Assets de TimelineJS usando WebAssetManager
        // Asegúrate que el WebAssetManager está disponible (lo está en Joomla 4/5 por defecto)
        if ($wa) {
            // Registrar y usar el JS y CSS de TimelineJS desde un CDN
            // Es mejor registrar primero y luego usar por si necesitas dependencias.
            $wa->registerScript('timeline3-js', 'https://cdn.knightlab.com/libs/timeline3/latest/js/timeline.js', [], ['version' => 'auto'], []);
            $wa->registerStyle('timeline3-css', 'https://cdn.knightlab.com/libs/timeline3/latest/css/timeline.css', [], ['version' => 'auto'], []);

            $wa->useScript('timeline3-js');
            $wa->useStyle('timeline3-css');

            // Podrías añadir tu propio JS/CSS aquí si lo necesitas
            // $wa->registerAndUseScript('com_audatoria.timeline', 'media/com_audatoria/js/timeline_init.js', ['timeline3-js'], ['version' => 'auto', 'defer' => true], []);
        } else {
            // Fallback si WA no está disponible (muy improbable en J5)
            $doc->addScript('https://cdn.knightlab.com/libs/timeline3/latest/js/timeline.js');
            $doc->addStyleSheet('https://cdn.knightlab.com/libs/timeline3/latest/css/timeline.css');
        }
        
        // Preparar la página (ej. título del navegador)
        if ($this->timeline) {
             $pageTitle = $this->timeline->title;
             $this->document->setTitle($pageTitle);
             // Añadir metadatos si es relevante
             if (!empty($this->timeline->description)) {
                  $this->document->setDescription($this->escape(strip_tags($this->timeline->description)));
             }
        }


        // Comprobar errores de la vista
        if (count($errors = $this->get('Errors'))) {
            Log::add('Errores en TimelineView: ' . implode("\n", $errors), Log::ERROR, 'com_audatoria');
            // Manejar el error, quizás mostrando un mensaje
        }

        parent::display($tpl);
    }
}