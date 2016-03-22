<?php
namespace Youppers\ProductBundle\Guesser;

use Youppers\ProductBundle\Entity\AttributeStandard;
use Youppers\ProductBundle\Entity\AttributeType;
use Youppers\ProductBundle\Manager\AttributeOptionManager;
use Youppers\ProductBundle\Manager\VariantPropertyManager;

class BaseDimensionPropertyGuesser extends BasePropertyGuesser
{	
	public function __construct(AttributeType $type, VariantPropertyManager $variantPropertyManager, AttributeOptionManager $attributeOptionManager)
	{
		parent::__construct($type, $variantPropertyManager, $attributeOptionManager);
		$this->autoAddOptions = true;
	}

	public function getDefaultStandardName()
	{
		return 'Lato x Lato in cm';
	}

	const PATTERN = '/^([0-9]+\.?[0-9]*)\s*x\s*([0-9]+\.?[0-9]*)$/i';

	public function normalizeValue($value,AttributeStandard $standard)
	{
		if (preg_match(self::PATTERN,$value,$matches)) {
			return $matches[1] . 'x' . $matches[2];
		} else {
			throw new \InvalidArgumentException("Value must match " . self::PATTERN);
		}
	}

}