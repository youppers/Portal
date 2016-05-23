<?php
namespace Youppers\ProductBundle\Guesser\CRS;

use Youppers\ProductBundle\Guesser\AbstractDimensionPropertyGuesser;
use Youppers\ProductBundle\Guesser\BasePropertyGuesser;
use Youppers\ProductBundle\Guesser\BaseVariantGuesser;
use Youppers\ProductBundle\Entity\ProductCollection;
use Youppers\ProductBundle\Entity\AttributeType;
use Youppers\ProductBundle\Entity\ProductVariant;
use Youppers\ProductBundle\Guesser\TapVariantGuesser;
use Youppers\ProductBundle\Manager\AttributeOptionManager;
use Youppers\ProductBundle\Manager\VariantPropertyManager;

class VariantGuesser extends TapVariantGuesser
{
	protected function getCollectionTypeGuesser(ProductCollection $collection, AttributeType $type)
	{
		if ($type->getCode() == 'DIM') {
			return new DimPropertyGuesser($type,$this->variantPropertyManager,$this->attributeOptionManager);
		}
		return parent::getCollectionTypeGuesser($collection, $type);
	}
	
}

class DimPropertyGuesser extends AbstractDimensionPropertyGuesser
{

	public function __construct(AttributeType $type, VariantPropertyManager $variantPropertyManager, AttributeOptionManager $attributeOptionManager)
	{
		parent::__construct($type, $variantPropertyManager, $attributeOptionManager);
		$this->autoAddOptions = true;
	}

	public function guessProperty(ProductVariant $variant, &$text, AttributeType $type, $textIsValue = false)
	{
		$info = $variant->getProduct()->getInfoArray();
		if (array_key_exists('Larghezza',$info) && array_key_exists('Altezza',$info) && array_key_exists('Lunghezza',$info)) {
			$l = $info['Larghezza'];
			$h = $info['Altezza'];
			$p = $info['Lunghezza'];
			$lhp =  $l . 'X' . $h . 'X' . $p;
			$standard = $this->getDefaultStandard($variant,$type);
			$lhp = $this->normalizeValue($lhp,$standard);
		} else {
			$lhp = null;
		}
		if ($lhp != null) {
			return parent::guessProperty($variant,$lhp,$type,true);
		} else {
			return parent::guessProperty($variant,$text,$type,$textIsValue);
		}
	}
	
	public function getDefaultStandardName()
	{
		return 'Larghezza x Altezza x Profondità in centimetri';
	}

	protected function getDimensions()
	{
		return 3;
	}

}
