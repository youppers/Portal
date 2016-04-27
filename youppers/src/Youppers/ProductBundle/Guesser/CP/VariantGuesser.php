<?php
namespace Youppers\ProductBundle\Guesser\CP;

use Youppers\ProductBundle\Guesser\BaseDimensionPropertyGuesser;
use Youppers\ProductBundle\Guesser\BaseVariantGuesser;
use Youppers\ProductBundle\Entity\ProductCollection;
use Youppers\ProductBundle\Entity\AttributeType;
use Youppers\ProductBundle\Entity\ProductVariant;
use Youppers\ProductBundle\Manager\VariantPropertyManager;
use Youppers\ProductBundle\Manager\AttributeOptionManager;

class VariantGuesser extends BaseVariantGuesser
{
	protected function getCollectionTypeGuesser(ProductCollection $collection, AttributeType $type)
	{
		if ($type->getCode() == 'DIM') {
			return new DimPropertyGuesser($type,$this->variantPropertyManager,$this->attributeOptionManager);
		}
		return parent::getCollectionTypeGuesser($collection, $type);
	}
	
}

class DimPropertyGuesser extends BaseDimensionPropertyGuesser
{

	public function __construct(AttributeType $type, VariantPropertyManager $variantPropertyManager, AttributeOptionManager $attributeOptionManager)
	{
		parent::__construct($type, $variantPropertyManager, $attributeOptionManager);
		$this->autoAddOptions = true;
	}

	public function getTypeColumn()
	{
		return 'FORMATO';
	}

	public function getDefaultStandardName()
	{
		return 'Lato x Lato in cm';
	}

	public function guessProperty(ProductVariant $variant, &$text, AttributeType $type, $textIsValue = false)
	{
		if (preg_match('/([0-9,]*\s{0,1}x\s{0,1}[0-9,]*)([^0-9,X]*)/i',$text,$matches)) {
			$value = $matches[1];
			return parent::guessProperty($variant,$value,$type,true);
		} else {
			return parent::guessProperty($variant,$text,$type,$textIsValue);
		}
	}

}
