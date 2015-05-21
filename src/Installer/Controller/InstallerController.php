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

    	foreach (array($base_dir."vendor",$base_dir."module") as $folder)
    	{
    		$dir = opendir($folder);
	    	while ($vendor = readdir($dir))
	    	{
	    		if(in_array($vendor, array(".","..")))
	    			continue;
	    		
			    		if(!is_dir($folder."/$vendor"))
			    			continue;
	    		
						if($folder == $base_dir.'module')
							$dir2 = opendir($folder);
						else
							$dir2 = opendir($folder."/$vendor");
	    				
	   					while ($module = readdir($dir2))
	   					{
	   						if(in_array($module, array(".","..")))
	   							continue;

	   						if(!is_dir($folder."/$vendor/$module") && !is_dir($folder."/$module"))
	   							continue;
	   						
	   						$console->write( "$module\n", color::LIGHT_MAGENTA);
	   						
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
	   								
			   						switch($operation_arr[0])
			   						{
			   							case "copy":
			   								if(!isset($operation_arr[3]) || mb_strtolower(trim($operation_arr[3])) == 'true')
			   									$operation_arr[3] = 1; //create folder
	
			   								if(!isset($operation_arr[4]) || mb_strtolower(trim($operation_arr[3])) == 'true')
			   									$operation_arr[4] = 1; //rewrite existen files
	
			   								if(mb_strtolower(trim($operation_arr[3])) == 'false')
			   									$operation[3] = 0;
			   									
			   								if(mb_strtolower(trim($operation_arr[4])) == 'false')
			   									$operation[4] = 0;
			   								
			   								if($this->copyFiles($folder."/$vendor/$module/".$operation_arr[1], $base_dir.$operation_arr[2],(int)$operation_arr[3],(int)$operation_arr[4]))
			   									$i++;
			   							break;
			   							
			   							default:
			   								$console->write("\tOperation {$operation_arr[0]} not supported\n", color::RED);
			   							break;
			   						}
	   							}
	   							
	   							if(!$i)
	   								$console->write("\t==================\n\tNo operations maked in $module\n", color::YELLOW);
	   							else 
	   								$console->write("\t==================\n\tThere have been $i operation(s) in $module\n", color::GREEN);
	   							
		   						fclose($fp);
	   						}else
	   						{
	   							$console->write("\tFile operation data/installer.data not found\n", color::YELLOW);
	   						}
	   					}
	    				closedir($dir2);
	
	    	}
    		closedir($dir);

    		
        }
    	
        /**
         * Update schema for database
         */

        $local = file_get_contents(dirname(__FILE__)."/../../../data/local.php");
        
        $console->write( "\nConfigure database\n=============\n", color::LIGHT_MAGENTA);
        $db_name = $this->getConsoleText($console,"Please get Db name");
        $db_user = $this->getConsoleText($console,"Please get db User");
        $db_pass = $this->getConsoleText($console,"Please get db PASS");
    	
        $new_local = sprintf($local,$db_name,$db_user,$db_pass);
        
        $fp = fopen("./config/autoload/local.php", "w");
        fwrite($fp, $new_local);
        fclose($fp);
        
        if(file_exists("./config/application.config.php"))
        {
        	$console->write( "\nStandart modules include to application.config.php\n=============\n", color::LIGHT_MAGENTA);
	        $config = file_get_contents("./config/application.config.php");
	        $config_str = str_replace("'Application',", "'TsvFunctions',
//     	'CsnFileManager',
    	'DoctrineModule',
    	'DoctrineORMModule',
    	'ZfcBase',
    	'ZfcUser',
    	'BjyProfiler',
    	'ZfcAdmin',
    	'TsvDirectory',
    	'TsvUsers',
     	'ZendDeveloperTools',
    	'BjyAuthorize',
    	'TsvNews',
        'Application',", $config);

	        $fp = fopen("./config/application.config.php","w");
	        fwrite($fp, $config_str);
	        fclose($fp);
	         
        }

        $console->write( "\nConfigure DB Schema\n=============\n", color::LIGHT_MAGENTA);
        
        system("./vendor/bin/doctrine-module orm:schema-tool:update --force");

        $console->write( "=============\nStrongly recomended set admin for using Admin system.\n
Please, use\n", color::YELLOW);
        $console->write( "zf drau\n");
        $console->write( "for insert new admin\n", color::YELLOW);
        
        /**
         * Set proxy writable
         * data/DoctrineORMModule/Proxy
         */
        
        if(!file_exists('./data'))
        	mkdir("./data");

        if(!file_exists('./data/DoctrineORMModule'))
        	mkdir("./data/DoctrineORMModule");
        
        if(!file_exists('./data/DoctrineORMModule/Proxy'))
        	mkdir("./data/DoctrineORMModule/Proxy");
        system("chmod -R 0777 ./data/DoctrineORMModule/Proxy");

        $console->write( "\nFolder ./data/DoctrineORMModule/Proxy set writable\n=============\n", color::LIGHT_MAGENTA);
               
        
    	$console->write( "\n=============\nEnd install operation \n", color::LIGHT_CYAN);
    	
    	return '';
    }
    
    public function getConsoleText($console, $show_title = 'Requested value', $max_length = 255, $min_length = 1)
    {
    	$console->write( "$show_title: ", color::GREEN);
    	$console->showCursor();
    	$data = $console->readLine();
    	 
    	if(mb_strlen($data)>=$min_length && $data<=$max_length)
    		return $data;
    	else
    	{
    		$console->write( "Please insert value length > ".($min_length-1)." and < ".($max_length+1)." chars. Press CTRL+C for cancel. \n", color::YELLOW);
    		return $this->getConsoleText($console, $show_title, $max_length, $min_length);
    	}
    }
    
    private function copyFiles($from,$to,$create_folder = true, $rewrite_files = true)
    {
    	$base_dir = "";
    	$from = str_replace("..", "", $from);
    	$to = str_replace("..", "", $to);
    	
    	$console = $this->getServiceLocator()->get('console');
    	 
    	if(!mb_strlen($from))
    	{
    		$console->write("\t!! ".__FUNCTION__." error: you dont set source folder name", color::RED);
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
    			$console->write("\t-- folder $base_dir$to_folder not exists, cant copy file $from to $to\n", color::RED);
    			return false;
    		}
    		
    		$path_arr = explode("/", $to_folder);
    		$path = ".";
    		
    		foreach ($path_arr as $k=>$v)
    		{
    			if($k>0)
    			$path .= "/".$v;

    			echo "\n$path\n";

    			if($path!='.')
	    			if(!file_exists($path))
	    				if(!@mkdir($path))
	    					$console->write("\t-- Cant create $path folder for copy file to $to\n", color::RED);
    		}
    	}
    	
    	if(!$rewrite_files && file_exists($base_dir.$to) && !is_dir($base_dir.$from))
    	{
    		$console->write("\t-- Cant copy file ".basename($from)." to $to because it already exists. Please set rewrite_files param to TRUE for rewrite.\n", color::YELLOW);
    		return false;
    	}
    		
    	if(!is_dir($base_dir.$from))
	    	if(file_exists($base_dir."$from") && !is_dir($base_dir.$from))
	    	{
	   			if(@copy($base_dir.$from, $base_dir.$to))
	    			$console->write( "\t++ File ".basename($from)." was copied to $to\n", color::GREEN);
	    		else
	    			$console->write("\t-- Cant copy file ".basename($from)." to $base_dir$to. May be access denied.\n", color::YELLOW);
	    	}
	    	else
	    	{
	    		$console->write("\t-- ".basename($from)." not exists in $base_dir$from_folder\n", color::RED);
	    		return false;
	    	}
	    	
    	return true;
    	 
    }
}