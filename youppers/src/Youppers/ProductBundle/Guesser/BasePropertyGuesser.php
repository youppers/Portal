<?php
namespace Youppers\ProductBundle\Guesser;

use Youppers\ProductBundle\Guesser\AbstractGuesser;
use Youppers\ProductBundle\Entity\ProductVariant;
use Youppers\ProductBundle\Entity\VariantProperty;

abstract class BasePropertyGuesser extends AbstractGuesser
{

	/**
	 * array( value => array( type => array( standard => option) ) )
	 */	
	private $options = null;
	
	protected function getActualOption($variant,$typeCode)
	{
		$actualoption = null;
		foreach ($variant->getVariantProperties() as $property) {
			if ($property->getAttributeOption()->getAttributeStandard()->getAttributeType()->getCode() === $typeCode) {
				$actualoption = $property->getAttributeOption();
				break;
			}
		}
		return $actualoption;
	}
		
	protected function searchOptions($typeCode,$value,$multivalues=null)
	{
		if (is_array($value)) {
			$results = array();
			if (count($value) > 1) {
				$multivalues = implode('/',$value);
			} else {
				$multivalues = null;
			}
			foreach ($value as $v1) {
				$res = $this->searchOptions($typeCode, $v1,$multivalues);
				if ($res !== null) {
					$results[] = $res;
				}  	
			}
			if (!empty($multivalues) && count($results) == 0) {
				$msg = sprintf("Add option of Attribute Type with code=%s : only one of %s",$typeCode,$multivalues);
				$this->getLogger()->warning($msg);
				$this->addTodo($msg);
			}			
			return $results;
		}
		
		if ($this->options === null) {
			$options = $this->getManagerRegistry()->getManager()->getRepository('YouppersProductBundle:AttributeOption')
				->findAll();
			if (count($options) == 0) { 
				throw new \Exception("Unable to retrive options");
			}
			$this->options = array();
			foreach ($options as $option) {
				$standard = $option->getAttributeStandard();
				if (empty($standard)) {
					throw new \Exception("Orphan option: " . $option->getId());
				}
				$s = $standard->getName();
				$type = $standard->getAttributeType();
				$t = $type->getCode();
				$v = $option->getValue();
				if (!array_key_exists($v,$this->options)) {
					$this->options[$v] = array();
				}
				if (!array_key_exists($t,$this->options[$v])) {
					$this->options[$v][$t] = array();
				}				
				$this->options[$v][$t][$s] = $option;
			}
			$this->getLogger()->info(sprintf("Cached %d options",count($options)));
		}
		if (array_key_exists($value,$this->options)) {
			if (array_key_exists($typeCode,$this->options[$value])) {
				return $this->options[$value][$typeCode];
			} else {
				$msg = sprintf("Attribute option with value '%s' not found for attribute type '%s' but for '%s'",
						$value,$typeCode,implode('/',array_keys($this->options[$value])));
				$this->getLogger()->warning($msg);
				$this->addTodo("Warning: " . $msg);
				$this->options[$value][$typeCode] = null;
				return null;
			}
		} else {
			$this->options[$value][$typeCode] = null;
			if (empty($multivalues)) {
				$msg = sprintf("Add option of Attribute Type with code=%s : %s",$typeCode,$value);
				$this->getLogger()->warning($msg);				
				$this->addTodo($msg);				
			}
			return null;
		}	
	}

	public function searchOption($typeCode,$values)
	{
		if (count($values) > 0) {
			$options = $this->searchOptions($typeCode,$values);
		} else {
			return null;
		}
		
		if (count($options) == 0) {
			return null;
		}		
		if (count($options) == 1) {
			if (count($options[0]) > 1) {
				$msg = sprintf("More standars found with values '%s' : '%s'",implode('/',$values),implode('/',array_keys($options[0])));
				$this->getLogger()->error($msg);
				$this->addTodo("Error: " . $msg);
			}
		} 
		if (count($options) > 1) {
			$msg = sprintf("More options found searching values '%s' : '%s'",implode('/',$values),implode('/',$options));
			$this->getLogger()->error($msg);
			$this->addTodo("Error: " . $msg);
		}		
		return array_pop($options[0]);			
	}	
}