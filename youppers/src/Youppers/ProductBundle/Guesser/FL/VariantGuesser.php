<?php
namespace Youppers\ProductBundle\Guesser\FL;

use Youppers\ProductBundle\Guesser\BaseDimensionPropertyGuesser;
use Youppers\ProductBundle\Guesser\BaseVariantGuesser;
use Youppers\ProductBundle\Entity\ProductCollection;
use Youppers\ProductBundle\Entity\AttributeType;
use Youppers\ProductBundle\Guesser\BasePropertyGuesser;
use Youppers\ProductBundle\Entity\ProductVariant;
use Doctrine\Common\Collections\Criteria;
use Youppers\ProductBundle\Guesser\IgnorePropertyGuesser;
use Youppers\ProductBundle\Guesser\TileItemPropertyGuesser;
use Youppers\ProductBundle\Manager\AttributeOptionManager;
use Youppers\ProductBundle\Manager\VariantPropertyManager;

class VariantGuesser extends BaseVariantGuesser
{
	protected function getCollectionTypeGuesser(ProductCollection $collection, AttributeType $type)
	{
		if ($type->getCode() == 'DIM') {
			return new DimPropertyGuesser($type,$this->variantPropertyManager,$this->attributeOptionManager);
		}
		if ($type->getCode() == 'FIN') {
			return new FinPropertyGuesser($type,$this->variantPropertyManager,$this->attributeOptionManager);
		}
		if ($type->getCode() == 'MIX' && $collection->getCode() != 'VETRO') {
			return new IgnorePropertyGuesser($type,$this->variantPropertyManager,$this->attributeOptionManager);
		}
		if ($type->getCode() == 'ITEM') {
			return new TileItemPropertyGuesser($type,$this->variantPropertyManager,$this->attributeOptionManager);
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

class DimPropertyGuesser extends BaseDimensionPropertyGuesser
{

	public function __construct(AttributeType $type, VariantPropertyManager $variantPropertyManager, AttributeOptionManager $attributeOptionManager)
	{
		parent::__construct($type, $variantPropertyManager, $attributeOptionManager);
		$this->autoAddOptions = true;
	}

	public function getTypeColumn()
	{
		return 'Formato Nominale/Size';
	}

	public function getDefaultStandardName()
	{
		return 'Lato x Lato in cm';
	}

	public function guessProperty(ProductVariant $variant, &$text, AttributeType $type, $textIsValue = false)
	{
		if ($textIsValue && preg_match("/^([0-9,]+)X([0-9,]+)/",$text,$matches)) {
			$value = $matches[1] . 'X' . $matches[2];
			return parent::guessProperty($variant,$value,$type,true);
		} else {
			return parent::guessProperty($variant,$text,$type,false);
		}
	}

}



