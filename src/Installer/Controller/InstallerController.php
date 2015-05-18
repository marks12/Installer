<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/Installer for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Installer\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Form\Element\Password;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\ColorInterface as color;
use Zend\Text\Table\Table as Table;

class InstallerController extends AbstractActionController
{
    public function indexAction()
    {
        return array();
    }

    public function fooAction()
    {
        // This shows the :controller and :action parameters in default route
        // are working when you browse to /installer/installer/foo
        return array();
    }
    
    public function configureAction()
    {
    	$console = $this->getServiceLocator()->get('console');
    	$console->write( "Start install operation \n=============\n", color::RED);
    	
    	return '';
    }
}
