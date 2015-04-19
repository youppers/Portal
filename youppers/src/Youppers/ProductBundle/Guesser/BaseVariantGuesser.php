<?php
namespace Youppers\ProductBundle\Guesser;

use Youppers\ProductBundle\Guesser\AbstractGuesser;
use Youppers\ProductBundle\Entity\ProductVariant;
use Youppers\ProductBundle\Entity\VariantProperty;

abstract class BaseVariantGuesser extends AbstractGuesser
{
		
	public function guessVariant(ProductVariant $variant, $guessers) {
		$text = $variant->getProduct()->getName();
		foreach ($guessers as $guesser) {
			dump($text);
			$guesser->guessVariant($variant, $text);
			dump($text);
		}
	}

}