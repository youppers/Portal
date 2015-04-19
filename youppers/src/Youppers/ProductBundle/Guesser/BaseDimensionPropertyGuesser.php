<?php
namespace Youppers\ProductBundle\Guesser;

use Youppers\ProductBundle\Guesser\AbstractGuesser;
use Youppers\ProductBundle\Entity\ProductVariant;
use Youppers\ProductBundle\Entity\VariantProperty;

class BaseDimensionPropertyGuesser extends BasePropertyGuesser
{

	const TYPE_DIMENSION = 'DIM';

	protected function guessDimension(ProductVariant $variant, &$text)
	{		
		$actualDimension = $this->getActualOption($variant, self::TYPE_DIMENSION);
		$this->getLogger()->debug(sprintf("Variant '%s' actual dimension '%s'",$variant,$actualDimension));
		
		$values = array();
		$a = array();
		if (preg_match("/(\s+)(\d*,)?(\d+)(\s+)(mm)/i", $text, $a)) {			
			$this->getLogger()->debug("Matched and stripped a tickness: " . $a[0]);
			$text = str_replace($a[0],'',$text);
		}
		if (preg_match("/(\d*,)?(\d+)[xX](\d*,)?(\d+)/i", $text, $a)) {
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
		} elseif (preg_match("/\s+(\d*,)?(\d+)(\s+|cm)/i", $text, $a)) {
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
			$this->getLogger()->debug(sprintf("Dimension not guessed in '%s' for '%s'",$text,$variant));
			return null;				
		}
			
		$values = array_unique($values);
		
		$newDimension = $this->searchOption(self::TYPE_DIMENSION, $values);
		
		if ($newDimension) {
			if (empty($actualDimension)) {
				$this->getLogger()->info(sprintf("Variant '%s' new guessed dimension '%s'",$variant,$newDimension));
				$variantProperty = new VariantProperty();
				$variantProperty->setProductVariant($variant);
				$variantProperty->setAttributeOption($newDimension);
				$variantProperty->setPosition(1 + count($variant->getVariantProperties()));
				$variant->addVariantProperty($variantProperty);
				$em = $this->managerRegistry->getManager();
				$em->persist($variantProperty);
				$em->flush();
				$text = str_replace($a[0],'',$text);				
			} elseif ($actualDimension == $newDimension) {
				$this->getLogger()->debug(sprintf("Variant '%s' actual dimension matches guessed '%s'",$variant,$newDimension));
				$text = str_replace($a[0],'',$text);				
			} else {
				$msg = sprintf("Variant '%s' actual dimension '%s' not matching with guessed '%s'",$variant,$actualDimension,$newDimension);
				$this->getLogger()->error($msg);
				$this->addTodo("Error: " . $msg);
			}
		} elseif (empty($actualDimension)) {
			$msg = sprintf("Variant '%s' dimension not guessed searching '%s'",$variant,implode('/',$values));
			$this->getLogger()->warning($msg);
			$this->addTodo("Warning: " . $msg);
		} else {
			$msg = sprintf("Variant '%s' dimension not guessed, but already set '%s'",$variant,$actualDimension);
			$this->getLogger()->info($msg);
			$this->addTodo("Check: " . $msg);
		}
		
	} 
	
	/* (non-PHPdoc)
	 * @see \Youppers\ProductBundle\Guesser\AbstractGuesser::guessVariant()
	 */
	public function guessVariant(ProductVariant $variant, &$text) {		
		$this->guessDimension($variant, $text);
	}

}