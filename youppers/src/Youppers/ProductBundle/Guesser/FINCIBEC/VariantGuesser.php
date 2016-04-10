<?php
namespace Youppers\ProductBundle\Guesser\FINCIBEC;

use Symfony\Component\Config\Definition\Exception\Exception;
use Youppers\ProductBundle\Guesser\BaseDimensionPropertyGuesser;
use Youppers\ProductBundle\Guesser\BaseVariantGuesser;
use Youppers\ProductBundle\Entity\ProductCollection;
use Youppers\ProductBundle\Entity\AttributeType;
use Youppers\ProductBundle\Guesser\BasePropertyGuesser;
use Youppers\ProductBundle\Entity\ProductVariant;
use Doctrine\Common\Collections\Criteria;
use Youppers\ProductBundle\Guesser\IgnorePropertyGuesser;
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
}
