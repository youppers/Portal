<?php
namespace Youppers\ProductBundle\Guesser;

use Youppers\ProductBundle\Guesser\AbstractGuesser;
use Youppers\ProductBundle\Entity\ProductVariant;
use Youppers\ProductBundle\Entity\VariantProperty;
use Youppers\ProductBundle\Entity\ProductCollection;
use Youppers\ProductBundle\Entity\AttributeType;
use Youppers\ProductBundle\Manager\VariantPropertyManager;
use Youppers\ProductBundle\Entity\AttributeOption;

class BasePropertyGuesser extends AbstractGuesser
{

	private $type;
	
	protected $variantPropertyManager;
	
	public function __construct(AttributeType $type, VariantPropertyManager $variantPropertyManager)
	{
		$this->type = $type;
		$this->variantPropertyManager = $variantPropertyManager;
	}
	
	public function guessVariant(ProductVariant $variant, &$text) {
		return $this->guessProperty($variant, $text, $this->type);
	}
	
	/**
	 * array( value => array( type => array( standard => option) ) )
	 */	
	private $options = null;
	
	protected function getActualOption(ProductVariant $variant,AttributeType $type)
	{
		$actualoption = null;
		foreach ($variant->getVariantProperties() as $property) {
			if ($property->getAttributeOption()->getAttributeStandard()->getAttributeType() === $type) {
				$actualoption = $property->getAttributeOption();
				break;
			}
		}
		return $actualoption;
	}
	
	private $collectionOptions = array();
	
	protected function getCollectionOptions(ProductCollection $collection, AttributeType $type)
	{
		if (!array_key_exists($collection->getId(),$this->collectionOptions)) {
			$this->collectionOptions[$collection->getId()] = array();
		}
		if (!array_key_exists($type->getId(),$this->collectionOptions[$collection->getId()])) {
			$options = array();
			foreach ($collection->getStandards()->getValues() as $standard) {
				if ($standard->getAttributeType() == $type) {
					//$options = array_merge($options,$standard->getAttributeOptions()->getValues());
					foreach ($standard->getAttributeOptions()->getValues() as $option) {
						foreach (explode(';',$option->getAlias()) as $alias) {
							if (!empty($alias)) {
								$options[$alias] = $option;
							}
						}
						$options[$option->getValue()] = $option;
					}
				}
			}
			$this->collectionOptions[$collection->getId()][$type->getId()] = $options;
			if (count($options) == 0) {
				$this->getLogger()->warning(sprintf("Collection '%s' don't have assigned standards for type '%s'",$collection,$type));
				$todo = sprintf("<error>Add standard</error> of type <info>%s</info> for collection <info>%s</info> then redo guessing.",$type,$collection);
				$this->addTodo($todo);
			} else {
				$this->getLogger()->info(sprintf("Cached %d options of type '%s' for collection '%s'",count($options),$type,$collection));
			}
		}
		return $this->collectionOptions[$collection->getId()][$type->getId()];
	}	

	protected function guessProperty(ProductVariant $variant, &$text, $type)
	{
		$actualOption = $this->getActualOption($variant, $type);
	
		$options = $this->getCollectionOptions($variant->getProductCollection(), $type);
		foreach ($options as $value => $option) {
			//if (stripos($text,$value) !== false) {
			if (preg_match("/[\s\.]+" . preg_quote($value,'/') . "[\s\.]+/i", " ".$text." ")) {
				
				if ($actualOption) {
					if ($option === $actualOption) {
						$this->getLogger()->debug(sprintf("Variant '%s' guessed property '%s' match actual",$variant,$option));
					} else {
						$this->getLogger()->info(sprintf("Variant '%s' guessed property '%s' don't match actual '%s'",$variant,$option,$actualOption));
						$todo = sprintf("<error>Change property</error> <info>%s</info> from <info>%s</info> to <info>%s</info> in <info>%s</info>",
								$option->getAttributeType(),$actualOption->getValueWithSymbol(),$option->getValueWithSymbol(),$variant);
						$this->addTodo($todo);
						return;
					}
				} else {
					if ($this->getForce()) {
						$this->addVariantProperty($variant,$option);
					} else {
						$todo = sprintf("<error>Add property</error> <info>%s</info> to <info>%s</info>",$option,$variant);
						$this->addTodo($todo);
					}
					$this->getLogger()->info(sprintf("Variant '%s' new guessed property '%s'",$variant,$option));
				}
				$text = str_replace($option->getValue(),'',$text);
				return;
			}
		}
		if (empty($actualOption)) {
			if ($this->hasDefaultOption($variant,$type)) {
				$option = $this->getDefaultOption($variant,$type);
				if ($option === false) {
					$this->getLogger()->debug(sprintf("Default option false for '%s' type '%s'",$variant,$type));
				} else {
					if ($this->getForce()) {
						$this->addVariantProperty($variant,$this->getDefaultOption($variant,$type));
					} else {
						$todo = sprintf("<error>Add default property</error> <info>%s</info> to <info>%s</info>",$option,$variant);
						$this->addTodo($todo);				
					}
				}
			} elseif (count($options)) {
				$todo = sprintf("Add property of type <info>%s</info> to <info>%s</info>",$type,$variant);
				$this->addTodo($todo);
			}
		} else {
			$this->getLogger()->debug(sprintf("Variant '%s' actual %s is '%s'",$variant,$type,$actualOption));
		}
	}

	protected function addVariantProperty(ProductVariant $variant,AttributeOption $option)
	{
		$variantProperty = $this->variantPropertyManager->create();
		$variantProperty->setProductVariant($variant);
		$variantProperty->setAttributeOption($option);
		$variantProperty->setPosition(1 + count($variant->getVariantProperties()));
		$variant->addVariantProperty($variantProperty);
		$this->variantPropertyManager->save($variantProperty,false);		
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
	
	protected function hasDefaultOption(ProductVariant $variant, AttributeType $type)
	{
		return null !== $this->getDefaultOption($variant,$type);
	}
	
	/**
	 * Override this to provide a default
	 * 
	 * @return AttributeOption
	 */
	protected function getDefaultOption(ProductVariant $variant, AttributeType $type)
	{
		return null;
	}

}