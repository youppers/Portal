<?php

namespace Youppers\ProductBundle\Guesser;

use Youppers\ProductBundle\Entity\ProductVariant;
use Youppers\ProductBundle\Entity\AttributeType;

class IgnorePropertyGuesser extends BasePropertyGuesser
{

	protected function getDefaultOption(ProductVariant $variant, AttributeType $type)
	{
		return false;
	}

}