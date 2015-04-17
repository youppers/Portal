<?php
namespace Youppers\ProductBundle\Guesser;

use Youppers\ProductBundle\Guesser\AbstractGuesser;
use Youppers\ProductBundle\Entity\ProductVariant;
use Youppers\ProductBundle\Entity\VariantProperty;

class BaseVariantGuesser extends AbstractGuesser
{

	private $options = null;
	
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
			return $results;
		}
		if ($this->options === null) {
			$options = $this->managerRegistry->getManager()->getRepository('YouppersProductBundle:AttributeOption')
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
			$this->logger->info(sprintf("Cached %d options",count($options)));
		}
		if (array_key_exists($value,$this->options)) {
			if (array_key_exists($typeCode,$this->options[$value])) {
				return $this->options[$value][$typeCode];
			} else {
				$msg = sprintf("Attribute option with value '%s' not found for attribute type '%s'",$value,$typeCode);
				$this->logger->error($msg);
				$this->addTodo("Error: " . $msg);
				$this->options[$value][$typeCode] = null;
				return null;
			}
		} else {
			$this->logger->debug(sprintf("Attribute option with value '%s' not found (searching type '%s')",$value,$typeCode));
			$this->options[$value][$typeCode] = null;
			if ($multivalues) {
				$this->addTodo(sprintf("Add option of Attribute Type with code=%s : only one of %s",$typeCode,$multivalues));				
			} else {
				$this->addTodo(sprintf("Add option of Attribute Type with code=%s : %s",$typeCode,$value));				
			}
			return null;
		}	
	}
	
	protected function guessDimension(ProductVariant $variant)
	{
		$productName = $variant->getProduct()->getName();
		$values = array();
		if (preg_match("/(\d*,)?(\d+)[xX](\d*,)?(\d+)/i", $productName, $a)) {
			if (empty($a[1])) {
				$v1 = $a[2];
			} else {
				$a[1] = preg_replace('/,/', '', $a[1]);				
				$v1 = floatval($a[1] . self::DOT . $a[2]);
			}
			if (empty($a[3])) {
				$v2 = $a[4];
			} else {
				$a[3] = preg_replace('/,/', '', $a[3]);
				$v2 = floatval($a[3] . self::DOT . $a[4]);
			}
			
			if (empty($a[1]) && empty($a[3])) { // 25x30					
				$values[] = $v1 . self::PER . $v2;
			} else {
				if (!empty($a[1]) && empty($a[3])) { // 23,6x30
					$values[] = $a[1] . self::COMMA . $a[2] . self::PER . $v2; // 23,6x30				
					$values[] = $a[1] . self::DOT . $a[2] . self::PER . $v2; // 23.6x30
					$values[] = ceil($v1) . self::PER . $v2; // 24x30
					$values[] = floor($v1) . self::PER . $v2; // 25x30
				}
	
				if (empty($a[1]) && !empty($a[3])) { // 25x30,8
					$values[] = $v1 . self::PER . $a[3] . self::COMMA . $a[4]; // 25x30,8
					$values[] = $v1 . self::PER . $a[3] . self::DOT . $a[4]; // 25x30.8
					$values[] = $v1 . self::PER . ceil($v2); // 25x31
					$values[] = $v1 . self::PER . floor($v2); // 25x30
				}
								
				if (!empty($a[1] && !empty($a[3]))) { // 23,6x30,8
					$values[] = $a[1] . self::COMMA . $a[2] . self::PER . $a[3] . self::COMMA . $a[4]; // 23,6x30,8
					$values[] = $a[1] . self::COMMA . $a[2] . self::PER . $a[3] . self::DOT . $a[4]; // 23,6x30.8
					$values[] = $a[1] . self::DOT . $a[2] . self::PER . $a[3] . self::COMMA . $a[4]; // 23.6x30,8
					$values[] = $a[1] . self::DOT . $a[2] . self::PER . $a[3] . self::DOT . $a[4]; // 23.6x30.8							
					
					$values[] = $a[1] . self::COMMA . $a[2] . self::PER . ceil($v2); // 23,6x31				
					$values[] = $a[1] . self::COMMA . $a[2] . self::PER . floor($v2); // 23,6x30				
					$values[] = $a[1] . self::DOT . $a[2] . self::PER . ceil($v2); // 23.6x31
					$values[] = $a[1] . self::DOT . $a[2] . self::PER . floor($v2); // 23.6x30
	
					$values[] = ceil($v1) . self::PER . $a[3] . self::COMMA . $a[4]; // 24x30,8
					$values[] = floor($v1) . self::PER . $a[3] . self::COMMA . $a[4]; // 23x30,8
					$values[] = ceil($v1) . self::PER . $a[3] . self::DOT . $a[4]; // 24x30.8
					$values[] = floor($v1) . self::PER . $a[3] . self::DOT . $a[4]; // 23x30.8
													
					$values[] = ceil($v1) . self::PER . ceil($v2); // 24x31
					$values[] = floor($v1) . self::PER . ceil($v2); // 23x31
					$values[] = ceil($v1) . self::PER . floor($v2); // 24x30
					$values[] = floor($v1) . self::PER . floor($v2); // 23x30
				}
			}						
		} elseif (preg_match("/\s+(\d*,)?(\d+)(\s+|cm)/i", $productName, $a)) {
			if (!empty($a[1])) {
				$a[1] = preg_replace('/,/', '', $a[1]);
				$v = floatval($a[1] . self::DOT . $a[2]);
				$values[] = $a[1] . self::COMMA . $a[2];
				$values[] = $a[1] . self::DOT . $a[2];
				$values[] = strval(ceil($v)); 
				$values[] = strval(floor($v));
			} else {
				$values[] = $a[2];
			}			
		} else {
			$this->logger->debug(sprintf("Dimension not found in product name for '%s'",$variant));
			return null;				
		}
			
		$values = array_unique($values);
		
		if (count($values) > 0) {
			$options = $this->searchOptions(self::TYPE_DIMENSION,$values);
		} else {
			$options = array();
		}
			
		if (count($options) == 1) {
			if (count($options[0]) > 1) {
				$this->logger->error(sprintf("More standars found with values '%s'",implode('/',$values)));
			}
			return array_pop($options[0]);
		} elseif (count($options) > 1) {
			$this->logger->error(sprintf("More dimensions found with values '%s'",implode('/',$values)));
			// TODO more than one standard
			return null;
		} elseif (count($values) > 0) {
			$msg = sprintf("Dimension '%s' not found for '%s'",implode('/',$values),$productName);
			$this->logger->warning($msg);
			$this->addTodo("Add: " . $msg);				
		} else {
			// TODO none
			return null;
		}		
	} 
	
	/* (non-PHPdoc)
	 * @see \Youppers\ProductBundle\Guesser\AbstractGuesser::guessVariant()
	 */
	protected function guessVariant(ProductVariant $variant) {

		$actualDimension = null;
		foreach ($variant->getVariantProperties() as $property) {
			if ($property->getAttributeOption()->getAttributeStandard()->getAttributeType()->getCode() === self::TYPE_DIMENSION) {
				$actualDimension = $property->getAttributeOption();
				$this->logger->debug(sprintf("Variant '%s' actual dimension '%s'",$variant,$actualDimension));
				break;
			}
		}			
		
		$newDimension = $this->guessDimension($variant);
		if ($newDimension) {
			if (empty($actualDimension)) {
				$this->logger->info(sprintf("Variant '%s' new guessed dimension '%s'",$variant,$newDimension));
				$variantProperty = new VariantProperty();
				$variantProperty->setProductVariant($variant);
				$variantProperty->setAttributeOption($newDimension);
				$variantProperty->setPosition(1 + count($variant->getVariantProperties()));
				$variant->addVariantProperty($variantProperty);
				$em = $this->managerRegistry->getManager();
				$em->persist($variantProperty);
				$em->flush();				
			} elseif ($actualDimension == $newDimension) {
				$this->logger->debug(sprintf("Variant '%s' actual dimension matches guessed '%s'",$variant,$newDimension));
			} else {
				$msg = sprintf("Variant '%s' actual dimension '%s' not matching with guessed '%s'",$variant,$actualDimension,$newDimension);
				$this->logger->error($msg);
				$this->addTodo("Error: " . $msg);
				//dump($newDimension); dump($actualDimension); die;
			}
		} elseif (empty($actualDimension)) {
			$this->logger->debug(sprintf("Dimension not found for '%s'",$variant));				
		} else {
			$msg = sprintf("Variant '%s' dimension not guessed, but already set '%s'",$variant,$actualDimension);
			$this->logger->debug($msg);
			$this->addTodo("Check: " . $msg);				
		}
	}

}