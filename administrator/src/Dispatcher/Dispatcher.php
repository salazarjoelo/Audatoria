<?php
defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcher;

class AudatoriaDispatcher extends ComponentDispatcher
{
    protected $defaultController = 'dashboard';

    public function dispatch()
    {
        $this->loadLanguage();
        parent::dispatch();
    }
}