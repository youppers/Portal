<?php
namespace Youppers\ProductBundle\Guesser;

use Youppers\ProductBundle\Guesser\AbstractDimensionPropertyGuesser;
use Youppers\ProductBundle\Guesser\BasePropertyGuesser;
use Youppers\ProductBundle\Guesser\BaseVariantGuesser;
use Youppers\ProductBundle\Entity\ProductCollection;
use Youppers\ProductBundle\Entity\AttributeType;
use Youppers\ProductBundle\Entity\ProductVariant;
use Youppers\ProductBundle\Manager\AttributeOptionManager;
use Youppers\ProductBundle\Manager\VariantPropertyManager;

class TapVariantGuesser extends BaseVariantGuesser
{
	protected function getCollectionTypeGuesser(ProductCollection $collection, AttributeType $type)
	{
		if ($type->getCode() == 'COLOR' && $collection->getProductType()->getCode() == 'TAP') {
			return new TapColorPropertyGuesser($type,$this->variantPropertyManager,$this->attributeOptionManager);
		}
		if ($type->getCode() == 'ITEM' && $collection->getProductType()->getCode() == 'TAP') {
			return new TapItemPropertyGuesser($type,$this->variantPropertyManager,$this->attributeOptionManager);
		}
		if ($type->getCode() == 'INST' && $collection->getProductType()->getCode() == 'TAP') {
			return new TapInstPropertyGuesser($type,$this->variantPropertyManager,$this->attributeOptionManager);
		}

		return parent::getCollectionTypeGuesser($collection, $type);
	}
	
}

class TapColorPropertyGuesser extends BasePropertyGuesser
{

	public function getDefaultStandardName()
	{
		return 'Rubinetti';
	}

}

class TapItemPropertyGuesser extends BasePropertyGuesser
{

	public function getDefaultStandardName()
	{
		return 'Rubinetti';
	}

}

class TapInstPropertyGuesser extends BasePropertyGuesser
{

	public function getDefaultStandardName()
	{
		return 'Rubinetti';
	}

}
