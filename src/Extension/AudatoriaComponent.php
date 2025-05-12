<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_audatoria
 *
 * @copyright   Copyright (C) 2025 Joel Salazar. Todos los derechos reservados.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Salazarjoelo\Component\Audatoria\Site\Extension; // <-- NAMESPACE CORRECTO

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Dispatcher\DispatcherInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Component main class for Audatoria (Site).
 *
 * @since  1.0.0
 */
class AudatoriaComponent implements ComponentInterface
{
    protected $application;
    protected $dispatcher;
    protected $mvcFactory;

    public function getContext(): string
    {
        return 'site';
    }

    public function boot(CMSApplication $application, MVCFactoryInterface $mvcFactory, DispatcherInterface $dispatcher): void
    {
        $this->application = $application;
        $this->mvcFactory = $mvcFactory;
        $this->dispatcher = $dispatcher;
    }

    public function setApplication(CMSApplication $application): void
    {
        $this->application = $application;
    }

    public function setMVCFactory(MVCFactoryInterface $mvcFactory): void
    {
        $this->mvcFactory = $mvcFactory;
    }

    public function setDispatcher(DispatcherInterface $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }
}