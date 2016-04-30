<?php
namespace Youppers\ProductBundle\Guesser\FAP;

use Symfony\Component\Config\Definition\Exception\Exception;
use Youppers\ProductBundle\Guesser\TileDimPropertyGuesser;
use Youppers\ProductBundle\Guesser\BaseVariantGuesser;
use Youppers\ProductBundle\Entity\ProductCollection;
use Youppers\ProductBundle\Entity\AttributeType;
use Youppers\ProductBundle\Guesser\BasePropertyGuesser;
use Youppers\ProductBundle\Entity\ProductVariant;
use Youppers\ProductBundle\Guesser\TileItemPropertyGuesser;

class VariantGuesser extends BaseVariantGuesser
{
	protected function getCollectionTypeGuesser(ProductCollection $collection, AttributeType $type)
	{
		if ($type->getCode() == 'DIM') {
			return new DimPropertyGuesser($type,$this->variantPropertyManager,$this->attributeOptionManager);
		}
		if ($type->getCode() == 'EDGE') {
			return new EdgePropertyGuesser($type,$this->variantPropertyManager,$this->attributeOptionManager);
		}
		if ($type->getCode() == 'ITEM') {
			return new TileItemPropertyGuesser($type,$this->variantPropertyManager,$this->attributeOptionManager);
		}
		return parent::getCollectionTypeGuesser($collection, $type);
	}
	
}

class DimPropertyGuesser extends TileDimPropertyGuesser
{

	public function getTypeColumn()
	{
		return 'DIM';
	}

	public function guessProperty(ProductVariant $variant, &$text, AttributeType $type, $textIsValue = false)
	{
		if (preg_match('/([0-9,X]*)(.*)/i',$text,$matches)) {
			$value = $matches[1];
			return parent::guessProperty($variant,$value,$type,true);
		} else {
			return parent::guessProperty($variant,$text,$type,$textIsValue);
		}
	}

}

class EdgePropertyGuesser extends BasePropertyGuesser
{

	public function getTypeColumn()
	{
		return 'DIM';
	}

	public function getDefaultStandardName()
	{
		return 'Bordo Piastrella';
	}

	public function guessProperty(ProductVariant $variant, &$text, AttributeType $type, $textIsValue = false)
	{
		// es: 30X30 RT
		if (preg_match('/([0-9,X]*)(.+)/i',$text,$matches)) {
			$value = trim($matches[2]);
			return parent::guessProperty($variant,$value,$type,true);
		} else {
			return false;
		}
	}

}
