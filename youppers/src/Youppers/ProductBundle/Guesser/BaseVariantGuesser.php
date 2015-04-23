<?php
namespace Youppers\ProductBundle\Guesser;

use Youppers\ProductBundle\Guesser\AbstractGuesser;
use Youppers\ProductBundle\Entity\ProductVariant;
use Youppers\ProductBundle\Entity\VariantProperty;

abstract class BaseVariantGuesser extends AbstractGuesser
{
		
	public function guessVariant(ProductVariant $variant, $guessers) {
		$product = $variant->getProduct();
		if (empty($product)) {
			$this->logger->critical("Variant without product: " . $variant->getId());
			return;
		}
		$text = $variant->getProduct()->getName();
		foreach ($guessers as $guesser) {
			if ($this->debug) dump($text);
			$guesser->guessVariant($variant, $text);
			if ($this->debug) dump($text);
		}
	}

}