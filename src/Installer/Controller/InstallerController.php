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
    	$console->write( "Start install operation \n=============\n", color::LIGHT_CYAN);
    	
    	$base_dir = "./";
    	$folder = $base_dir."vendor";

    	$dir = opendir($folder);
    	
    	while ($vendor = readdir($dir))
    	{
    		if(in_array($vendor, array(".","..")))
    			continue;
    		
    		switch ($vendor)
    		{
    			case "marks12":
    				$dir2 = opendir($folder."/$vendor");
    				
   					while ($module = readdir($dir2))
   					{
   						if(in_array($module, array(".","..")))
   							continue;
   						
   						$console->write( "\n$module\n\n", color::LIGHT_MAGENTA);
   						
   						if(file_exists($folder."/$vendor/$module/data/installer.data"))
   						{
   							$fp = fopen($folder."/$vendor/$module/data/installer.data", "r");

   							$i = 0;
   							while (!feof($fp))
   							{
   								$str = trim(fgets($fp));
   								
   								if(!mb_strlen($str) || in_array($str[0],array("/",";","#","=")))
   									continue;
   								
   								$operation_arr = explode(":", $str);
   								if(count($operation_arr)==1)
   								{
   									$console->write( "\tOperation ingrored: $str \n", color::RED);
   									continue;
   								}
   								
		   						switch ($operation_arr[0])
		   						{
		   							case "copy":
		   								if(!isset($operation_arr[3]) || mb_strtolower(trim($operation[3])) == 'true')
		   									$operation_arr[3] = 1; //create folder

		   								if(!isset($operation_arr[4]) || mb_strtolower(trim($operation[3])) == 'true')
		   									$operation_arr[4] = 1; //rewrite existen files

		   								if(mb_strtolower(trim($operation[3])) == 'false')
		   									$operation[3] = 0;
		   									
		   								if(mb_strtolower(trim($operation[4])) == 'false')
		   									$operation[4] = 0;
		   								
		   								if($this->copyFiles($folder."/$vendor/$module/".$operation_arr[1], $base_dir.$operation_arr[2],(int)$operation_arr[3],(int)$operation_arr[4]))
		   									$i++;
		   							break;
		   							
		   							default:
		   								$console->write("\tOperation {$operation_arr[0]} not supported\n", color::RED);
		   							break;
		   						}
   							}
   							
	   						fclose($fp);
   						}
   					}
    				closedir($dir2);
    			break;
    			
    			default:
    			break;
    		}
    	}
    	
    	closedir($dir);
    	
    	$console->write( "\n=============\nEnd install operation \n", color::LIGHT_CYAN);
    	
    	return '';
    }
    
    private function copyFiles($from,$to,$create_folder = true, $rewrite_files = true)
    {
    	$base_dir = "./";
    	$from = str_replace("..", "", $from);
    	$to = str_replace("..", "", $to);
    	
    	$console = $this->getServiceLocator()->get('console');
    	 
    	if(!mb_strlen($from))
    	{
    		$console->write("!! ".__FUNCTION__." error: you dont set source folder name", color::RED);
    		return false;
    	}
    	
    	$folder = $base_dir.$from;
    	if(is_dir($folder))
    	{
    		$dir = opendir($folder);
    		
    		while ($file = readdir($dir))
    		{
    			if(in_array($file, array(".","..")))
    				continue;
    			
    			$this->copyFiles($from."/".$file, $to."/".$file,$create_folder,$rewrite_files);
    		}
    		
    		closedir($dir);
    	}
    	
    	$to_folder = dirname($to);
    	$from_folder = dirname($from);
    	
    	if(!file_exists($base_dir."$to_folder"))
    	{
    		if(!$create_folder)
    		{
    			$console->write("-- folder $base_dir$to_folder not exists, cant copy file $from to $to\n", color::RED);
    			return false;
    		}
    		
    		$path_arr = explode("/", $to_folder);
    		$path = substr($base_dir,0, mb_strlen($base_dir)-1);
    		
    		foreach ($path_arr as $k=>$v)
    		{
    			$path .= "/".$v;
    			if(!file_exists($path))
    				if(!@mkdir($path))
    					$console->write("-- Cant create $path folder for copy file to $to\n", color::RED);
    		}
    	}
    	
    	if(!$rewrite_files && file_exists($base_dir.$to))
    	{
    		$console->write("-- Cant copy file ".basename($from)." to $base_dir$to because it already exists. Please set rewrite_files param to TRUE for rewrite.\n", color::YELLOW);
    		return false;
    	}
    		
    	if(file_exists($base_dir."$from"))
    	{
   			if(@copy($base_dir.$from, $base_dir.$to))
    			$console->write( "++ File ".basename($from)." was copied to $to\n", color::GREEN);
    		else
    			$console->write("-- Cant copy file ".basename($from)." to $base_dir$to\n", color::YELLOW);
    	}
    	else
    	{
    		$console->write("-- ".basename($from)." not exists in $base_dir$from_folder\n", color::RED);
    		return false;
    	}
    	 
    }
}