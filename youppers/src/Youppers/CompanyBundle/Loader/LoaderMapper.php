<?php

namespace Youppers\CompanyBundle\Loader;

use JMS\Serializer\Annotation as JMS;
use Application\Sonata\ProductBundle\Entity\Package;

/**
 * 
 * @author sergio
 * @JMS\ExclusionPolicy("all")
 */
class LoaderMapper
{

	protected $loader;
	
	protected $mapping = array();

	/**
	 * @JMS\Type("array<string, string>")
	 * @JMS\Expose()
	*/
	protected $data;

	public function __construct($mapping)
	{
		$this->mapping = $mapping;
	}
	
	public function setData($data)
	{
		$this->data=$data;
		return $this;
	}
	
	public function getData()
	{
		return $this->data;
	}
	
	public function get($what,$returnKey=false,$remove=false)
	{
		if (array_key_exists($what,$this->mapping)) {
			$key = $this->mapping[$what];
		} elseif (array_key_exists($what,$this->data)) {
			$key = $what;
		} else {
			return null;
		}
		if (is_object($key) && ($key instanceof \Closure)) {
			if ($returnKey) {
				return 'Closure';
			} else {
				return $key->__invoke($this->data);
			}
		}
		if ($returnKey) {
			if (is_array($key)) {
				return print_r($key,true);
			}
			return $key;
		}
		if ($key === false) {
			return null; 
		}
		if (is_array($key)) {
			$res = array();
			foreach ($key as $what1 => $key1) {
				if (array_key_exists($key1,$this->data)) {
					$res[$what1] = trim($this->data[$key1]);
					if ($remove) {
						unset($this->data[$key1]);						
					}
				}
			}
			return $res;			
		}
		if (array_key_exists($key,$this->data)) {
			$value = trim($this->data[$key]);
			if ($remove) {
				unset($this->data[$key]);
			}
			return $value;
		} else {
			return null;
		}
	}

	/**
	 * Return a value and removed it from the data  
	 * @param string $what field name
	 * @return NULL|unknown The value
	 */
	public function remove($what)
	{
		return $this->get($what,false,true);		
	}

	public function key($what)
	{
		return $this->get($what,true);
	}

	public function __toString()
	{
		return var_export($this->mapping, true);
	}
}
