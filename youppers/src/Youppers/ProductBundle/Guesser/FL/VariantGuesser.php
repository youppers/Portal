<?php
namespace Youppers\ProductBundle\Guesser\FL;

use Youppers\ProductBundle\Guesser\BaseVariantGuesser;
use Youppers\ProductBundle\Entity\ProductCollection;
use Youppers\ProductBundle\Entity\AttributeType;
use Youppers\ProductBundle\Guesser\BasePropertyGuesser;
use Youppers\ProductBundle\Entity\ProductVariant;
use Doctrine\Common\Collections\Criteria;
use Youppers\ProductBundle\Guesser\IgnorePropertyGuesser;

class VariantGuesser extends BaseVariantGuesser
{
	protected function getCollectionTypeGuesser(ProductCollection $collection, AttributeType $type)
	{
		if ($type->getCode() == 'FIN') {
			return new FinPropertyGuesser($type,$this->variantPropertyManager);
		}
		if ($type->getCode() == 'MIX' && $collection->getCode() != 'VETRO') {
			return new IgnorePropertyGuesser($type,$this->variantPropertyManager);
		}
		return parent::getCollectionTypeGuesser($collection, $type);
	}
	
}

class FinPropertyGuesser extends BasePropertyGuesser
{
	protected function getDefaultOption(ProductVariant $variant, AttributeType $type)
	{
        parent::getDefaultOption($variant,$type);
		if (null === $this->defaultOption) {
			$standard = $variant->getProductCollection()->getStandards()
				->matching(Criteria::create()->where(Criteria::expr()->eq("attributeType", $type)))->first();
			if (empty($standard)) {
				$this->defaultOption = false;				
			} else {
				$this->defaultOption = $standard
					->getAttributeOptions()
					->matching(Criteria::create()->where(Criteria::expr()->eq("value", 'Naturale')))->first()
				;
			}
		}
		return $this->defaultOption; 
	}
}

