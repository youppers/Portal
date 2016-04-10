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

	const PATTERN = '/^([0-9]+[,\.]?[0-9]*)\s*x\s*([0-9]+[,\.]?[0-9]*)$/i';

	public function normalizeValue($value,AttributeStandard $standard = null)
	{
		if (preg_match(self::PATTERN,$value,$matches)) {
			$matches[1]=preg_replace('/^0+/','',$matches[1]);
			$matches[2]=preg_replace('/^0+/','',$matches[2]);
			$normalized = str_replace(".",",",$matches[1] . 'X' . $matches[2]);
			if ($this->getDebug()) {
				$this->getLogger()->debug(sprintf("Dimension value '%s' normalized '%s'",$value,$normalized));
			}
			return $normalized;
		} else {
			if (empty($standard)) {
				$this->getLogger()->warning(sprintf("Value '%s' should match '%s'",$value,self::PATTERN));
				return parent::normalizeValue($value,$standard);
			} else {
				$this->getLogger()->error(sprintf("Value '%s' must match '%s'",$value,self::PATTERN));
				return null;
			}
		}
	}

}