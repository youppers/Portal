<?php
namespace Youppers\ProductBundle\Guesser\CCI;

use Symfony\Component\Config\Definition\Exception\Exception;
use Youppers\ProductBundle\Guesser\TileDimPropertyGuesser;
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
		if ($type->getCode() == 'COLOR') {
			return new ColorPropertyGuesser($type,$this->variantPropertyManager,$this->attributeOptionManager);
		}
		if ($type->getCode() == 'DIM') {
			return new DimPropertyGuesser($type,$this->variantPropertyManager,$this->attributeOptionManager);
		}
		if ($type->getCode() == 'FIN') {
			return new FinPropertyGuesser($type,$this->variantPropertyManager,$this->attributeOptionManager);
		}
		if ($type->getCode() == 'EDGE') {
			return new EdgePropertyGuesser($type,$this->variantPropertyManager,$this->attributeOptionManager);
		}
		return parent::getCollectionTypeGuesser($collection, $type);
	}
	
}

class ColorPropertyGuesser extends BasePropertyGuesser
{
	public function __construct(AttributeType $type, VariantPropertyManager $variantPropertyManager, AttributeOptionManager $attributeOptionManager)
	{
		parent::__construct($type, $variantPropertyManager, $attributeOptionManager);
		$this->autoAddOptions = true;
	}

	public function getTypeColumn()
	{
		return 'Colore';
	}

	public function getDefaultStandardName()
	{
		return 'Imola Ceramiche';
	}

}

class DimPropertyGuesser extends TileDimPropertyGuesser
{

	public function getTypeColumn()
	{
		return 'Formato - Size';
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

	public function getTypeColumn()
	{
		return 'Finitura';
	}

	public function getDefaultStandardName()
	{
		// FIXME Move to config
		return 'Superficie Piastrella';
	}

}

class EdgePropertyGuesser extends BasePropertyGuesser
{
	public function __construct(AttributeType $type, VariantPropertyManager $variantPropertyManager, AttributeOptionManager $attributeOptionManager)
	{
		parent::__construct($type, $variantPropertyManager, $attributeOptionManager);
		$this->autoAddOptions = false;
	}

	public function getTypeColumn()
	{
		return 'Bordi';
	}

	public function getDefaultStandardName()
	{
		return 'Bordo Piastrella';
	}

}
