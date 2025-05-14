<?php
namespace Salazarjoelo\Component\Audatoria\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text; // No se usa directamente aquí, pero es bueno tenerlo
use Joomla\CMS\Log\Log;       // Para registrar errores

class TimelineModel extends BaseDatabaseModel
{
    /**
     * Obtiene los datos de una línea de tiempo específica, publicada y accesible.
     *
     * @param   int  $id  El ID de la línea de tiempo.
     * @return  object|null  El objeto de la línea de tiempo o null.
     */
    public function getTimeline(int $id): ?object
    {
        if ($id <= 0) {
            return null;
        }

        $app   = Factory::getApplication();
        $db    = $this->getDbo(); // $this->getDbo() disponible en BaseDatabaseModel
        $query = $db->getQuery(); // CORREGIDO

        $query->select($db->quoteName(['a.id', 'a.title', 'a.description', 'a.access', 'a.language', 'a.params']))
            ->from($db->quoteName('#__audatoria_timelines', 'a'))
            ->where($db->quoteName('a.id') . ' = ' . (int) $id)
            ->where($db->quoteName('a.state') . ' = 1'); // Solo publicadas

        // Comprobar nivel de acceso
        $user = $app->getIdentity(); // CORREGIDO
        $levels = $user->getAuthorisedViewLevels();
        $query->where($db->quoteName('a.access') . ' IN (' . implode(',', $levels) . ')');

        // Comprobar idioma
        $currentLanguage = $app->getLanguage()->getTag(); // CORREGIDO
        $query->where('(' . $db->quoteName('a.language') . ' = ' . $db->quote($currentLanguage)
            . ' OR ' . $db->quoteName('a.language') . ' = ' . $db->quote('*') . ')');

        $db->setQuery($query);

        try {
            $timeline = $db->loadObject();
            // Aquí podrías procesar los parámetros si es necesario
            // if ($timeline && !empty($timeline->params)) {
            //     $timeline->pageParams = new \Joomla\Registry\Registry($timeline->params);
            // }
            return $timeline;
        } catch (\RuntimeException $e) {
            Log::add('Error cargando timeline (sitio) ' . $id . ': ' . $e->getMessage(), Log::ERROR, 'com_audatoria');
            $app->enqueueMessage(Text::_('COM_AUDATORIA_ERROR_TIMELINE_LOAD_ERROR'), 'error'); // Necesitas esta constante de idioma
            return null;
        }
    }

    /**
     * Obtiene los ítems publicados de una línea de tiempo específica.
     *
     * @param   int  $timelineId  El ID de la línea de tiempo.
     * @return  array  Un array de objetos de ítems.
     */
    public function getItems(int $timelineId): array
    {
        if ($timelineId <= 0) {
            return [];
        }

        $app   = Factory::getApplication();
        $db    = $this->getDbo();
        $query = $db->getQuery(); // CORREGIDO

        $query->select($db->quoteName([
                'a.id', 'a.title', 'a.description', 'a.start_date', 'a.end_date',
                'a.media_type', 'a.media_url', 'a.media_caption', 'a.media_credit',
                'a.lat', 'a.lng', 'a.location_name', 'a.params' // Campos necesarios para TimelineJS
            ]))
            ->from($db->quoteName('#__audatoria_items', 'a'))
            ->where($db->quoteName('a.timeline_id') . ' = ' . (int) $timelineId)
            ->where($db->quoteName('a.state') . ' = 1') // Solo ítems publicados
            ->order($db->quoteName('a.start_date') . ' ASC');

        // Comprobar nivel de acceso de los ítems (si tienen su propio campo 'access')
        // $user = $app->getIdentity();
        // $levels = $user->getAuthorisedViewLevels();
        // $query->where($db->quoteName('a.access') . ' IN (' . implode(',', $levels) . ')');

        // Comprobar idioma de los ítems
        $currentLanguage = $app->getLanguage()->getTag(); // CORREGIDO
        $query->where('(' . $db->quoteName('a.language') . ' = ' . $db->quote($currentLanguage)
            . ' OR ' . $db->quoteName('a.language') . ' = ' . $db->quote('*') . ')');

        $db->setQuery($query);

        try {
            $items = $db->loadObjectList();
            // Aquí podrías procesar los parámetros de cada ítem si es necesario
            // foreach ($items as &$item) {
            //     if (!empty($item->params)) {
            //         $item->itemParams = new \Joomla\Registry\Registry($item->params);
            //     }
            // }
            return $items ?: [];
        } catch (\RuntimeException $e) {
            Log::add('Error cargando items para timeline (sitio) ' . $timelineId . ': ' . $e->getMessage(), Log::ERROR, 'com_audatoria');
            $app->enqueueMessage(Text::_('COM_AUDATORIA_ERROR_TIMELINE_LOAD_ERROR'), 'error');
            return [];
        }
    }
}