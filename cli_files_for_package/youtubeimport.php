<?php
/**
 * Script CLI para importar videos de YouTube para el componente Audatoria.
 *
 * USO: php /ruta/a/joomla/cli/audatoria_youtubeimport.php [--channel_id=UCxxxx] [--timeline_id=Y]
 *
 * --channel_id=UCxxxx  (Opcional) ID del canal específico de YouTube a importar.
 * --timeline_id=Y    (Opcional) ID de la timeline específica a la que pertenece el canal_id (requerido si se usa --channel_id).
 *
 * Si no se especifican argumentos, intentará importar para todos los canales habilitados.
 */

// Asegurar que se ejecuta como CLI
if (PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg') {
    die('Este script solo puede ser ejecutado desde la línea de comandos (CLI).');
}

// Definir JPATH_BASE. Este script DEBE estar en la carpeta /cli de la raíz de Joomla.
if (!\defined('JPATH_BASE')) {
    // Asume que el script está en JOOMLA_ROOT/cli/
    $jpath_base = dirname(__DIR__); // Sube un nivel desde /cli a la raíz de Joomla
    if (file_exists($jpath_base . '/includes/defines.php')) {
        define('JPATH_BASE', $jpath_base);
    } else {
        die('Error: JPATH_BASE no pudo ser definido. Asegúrate de que el script está en la carpeta /cli de tu instalación de Joomla.' . PHP_EOL);
    }
}

// Evitar que Joomla termine la sesión si no es necesario para un script CLI
if (!defined('_JEXEC')) {
    define('_JEXEC', 1);
}

// Cargar el framework de Joomla
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

// Importar clases necesarias
use Joomla\CMS\Factory;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseDriver; // Para tipado

// Configurar el manejo de errores para CLI
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0); // Permitir ejecución indefinida para scripts largos
ob_start(); // Iniciar buffer de salida

class AudatoriaYouTubeImportCli
{
    private DatabaseDriver $db;
    private \Joomla\CMS\Application\CMSApplicationInterface $app;
    private \Joomla\Http\Http $http;
    private ?string $apiKey;
    private int $autoPublishState; // 1 para publicado, 0 para no publicado
    private int $defaultUserId = 0; // ID de usuario para 'created_by', 0 para 'System'

    public function __construct()
    {
        try {
            // Inicializar la aplicación Joomla para CLI (sin sesión)
            $this->app = Factory::getApplication('cli', ['session' => false]);
            $this->db = Factory::getDbo();
            $this->http = HttpFactory::getHttp();
        } catch (\Exception $e) {
            $this->output("Error crítico inicializando la aplicación Joomla: " . $e->getMessage());
            exit(1);
        }


        // Cargar parámetros del componente com_audatoria
        try {
            $params = ComponentHelper::getParams('com_audatoria');
            $this->apiKey = $params->get('youtube_api_key');
            $this->autoPublishState = (int) $params->get('auto_publish_imported', 1); // 1 = Publicado, 0 = No Publicado
        } catch (\Exception $e) {
            $this->output("Error cargando parámetros del componente: " . $e->getMessage());
            $this->apiKey = null; // Asegurarse que es null si falla
            $this->autoPublishState = 1; // Default a publicado si los params fallan
        }


        // Cargar archivos de idioma para mensajes (opcional, pero bueno para consistencia)
        try {
            $lang = Factory::getLanguage();
            $lang->load('com_audatoria', JPATH_ADMINISTRATOR, null, false, true); // Cargar desde admin
            $lang->load('com_audatoria', JPATH_SITE, null, false, true); // Cargar también del sitio por si acaso
        }  catch (\Exception $e) {
            $this->output("Advertencia: No se pudieron cargar los archivos de idioma de com_audatoria. Se usarán cadenas en inglés si no se encuentran las claves: " . $e->getMessage());
        }

        // Obtener ID del super usuario para created_user_id (o usar 0)
        // Esto es mejor que un 0 hardcodeado si quieres atribuir a un usuario específico
        // $superUserIds = Access::getUsersByGroup(ComponentHelper::getParams('com_users')->get('super_user_group', 8));
        // if (!empty($superUserIds)) {
        //     $this->defaultUserId = (int) $superUserIds[0];
        // }
    }

    private function output(string $message): void
    {
        echo '[' . (new Date('now'))->format('Y-m-d H:i:s', true) . '] ' . $message . PHP_EOL;
        flush(); // Asegurar que la salida se envíe inmediatamente
        ob_flush();
    }

    public function execute(?string $specificYouTubeChannelId = null, ?int $specificTimelineId = null): void
    {
        if (empty($this->apiKey)) {
            $this->output(Text::_('COM_AUDATORIA_ERROR_YOUTUBE_API_KEY_NOT_CONFIGURED_CLI'));
            Log::add(Text::_('COM_AUDATORIA_ERROR_YOUTUBE_API_KEY_NOT_CONFIGURED_CLI'), Log::ERROR, 'com_audatoria_import');
            return;
        }

        $this->output(Text::_('COM_AUDATORIA_IMPORT_CLI_STARTED'));

        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName(['id', 'channel_id', 'timeline_id', 'title', 'last_checked']))
            ->from($this->db->quoteName('#__audatoria_channels'))
            ->where($this->db->quoteName('state') . ' = 1'); // Solo canales habilitados (state=1)

        if ($specificYouTubeChannelId !== null && $specificTimelineId !== null) {
            $query->where($this->db->quoteName('channel_id') . ' = ' . $this->db->quote($specificYouTubeChannelId));
            $query->where($this->db->quoteName('timeline_id') . ' = ' . (int) $specificTimelineId);
            $this->output(Text::sprintf('COM_AUDATORIA_IMPORT_CLI_SPECIFIC_CHANNEL_TIMELINE', $specificYouTubeChannelId, $specificTimelineId));
        } elseif ($specificYouTubeChannelId !== null) {
             $this->output(Text::sprintf('COM_AUDATORIA_IMPORT_CLI_SPECIFIC_CHANNEL_ONLY_WARNING', $specificYouTubeChannelId));
             // No proceder si solo se da channel_id sin timeline_id, ya que un canal de YT puede estar en múltiples timelines
             return;
        }


        $this->db->setQuery($query);
        try {
            $channels = $this->db->loadObjectList();
        } catch (\Exception $e) {
            $this->output(Text::sprintf('COM_AUDATORIA_IMPORT_CLI_DB_ERROR_CHANNELS', $e->getMessage()));
            Log::add(Text::sprintf('COM_AUDATORIA_IMPORT_CLI_DB_ERROR_CHANNELS', $e->getMessage()), Log::ERROR, 'com_audatoria_import');
            return;
        }


        if (empty($channels)) {
            $this->output(Text::_('COM_AUDATORIA_IMPORT_CLI_NO_CHANNELS_ENABLED'));
            return;
        }

        $dateNowSql = Factory::getDate()->toSql();

        foreach ($channels as $channel) {
            $channelNameForLog = $channel->title ?: $channel->channel_id;
            $this->output(Text::sprintf('COM_AUDATORIA_IMPORT_CLI_PROCESSING_CHANNEL', $channelNameForLog, $channel->channel_id));

            $pageToken = null;
            $videosProcessedThisChannel = 0;
            $maxPagesToFetch = 5; // Límite para evitar uso excesivo de API y bucles largos
            $currentPage = 0;
            $newVideosFoundInApiCall = 0;

            do {
                $currentPage++;
                if ($currentPage > $maxPagesToFetch) {
                    $this->output(Text::sprintf('COM_AUDATORIA_IMPORT_CLI_MAX_PAGES_REACHED', $maxPagesToFetch, $channelNameForLog));
                    break;
                }

                // Construir URL de la API de YouTube
                $apiUrl = "https://www.googleapis.com/youtube/v3/search"
                        . "?key=" . $this->apiKey
                        . "&channelId=" . $channel->channel_id
                        . "&part=snippet,id"
                        . "&order=date"      // Videos más recientes primero
                        . "&type=video"      // Solo videos
                        . "&maxResults=20"; // 1-50, ajusta según necesidad y cuotas (ej. 20)

                if ($pageToken) {
                    $apiUrl .= "&pageToken=" . $pageToken;
                }

                // Optimización: Si tenemos 'last_checked', usar 'publishedAfter'
                // Pero cuidado: si un video viejo no se importó, esta optimización lo omitiría.
                // Por ahora, no lo usamos para asegurar que se revisen todos los videos (hasta cierto límite de páginas).
                // if ($channel->last_checked && $channel->last_checked !== $this->db->getNullDate()) {
                //     try {
                //         $publishedAfter = new Date($channel->last_checked);
                //         // Formato RFC 3339 / ISO 8601
                //         $apiUrl .= "&publishedAfter=" . rawurlencode($publishedAfter->format(Date::ISO8601, true));
                //     } catch (\Exception $e) {
                //        $this->output(Text::sprintf('COM_AUDATORIA_IMPORT_CLI_INVALID_LAST_CHECKED', $channelNameForLog, $e->getMessage()));
                //     }
                // }

                $this->output(Text::sprintf('COM_AUDATORIA_IMPORT_CLI_FETCHING_PAGE', $currentPage, $channelNameForLog));

                try {
                    $response = $this->http->get($apiUrl);

                    if ($response->code !== 200) {
                        $errorBody = substr($response->body, 0, 500);
                        $this->output(Text::sprintf('COM_AUDATORIA_IMPORT_CLI_YOUTUBE_API_ERROR', $response->code, $channelNameForLog, $errorBody));
                        Log::add(Text::sprintf('COM_AUDATORIA_IMPORT_CLI_YOUTUBE_API_ERROR_LOG', $response->code, $channelNameForLog, $channel->channel_id, $response->body), Log::ERROR, 'com_audatoria_import');
                        break; // Salir del bucle do-while para este canal si la API falla
                    }

                    $data = json_decode($response->body);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $this->output(Text::sprintf('COM_AUDATORIA_IMPORT_CLI_JSON_DECODE_ERROR', $channelNameForLog, json_last_error_msg()));
                        Log::add(Text::sprintf('COM_AUDATORIA_IMPORT_CLI_JSON_DECODE_ERROR_LOG', $channelNameForLog, json_last_error_msg(), $response->body), Log::ERROR, 'com_audatoria_import');
                        break;
                    }

                    if (empty($data->items)) {
                        $this->output(Text::sprintf('COM_AUDATORIA_IMPORT_CLI_NO_NEW_VIDEOS_PAGE', $currentPage, $channelNameForLog));
                        break; // No más items, salir del bucle do-while
                    }
                    
                    $newVideosFoundInApiCall = count($data->items);

                    foreach ($data->items as $video_item) {
                        if (!isset($video_item->id->videoId) || !isset($video_item->snippet)) {
                            $this->output(Text::_('COM_AUDATORIA_IMPORT_CLI_MALFORMED_VIDEO_ITEM'));
                            continue;
                        }

                        $videoId = $video_item->id->videoId;
                        $videoTitle = $video_item->snippet->title;
                        $videoDescription = $video_item->snippet->description;
                        $publishedAt = $video_item->snippet->publishedAt; // UTC (Zulu time)

                        // Comprobar si el video ya existe en la base de datos para esta timeline
                        $checkQuery = $this->db->getQuery(true)
                            ->select('COUNT(*)')
                            ->from($this->db->quoteName('#__audatoria_items'))
                            ->where($this->db->quoteName('external_source_id') . ' = ' . $this->db->quote($videoId))
                            ->where($this->db->quoteName('timeline_id') . ' = ' . (int)$channel->timeline_id);
                        $this->db->setQuery($checkQuery);

                        if ($this->db->loadResult() > 0) {
                            // $this->output(Text::sprintf('COM_AUDATORIA_IMPORT_CLI_VIDEO_EXISTS', $videoTitle, $videoId));
                            // Si los videos se ordenan por fecha y encontramos uno existente,
                            // podríamos asumir que todos los anteriores ya están. Pero es más seguro continuar
                            // por si hubo fallos previos o importaciones parciales.
                            continue;
                        }

                        $itemObject = new \stdClass();
                        $itemObject->timeline_id        = (int)$channel->timeline_id;
                        $itemObject->title              = $videoTitle;
                        $itemObject->description        = $videoDescription;
                        // Convertir fecha de publicación a formato SQL de Joomla (usualmente YYYY-MM-DD HH:MM:SS)
                        // y almacenar como UTC en la BD.
                        try {
                            $itemObject->start_date = (new Date($publishedAt))->toSql(true); // true para convertir a local si es necesario, pero API es UTC. Guardar UTC.
                        } catch (\Exception $e) {
                            $this->output(Text::sprintf('COM_AUDATORIA_IMPORT_CLI_INVALID_PUBLISH_DATE', $videoId, $publishedAt, $e->getMessage()));
                            $itemObject->start_date = $dateNowSql; // Fallback a la fecha actual
                        }
                        // $itemObject->end_date        = null; // Opcional
                        $itemObject->media_type         = 'youtube';
                        $itemObject->media_url          = 'https://www.youtube.com/watch?v=' . $videoId; // URL canónica
                        $itemObject->media_caption      = $videoTitle; // Puede ser redundante, pero útil
                        // $itemObject->media_credit    = $video_item->snippet->channelTitle; // Opcional
                        $itemObject->external_source_id = $videoId;
                        $itemObject->created_user_id    = $this->defaultUserId;
                        $itemObject->created_time       = $dateNowSql;
                        $itemObject->modified_user_id   = $this->defaultUserId; // O el ID del sistema/cron
                        $itemObject->modified_time      = $dateNowSql;
                        $itemObject->state              = $this->autoPublishState; // 1 para publicado, 0 para no publicado
                        $itemObject->access             = 1; // Acceso público por defecto
                        $itemObject->language           = '*'; // Todos los idiomas por defecto
                        // $itemObject->lat             = null;
                        // $itemObject->lng             = null;
                        // $itemObject->location_name   = null;
                        // $itemObject->ordering        = 0; // Podría calcularse si es necesario

                        try {
                            if ($this->db->insertObject('#__audatoria_items', $itemObject, 'id')) {
                                $this->output(Text::sprintf('COM_AUDATORIA_IMPORT_CLI_VIDEO_IMPORTED', $videoTitle, $videoId, $channel->timeline_id));
                                $videosProcessedThisChannel++;
                            } else {
                                $this->output(Text::sprintf('COM_AUDATORIA_IMPORT_CLI_DB_INSERT_ERROR', $videoTitle, $videoId, $this->db->getErrorMsg()));
                                Log::add(Text::sprintf('COM_AUDATORIA_IMPORT_CLI_DB_INSERT_ERROR_LOG', $videoTitle, $videoId, $this->db->getErrorMsg()), Log::ERROR, 'com_audatoria_import');
                            }
                        } catch (\Exception $e) {
                            $this->output(Text::sprintf('COM_AUDATORIA_IMPORT_CLI_DB_INSERT_EXCEPTION', $videoTitle, $videoId, $e->getMessage()));
                            Log::add(Text::sprintf('COM_AUDATORIA_IMPORT_CLI_DB_INSERT_EXCEPTION_LOG', $videoTitle, $videoId, $e->getMessage()), Log::ERROR, 'com_audatoria_import');
                        }
                    } // Fin foreach ($data->items as $video_item)

                    // Preparar para la siguiente página
                    $pageToken = $data->nextPageToken ?? null;

                } catch (\Exception $e) { // Capturar excepciones de Http o de lógica
                    $this->output(Text::sprintf('COM_AUDATORIA_IMPORT_CLI_GENERAL_API_EXCEPTION', $channelNameForLog, $e->getMessage()));
                    Log::add(Text::sprintf('COM_AUDATORIA_IMPORT_CLI_GENERAL_API_EXCEPTION_LOG', $channelNameForLog, $e->getMessage(), $e->getTraceAsString()), Log::ERROR, 'com_audatoria_import');
                    $pageToken = null; // Detener paginación para este canal en caso de excepción
                }

                 // Si en la primera página se procesaron muchos, y la API devuelve items, pero no se procesaron nuevos (porque ya existían)
                 // Y si no hay nextPageToken, significa que ya no hay más.
                if ($currentPage === 1 && $videosProcessedThisChannel === 0 && $newVideosFoundInApiCall > 0 && !$pageToken) {
                     $this->output(Text::sprintf('COM_AUDATORIA_IMPORT_CLI_NO_NEW_VIDEOS_PROCESSED_FIRST_PAGE', $channelNameForLog));
                }


            } while ($pageToken); // Fin do-while para paginación

            // Actualizar 'last_checked' para el canal, incluso si no se importaron videos nuevos (para saber que se revisó)
            $updateQuery = $this->db->getQuery(true)
                ->update($this->db->quoteName('#__audatoria_channels'))
                ->set($this->db->quoteName('last_checked') . ' = ' . $this->db->quote($dateNowSql))
                ->where($this->db->quoteName('id') . ' = ' . (int)$channel->id);
            $this->db->setQuery($updateQuery);
            try {
                $this->db->execute();
            } catch (\Exception $e) {
                $this->output(Text::sprintf('COM_AUDATORIA_IMPORT_CLI_DB_UPDATE_LAST_CHECKED_ERROR', $channelNameForLog, $e->getMessage()));
                Log::add(Text::sprintf('COM_AUDATORIA_IMPORT_CLI_DB_UPDATE_LAST_CHECKED_ERROR_LOG', $channelNameForLog, $e->getMessage()), Log::ERROR, 'com_audatoria_import');
            }

            $this->output(Text::sprintf('COM_AUDATORIA_IMPORT_CLI_CHANNEL_COMPLETE', $channelNameForLog, $videosProcessedThisChannel));

        } // Fin foreach ($channels as $channel)

        $this->output(Text::_('COM_AUDATORIA_IMPORT_CLI_FINISHED'));
        ob_end_flush(); // Enviar toda la salida acumulada
    }
}

// --- Ejecución del script ---

// Obtener argumentos de línea de comandos
$options = getopt('', ['channel_id::', 'timeline_id::', 'help::']);

if (isset($options['help'])) {
    echo "Script de Importación de YouTube para Audatoria\n";
    echo "Uso: php " . basename(__FILE__) . " [opciones]\n";
    echo "Opciones:\n";
    echo "  --channel_id=<YouTubeChannelID>   ID del canal de YouTube a importar (ej. Uxxxxxxxxxxxxxxx).\n";
    echo "  --timeline_id=<TimelineID>        ID de la Línea de Tiempo a la que pertenece el canal_id (requerido si se usa --channel_id).\n";
    echo "  --help                          Muestra este mensaje de ayuda.\n\n";
    echo "Si no se especifican opciones, se importarán todos los canales habilitados.\n";
    exit(0);
}


$specificYouTubeChannelIdToImport = $options['channel_id'] ?? null;
$specificTimelineIdForChannel   = isset($options['timeline_id']) ? (int)$options['timeline_id'] : null;

if ($specificYouTubeChannelIdToImport !== null && $specificTimelineIdForChannel === null) {
    echo "Error: Si se especifica --channel_id, también se debe especificar --timeline_id.\n";
    echo "Use --help para más información.\n";
    exit(1);
}


try {
    $importer = new AudatoriaYouTubeImportCli();
    $importer->execute($specificYouTubeChannelIdToImport, $specificTimelineIdForChannel);
} catch (\Exception $e) {
    // Captura de excepciones no manejadas durante la instanciación o ejecución.
    echo "Error fatal no capturado: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
    Log::add("Error fatal no capturado en AudatoriaYouTubeImportCli: " . $e->getMessage() . "\n" . $e->getTraceAsString(), Log::EMERGENCY, 'com_audatoria_import');
    exit(1);
}

exit(0);