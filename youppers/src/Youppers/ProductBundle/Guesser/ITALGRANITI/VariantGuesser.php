<?php
namespace Youppers\ProductBundle\Guesser\ITALGRANITI;

use Symfony\Component\Config\Definition\Exception\Exception;
use Youppers\ProductBundle\Guesser\TileDimPropertyGuesser;
use Youppers\ProductBundle\Guesser\BaseVariantGuesser;
use Youppers\ProductBundle\Entity\ProductCollection;
use Youppers\ProductBundle\Entity\AttributeType;
use Youppers\ProductBundle\Entity\ProductVariant;

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

class DimPropertyGuesser extends TileDimPropertyGuesser
{

	public function getTypeColumn()
	{
		return 'formato';
	}

	public function guessProperty(ProductVariant $variant, &$text, AttributeType $type, $textIsValue = false)
	{
		// 1200X 330 , 20  X  50,
		if (preg_match("/^([0-9]{2,4})[\s]{0,2}X[\s]{0,2}([0-9]{2,4})$/",$text,$matches)) {
			$value = intval($matches[1]) . 'X' . intval($matches[2]);
			return parent::guessProperty($variant,$value,$type,true);
		} else {
			return parent::guessProperty($variant,$text,$type,$textIsValue);
		}
	}

}
