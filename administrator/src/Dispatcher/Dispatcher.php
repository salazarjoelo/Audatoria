<?php
namespace Joomla\Component\Audatoria\Dispatcher; // Namespace actualizado

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcher;

class Dispatcher extends ComponentDispatcher
{
    public function dispatch()
    {
        $this->loadLanguage();
        parent::dispatch();
    }
}