<?php
// Ubicación: site/views/timeline/tmpl/default.php
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper; // Para posibles usos futuros

// Los assets (CSS/JS) ya fueron cargados por la vista (view.html.php)

// Comprobar si la timeline se cargó correctamente
if (!$this->timeline) {
    // Si la vista no lanzó un 404, muestra un mensaje aquí.
    // Normalmente, si no se encuentra, no se llegaría aquí debido al throw en la vista.
    echo '<p class="alert alert-warning">' . Text::_('COM_AUDATORIA_TIMELINE_NOT_AVAILABLE') . '</p>';
    return;
}

$timeline_events = []; // Inicializar array para eventos

// Procesar los ítems para convertirlos al formato JSON que TimelineJS espera
if (!empty($this->items)) {
    foreach ($this->items as $item) {
        // Validar y formatear fecha de inicio (¡Obligatoria para TimelineJS!)
        $startDateObj = null;
        if (!empty($item->start_date) && $item->start_date !== $this->getDbo()->getNullDate()) {
            try {
                 $startDateObj = new \Joomla\CMS\Date\Date($item->start_date);
            } catch (\Exception $e) {
                 // Fecha inválida, loguear y saltar este item
                 \Joomla\CMS\Log\Log::add(
                    'Fecha de inicio inválida para item ID ' . $item->id . ': ' . $item->start_date . ' en timeline ID ' . $this->timeline->id,
                    \Joomla\CMS\Log\Log::WARNING,
                    'com_audatoria'
                 );
                continue; // Saltar al siguiente item
            }
        } else {
            // Si no hay fecha de inicio, no se puede mostrar en TimelineJS
            \Joomla\CMS\Log\Log::add(
                'Item ID ' . $item->id . ' omitido por falta de fecha de inicio en timeline ID ' . $this->timeline->id,
                \Joomla\CMS\Log\Log::WARNING,
                'com_audatoria'
            );
            continue;
        }

        // Construir el objeto 'event' para JSON
        $event = [
            'start_date' => [
                'year'  => $startDateObj->format('Y', true), // true para obtener UTC si la fecha es UTC
                'month' => $startDateObj->format('m', true),
                'day'   => $startDateObj->format('d', true),
                'hour'  => $startDateObj->format('H', true), // Opcional
                'minute'=> $startDateObj->format('i', true), // Opcional
            ],
            'text' => [
                // Usar htmlspecialchars para prevenir XSS si el contenido no debe ser HTML
                'headline' => $item->title ? htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8') : Text::_('COM_AUDATORIA_UNTITLED_ITEM'),
                // Permitir HTML en la descripción, pero limpiarlo si es necesario (JInput/Filter) o mostrarlo tal cual
                 // 'text' => $item->description ? $item->description : ''
                 // Alternativa: Limpiar HTML básico
                 'text' => $item->description ? Joomla\CMS\HTML\HTMLHelper::_('string.truncate', strip_tags($item->description), 500, true, true) : '' // Ejemplo: truncado y sin HTML
            ],
             // ID único para el slide si es necesario (para deep linking)
             'unique_id' => 'audatoria_item_' . $item->id
        ];

        // Añadir fecha de fin si existe y es válida
        if (!empty($item->end_date) && $item->end_date !== $this->getDbo()->getNullDate()) {
            try {
                $endDateObj = new \Joomla\CMS\Date\Date($item->end_date);
                // Asegurarse que la fecha de fin no sea anterior a la de inicio
                if ($endDateObj >= $startDateObj) {
                    $event['end_date'] = [
                        'year'  => $endDateObj->format('Y', true),
                        'month' => $endDateObj->format('m', true),
                        'day'   => $endDateObj->format('d', true),
                        'hour'  => $endDateObj->format('H', true),
                        'minute'=> $endDateObj->format('i', true),
                    ];
                }
            } catch (\Exception $e) {
                // Fecha de fin inválida, no la incluimos
                \Joomla\CMS\Log\Log::add(
                    'Fecha de fin inválida para item ID ' . $item->id . ': ' . $item->end_date . ' en timeline ID ' . $this->timeline->id,
                    \Joomla\CMS\Log\Log::WARNING,
                    'com_audatoria'
                 );
            }
        }

        // Añadir media si existe
        if (!empty($item->media_url)) {
            $event['media'] = [
                'url' => $item->media_url, // Asegúrate que la URL es válida para TimelineJS
                'caption' => $item->media_caption ? htmlspecialchars($item->media_caption, ENT_QUOTES, 'UTF-8') : '',
                'credit' => $item->media_credit ? htmlspecialchars($item->media_credit, ENT_QUOTES, 'UTF-8') : '',
            ];
            // Podrías añadir miniaturas (thumbnail) si las tienes disponibles
            // 'thumbnail' => '...'
        }

        // Añadir grupo si es relevante (para categorizar eventos visualmente)
        // if (!empty($item->category)) { // Asumiendo que tienes un campo 'category'
        //     $event['group'] = htmlspecialchars($item->category, ENT_QUOTES, 'UTF-8');
        // }

        // Añadir ubicación al texto si existe (TimelineJS no tiene un campo 'location' de primer nivel)
        if (!empty($item->location_name)) {
             $event['text']['text'] .= '<br><small class="audatoria-location">'
                                     . '<span class="icon-location" aria-hidden="true"></span> ' // Asume que tienes CSS para este icono
                                     . htmlspecialchars($item->location_name, ENT_QUOTES, 'UTF-8')
                                     . '</small>';
        }

        $timeline_events[] = $event;
    }
}

// Preparar la estructura final para TimelineJS
$timeline_data = [
    'title' => [
        'text' => [
            'headline' => htmlspecialchars($this->timeline->title, ENT_QUOTES, 'UTF-8'),
            'text'     => $this->timeline->description ? $this->timeline->description : '', // Permitir HTML aquí si se desea
        ],
        // Podrías añadir media para la diapositiva del título aquí
        // 'media' => [...]
    ],
    'events' => $timeline_events,
    // Puedes añadir 'eras' aquí si las necesitas
    // 'eras' => [...]
];

?>

<?php if (!empty($timeline_events)): // Solo mostrar la línea de tiempo si hay eventos válidos ?>
    <div id="timeline-embed" style="width: 100%; height: 600px; min-height: 600px;">
        <noscript>
            <p><?php echo Text::_('COM_AUDATORIA_ENABLE_JAVASCRIPT'); ?></p>
        </noscript>
    </div>

    <?php
        // Opciones para TimelineJS
        $timeline_options = [
            'hash_bookmark' => true, // Habilitar deep linking con hash en la URL
            'initial_zoom'  => 2,
            'start_at_end'  => false, // Empezar por el evento más antiguo
            'debug'         => Factory::getApplication()->get('debug'), // Activar debug de TimelineJS si Joomla está en modo debug
            'language'      => substr(Factory::getApplication()->getLanguage()->getTag(), 0, 2), // Pasar el código de idioma de Joomla (ej. 'es')
            // 'map_type' => 'osm:standard', // Si quieres usar OpenStreetMap por defecto
            'timenav_position' => 'bottom', // Posición de la barra de navegación
            // Otras opciones: scale_factor, start_at_slide, etc.
        ];
        
        // Añadir clave API de Google Maps si está configurada y si TimelineJS la necesita (por defecto usa OSM)
        $googleApiKey = $this->params->get('Maps_api_key');
        if (!empty($googleApiKey)) {
             // TimelineJS espera la clave como 'google_api_key'
             // Pero verifica la documentación actual de TimelineJS si esto sigue siendo necesario o cómo configurarlo
             // $timeline_options['google_api_key'] = $googleApiKey;
             // Si prefieres Google Maps:
             // $timeline_options['map_type'] = 'google:roadmap';
        }

        // Convertir datos y opciones a JSON de forma segura
        try {
            $json_data = json_encode($timeline_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            $json_options = json_encode($timeline_options, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
             echo '<p class="alert alert-danger">Error al generar JSON para TimelineJS: ' . $e->getMessage() . '</p>';
             // Loguear el error
             Log::add('Error JSON para TimelineJS: ' . $e->getMessage() . ' - Data: ' . print_r($timeline_data, true) . ' - Options: ' . print_r($timeline_options, true), Log::ERROR, 'com_audatoria');
             return; // No continuar si el JSON falla
        }
    ?>

    <script>
        // Esperar a que el DOM esté listo y TimelineJS (TL) esté cargado
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof TL !== 'undefined') {
                try {
                    const timelineData = <?php echo $json_data; ?>;
                    const timelineOptions = <?php echo $json_options; ?>;
                    window.timeline = new TL.Timeline('timeline-embed', timelineData, timelineOptions);
                } catch (e) {
                     console.error('Error inicializando TimelineJS:', e);
                     document.getElementById('timeline-embed').innerHTML = '<p class="alert alert-danger"><?php echo Text::_('COM_AUDATORIA_ERROR_TIMELINEJS_INIT_FAILED'); ?></p>';
                }
            } else {
                console.error('TimelineJS (TL) no está definido.');
                // Mostrar un mensaje o intentar cargar TL de nuevo podría ser una opción
                document.getElementById('timeline-embed').innerHTML = '<p class="alert alert-warning"><?php echo Text::_('COM_AUDATORIA_ERROR_TIMELINEJS_NOT_LOADED'); ?></p>';
            }
        });
    </script>

<?php elseif ($this->timeline): ?>
    <?php // La timeline existe pero no tiene ítems publicables/válidos ?>
    <div class="alert alert-info">
        <h2><?php echo htmlspecialchars($this->timeline->title, ENT_QUOTES, 'UTF-8'); ?></h2>
        <?php if ($this->timeline->description): ?>
             <div><?php echo $this->timeline->description; ?></div>
        <?php endif; ?>
        <p><?php echo Text::_('COM_AUDATORIA_NO_EVENTS_TO_DISPLAY'); ?></p>
    </div>
<?php else: ?>
    <?php // Este caso no debería ocurrir si la vista maneja el error 404 ?>
    <p class="alert alert-danger"><?php echo Text::_('COM_AUDATORIA_TIMELINE_LOAD_ERROR'); ?></p>
<?php endif; ?>