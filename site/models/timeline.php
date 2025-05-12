<?php
// Ubicación: site/models/timeline.php
namespace Joomla\Component\Audatoria\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Access\Access; // Para comprobar permisos de acceso

class TimelineModel extends BaseDatabaseModel
{
    /**
     * Obtiene los datos de una línea de tiempo específica.
     *
     * @param   int  $id  El ID de la línea de tiempo.
     *
     * @return  object|null  El objeto de la línea de tiempo o null si no se encuentra o no se tiene acceso.
     */
    public function getTimeline(int $id): ?object
    {
        if ($id <= 0) {
            return null;
        }

        $db    = $this->getDbo();
        $query = $db->getQuery(true)
            ->select('a.id, a.title, a.description, a.access, a.language') // Selecciona los campos necesarios
            ->from($db->quoteName('#__audatoria_timelines', 'a'))
            ->where('a.id = :id')
            ->where('a.state = 1'); // Solo obtener timelines publicadas

        // Comprobar nivel de acceso
        $user = Factory::getApplication()->getIdentity();
        $levels = $user->getAuthorisedViewLevels();
        $query->where('a.access IN (' . implode(',', $levels) . ')');

        // Comprobar idioma
        $query->where('a.language IN (' . $db->quote(Factory::getApplication()->getLanguage()->getTag()) . ',' . $db->quote('*') . ')');

        $query->bind(':id', $id, $db->PARAM_INT); // Usar bind para seguridad

        $db->setQuery($query);

        try {
            $timeline = $db->loadObject();
            return $timeline;
        } catch (\Exception $e) {
            // Loguear el error si es necesario
            Log::add('Error cargando timeline ' . $id . ': ' . $e->getMessage(), Log::ERROR, 'com_audatoria');
            return null;
        }
    }

    /**
     * Obtiene los ítems publicados de una línea de tiempo específica.
     *
     * @param   int  $timelineId  El ID de la línea de tiempo.
     *
     * @return  array  Un array de objetos de ítems.
     */
    public function getItems(int $timelineId): array
    {
        if ($timelineId <= 0) {
            return [];
        }

        $db    = $this->getDbo();
        $query = $db->getQuery(true)
            ->select('a.id, a.title, a.description, a.start_date, a.end_date, a.media_type, a.media_url, a.media_caption, a.media_credit, a.lat, a.lng, a.location_name') // Campos necesarios para TimelineJS
            ->from($db->quoteName('#__audatoria_items', 'a'))
            ->where('a.timeline_id = :timeline_id')
            ->where('a.state = 1') // Solo ítems publicados
            ->order($db->quoteName('a.start_date') . ' ASC'); // Ordenar por fecha de inicio

        // Comprobar nivel de acceso (los ítems heredan el acceso de la timeline o tienen el suyo propio?)
        // Si los ítems tienen su propio campo 'access':
        // $user = Factory::getApplication()->getIdentity();
        // $levels = $user->getAuthorisedViewLevels();
        // $query->where('a.access IN (' . implode(',', $levels) . ')');

         // Comprobar idioma
        $query->where('a.language IN (' . $db->quote(Factory::getApplication()->getLanguage()->getTag()) . ',' . $db->quote('*') . ')');


        $query->bind(':timeline_id', $timelineId, $db->PARAM_INT); // Usar bind

        $db->setQuery($query);

        try {
            $items = $db->loadObjectList();
            return $items ?: []; // Devolver un array vacío si no hay resultados
        } catch (\Exception $e) {
            Log::add('Error cargando items para timeline ' . $timelineId . ': ' . $e->getMessage(), Log::ERROR, 'com_audatoria');
            return [];
        }
    }
}