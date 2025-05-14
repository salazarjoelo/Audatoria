<?php
namespace Salazarjoelo\Component\Audatoria\Site\View\Timeline;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
// use Joomla\CMS\Document\Document; // $this->document ya está disponible
use Joomla\CMS\Component\ComponentHelper; // Para obtener parámetros globales del componente
use Joomla\CMS\Log\Log;
use Joomla\Registry\Registry; // Para los parámetros

class HtmlView extends BaseHtmlView
{
    protected ?object $timeline = null;
    protected array $items = [];
    protected ?Registry $params = null;      // Parámetros del componente/menú
    protected ?Registry $pageParams = null;  // Parámetros específicos del timeline (si existen en la BD)
    protected ?string $error = null;

    public function display($tpl = null): void
    {
        $app = Factory::getApplication();

        // Obtener el ID de la timeline. El ítem de menú debería proveerlo.
        // Si no hay ítem de menú, $app->getParams() podría no tener 'id'.
        // El XML del ítem de menú (default.xml) ahora tiene un campo 'id' para seleccionar la timeline.
        // JInput getInt('id') tomará el 'id' de la URL (si se pasa directamente, menos común)
        // o del request que el ítem de menú establece.
        $menuParams = $app->getParams();
        $id = $app->input->getInt('id', $menuParams->get('id', 0));

        // Parámetros globales del componente
        $this->params = ComponentHelper::getParams('com_audatoria');

        if ($id <= 0) {
            $this->error = Text::_('COM_AUDATORIA_ERROR_TIMELINE_ID_MISSING');
            Log::add($this->error, Log::WARNING, 'com_audatoria');
        } else {
            try {
                /** @var \Salazarjoelo\Component\Audatoria\Site\Model\TimelineModel $model */
                $model = $this->getModel(); // Joomla encontrará el modelo TimelineModel
                if (!$model) {
                     throw new \RuntimeException('Modelo Timeline (Sitio) no pudo ser cargado.');
                }
            } catch (\Exception $e) {
                Log::add('Error al obtener el modelo Timeline (Sitio): ' . $e->getMessage(), Log::ERROR, 'com_audatoria');
                $this->error = Text::_('COM_AUDATORIA_ERROR_MODEL_NOT_LOADED_FRONTEND');
                $this->prepareDocument(); // Preparar documento aunque haya error
                parent::display($tpl);
                return;
            }

            $this->timeline = $model->getTimeline($id);

            if (!$this->timeline) {
                $this->error = Text::_('COM_AUDATORIA_ERROR_TIMELINE_NOT_FOUND_OR_ACCESS_DENIED');
                Log::add('Timeline (Sitio) no encontrada o acceso denegado. ID: ' . $id, Log::WARNING, 'com_audatoria');
            } else {
                $this->items = $model->getItems($this->timeline->id);
                // Cargar parámetros específicos del timeline si están en $this->timeline->params
                if (!empty($this->timeline->params)) {
                    $this->pageParams = new Registry($this->timeline->params);
                }
            }
        }
        
        $this->prepareDocument();
        $this->loadAssets();

        // Comprobar errores de la vista padre (menos común si no los generas explícitamente)
        if (count($errors = $this->get('Errors'))) {
            Log::add('Errores heredados en Site/TimelineView: ' . implode("\n", $errors), Log::ERROR, 'com_audatoria');
            // Podrías añadir estos errores a $this->error o encolarlos
        }

        parent::display($tpl);
    }

    /**
     * Prepara el documento (título, metadatos).
     */
    protected function prepareDocument(): void
    {
        $app = Factory::getApplication();
        $doc = $this->document; // $this->document está disponible en HtmlView

        if ($this->error) {
            $doc->setTitle($this->escape(Text::_('COM_AUDATORIA_ERROR_PAGE_TITLE'))); // Necesitas esta constante
            // Podrías querer noindex en caso de error
            // $doc->setMetadata('robots', 'noindex, follow');
        } elseif ($this->timeline) {
            $pageTitle = $this->timeline->title;
            $siteName  = $app->get('sitename');
            $doc->setTitle($this->escape($pageTitle) . ' - ' . $this->escape($siteName));

            if (!empty($this->timeline->description)) {
                $doc->setDescription($this->escape(Text::truncate(strip_tags($this->timeline->description), 200, true, false))); // true para entero, false para no añadir '...'
            }
            // Aquí podrías añadir más metadatos si los tienes (ej. OpenGraph para redes sociales)
            // $doc->addCustomTag('<meta property="og:title" content="' . $this->escape($pageTitle) . '" />');
        } else {
            // Caso donde no hay error pero tampoco timeline (ej. ID 0 y no se mostró error aún)
            $doc->setTitle($this->escape(Text::_('COM_AUDATORIA_TIMELINE_VIEW_DEFAULT_TITLE')));
        }
    }

    /**
     * Carga los assets JS/CSS necesarios.
     */
    protected function loadAssets(): void
    {
        $wa = $this->document->getWebAssetManager();

        if ($wa) {
            // Es buena práctica registrar y luego usar, especialmente si tienen dependencias o atributos.
            $wa->registerStyle('timeline3-css', 'https://cdn.knightlab.com/libs/timeline3/latest/css/timeline.css', [], ['version' => 'auto']);
            $wa->useStyle('timeline3-css');

            // El JS de TimelineJS podría depender de jQuery si lo usara, aunque la v3 es independiente.
            // Ponerlo en el pie (`['defer' => true, 'group' => 'footer']`) es a menudo mejor para el rendimiento.
            $wa->registerScript('timeline3-js', 'https://cdn.knightlab.com/libs/timeline3/latest/js/timeline.js', [], ['version' => 'auto'], ['defer' => true]);
            $wa->useScript('timeline3-js');

            // Si tienes tu propio CSS/JS para el componente:
            // $wa->registerAndUseStyle('com_audatoria.site', 'media/com_audatoria/css/site_style.css', [], ['version' => 'auto']);
            // $wa->registerAndUseScript('com_audatoria.site.init', 'media/com_audatoria/js/site_init.js', ['timeline3-js'], ['version' => 'auto'], ['defer' => true]);
        } else {
            // Fallback si WebAssetManager no está disponible (muy improbable en J5)
            $this->document->addStyleSheet('https://cdn.knightlab.com/libs/timeline3/latest/css/timeline.css');
            $this->document->addScript('https://cdn.knightlab.com/libs/timeline3/latest/js/timeline.js', ['defer' => true]);
        }
    }
}