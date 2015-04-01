<?php

namespace Youppers\CompanyBundle\Loader;

use JMS\Serializer\Annotation as JMS;

class LoaderMapper
{

	protected $loader;
	
	protected $mapping = array();

	/**
	 * @JMS\Type("array<string, string>")
	*/
	protected $data;

	public function __construct($mapping)
	{
		$this->mapping = $mapping;
	}
	
	public function setData($data)
	{
		$this->data=$data;
	}

	public function get($what)
	{
		if (array_key_exists($what,$this->mapping)) {
			$key = $this->mapping[$what];
		} elseif (array_key_exists($what,$this->data)) {
			$key = $what;
		} else {
			return null;
		}
		if (is_object($key) && ($key instanceof \Closure)) {
			$res = $key->__invoke($this->data);
			return $res;
		}
		if (array_key_exists($key,$this->data)) {
			return $this->data[$key];
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
		if (array_key_exists($what,$this->mapping)) {
			$key = $this->mapping[$what];
		} elseif (array_key_exists($what,$this->data)) {
			$key = $what;
		} else {
			return null;
		}
		if (is_object($key) && ($key instanceof \Closure)) {
			return $key->__invoke($this->data);
		}
		if (array_key_exists($key,$this->data)) {
			$value = $this->data[$key];
			unset($this->data[$key]);
			return $value;
		} else {
			return null;
		}
	}

	public function key($what)
	{
		if (array_key_exists($what,$this->mapping)) {
			$key = $this->mapping[$what];
		} elseif (array_key_exists($what,$this->data)) {
			$key = $what;
		} else {
			return null;
		}
		if (is_object($key) && ($key instanceof \Closure)) {
			return 'Closure';
		}
		return $key;		
	}

	public function __toString()
	{
		return var_export($this->mapping, true);
	}
}
