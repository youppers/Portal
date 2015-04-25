<?php
namespace Youppers\ProductBundle\Guesser;

use Youppers\ProductBundle\Guesser\AbstractGuesser;
use Youppers\ProductBundle\Entity\ProductVariant;
use Youppers\ProductBundle\Entity\VariantProperty;
use Youppers\ProductBundle\Entity\ProductCollection;

class BaseRettificaPropertyGuesser extends BasePropertyGuesser
{
	
	public function guessVariant(ProductVariant $variant, &$text) {
		return $this->guessVariantType($variant, $text, 'RETTIFICATO');
	}
		
}