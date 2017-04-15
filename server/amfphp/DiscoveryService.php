<?php
include_once(AMFPHP_BASE . "shared/util/MethodTable.php");
/**
 * A built-in amfphp service that allows introspection into services and their methods.
 * Remove from production servers
 */
class DiscoveryService
{
	/**
	 * Get the list of services
	 * @returns An array of array ready to be bound to a Tree
	 */
	function getServices()
	{
		$this->_omit = array();
		$this->_path = dirname(dirname(realpath(__FILE__))) . '/';
		$services = $this->_listServices();
		//Now sort on key
		ksort($services);
		$out = array();
		foreach($services as $key => $val)
		{
			if($key == "zzz_default")
			{
				foreach($val as $key2 => $val2)
				{
					$out[] = array("label" => $val2[0], "data" => $val2[1]);
				}
			}
			else
			{
				$children = array();
				foreach($val as $key2 => $val2)
				{
					$children[] = array("label" => $val2[0], "data" => $val2[1]);
				}
				$out[] = array("label" => $key, "children" => $children, "open" => true);
			}
		}
		return $out;
	}
	
	/**
	 * Describe a service and all its methods
	 * @param $data An object containing 'label' and 'data' keys
	 */
	function describeService($data)
	{
		$className = $data['label'];
		//Sanitize path
		$path = str_replace('..', '', $data['data']);
		//Generate the method table from this info
		$this->_path = dirname(dirname(realpath(__FILE__))) . DIRECTORY_SEPARATOR;
		
		$methodTable = MethodTable::create($this->_path . $path . $className . '.php', NULL, $classComment);
		return array($methodTable, $classComment);
	}
	
	function _listServices($dir = "", $suffix = "")
	{
		if($dir == "")
		{
			$dir = $this->_path;
		}
		$services = array();
		if(in_array($suffix, $this->_omit)){ return; }
		if ($handle = opendir($dir . $suffix))
		{
			while (false !== ($file = readdir($handle))) 
			{
				chdir(dirname(__FILE__));
				if ($file != "." && $file != "..") 
				{
					if(is_file($dir . $suffix . $file))
					{
						if(strpos($file, '.methodTable') !== FALSE)
						{
							continue;
						}
						$index = strrpos($file, '.');
						$before = substr($file, 0, $index);
						$after = substr($file, $index + 1);
						
						if($after == 'php')
						{
							$loc = "zzz_default";
							if($suffix != "")
							{
								$loc = str_replace(DIRECTORY_SEPARATOR,'.', substr($suffix, 0, -1));
							}
							
							if($services[$loc] == NULL)
							{
								$services[$loc] = array();
							}
							$services[$loc][] = array($before, $suffix);
							//array_push($this->_classes, $before);
						}
						
					}
					elseif(is_dir($dir . $suffix . $file))
					{
						$insideDir = $this->_listServices($dir, $suffix . $file . DIRECTORY_SEPARATOR);
						if(is_array($insideDir))
						{
							$services = $services + $insideDir;
						}
					}
				}
			}
		}else{
			//echo("error");
		}
		closedir($handle);
		return $services;
	}
	
	function listTemplates()
	{
		$templates = array();
		if ($handle = opendir('templates'))
		{
			while (false !== ($file = readdir($handle))) 
			{
				//chdir(dirname(__FILE__));
				if ($file != "." && $file != "..") 
				{
					if(is_file('./templates/' . $file))
					{
						$index = strrpos($file, '.');
						$before = substr($file, 0, $index);
						$after = substr($file, $index + 1);
						
						if($after == 'php')
						{
							$templates[] = $before;
						}
					}
				}
			}
		}
		else
		{
			trigger_error("Could not open templates dir");
		}
		return $templates;
	}
}