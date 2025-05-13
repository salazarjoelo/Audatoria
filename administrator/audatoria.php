<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_audatoria
 * @copyright   Copyright (C) 2025 Joel Salazar. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Autoload dependencies (if any composer dependencies exist)
if (file_exists(JPATH_COMPONENT_ADMINISTRATOR . '/vendor/autoload.php')) {
    require_once JPATH_COMPONENT_ADMINISTRATOR . '/vendor/autoload.php';
}

// Include dependencies
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Language\Text;

// Execute the task
try {
    // Load the language file for the component
    $lang = Factory::getLanguage();
    $lang->load('com_audatoria', JPATH_ADMINISTRATOR);

    // Get an instance of the controller
    $controller = BaseController::getInstance('Audatoria');

    // Perform the requested task
    $input = Factory::getApplication()->input;
    $task = $input->getCmd('task', '');

    // Log the task being executed (for debugging purposes)
    Factory::getApplication()->enqueueMessage(Text::sprintf('COM_AUDATORIA_LOG_TASK', $task), 'message');

    $controller->execute($task);

    // Redirect if set by the controller
    $controller->redirect();
} catch (Exception $e) {
    // Handle errors gracefully
    Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
    Factory::getApplication()->redirect('index.php?option=com_audatoria');
}