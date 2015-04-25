<?php
namespace Youppers\ProductBundle\Guesser;

use Youppers\ProductBundle\Guesser\AbstractGuesser;
use Youppers\ProductBundle\Entity\ProductVariant;
use Youppers\ProductBundle\Entity\VariantProperty;
use Youppers\ProductBundle\Entity\AttributeType;

class BaseDimensionPropertyGuesser extends BasePropertyGuesser
{	
	/* (non-PHPdoc)
	 * @see \Youppers\ProductBundle\Guesser\AbstractGuesser::guessVariant()
	 */
	public function guessVariant(ProductVariant $variant, &$text) {
		$type = $this->getAttributeTypeByCode('DIM');		
		$this->guessProperty($variant, $text, $type);
	}

}