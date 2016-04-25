<?php
namespace Youppers\ProductBundle\Guesser\CAESAR;

use Symfony\Component\Config\Definition\Exception\Exception;
use Youppers\ProductBundle\Guesser\BaseDimensionPropertyGuesser;
use Youppers\ProductBundle\Guesser\BaseVariantGuesser;
use Youppers\ProductBundle\Entity\ProductCollection;
use Youppers\ProductBundle\Entity\AttributeType;
use Youppers\ProductBundle\Guesser\BasePropertyGuesser;
use Youppers\ProductBundle\Entity\ProductVariant;
use Doctrine\Common\Collections\Criteria;
use Youppers\ProductBundle\Guesser\IgnorePropertyGuesser;
use Youppers\ProductBundle\Guesser\TileItemPropertyGuesser;
use Youppers\ProductBundle\Manager\VariantPropertyManager;
use Youppers\ProductBundle\Manager\AttributeOptionManager;

class VariantGuesser extends BaseVariantGuesser
{
	protected function getCollectionTypeGuesser(ProductCollection $collection, AttributeType $type)
	{
		if ($type->getCode() == 'DIM') {
			return new DimPropertyGuesser($type,$this->variantPropertyManager,$this->attributeOptionManager);
		}
		if ($type->getCode() == 'COLOR') {
			return new ColorPropertyGuesser($type,$this->variantPropertyManager,$this->attributeOptionManager);
		}
		if ($type->getCode() == 'EDGE') {
			return new EdgePropertyGuesser($type,$this->variantPropertyManager,$this->attributeOptionManager);
		}
		if ($type->getCode() == 'ITEM') {
			return new ItemPropertyGuesser($type,$this->variantPropertyManager,$this->attributeOptionManager);
		}
		if ($type->getCode() == 'FIN') {
			return new FinPropertyGuesser($type,$this->variantPropertyManager,$this->attributeOptionManager);
		}
		return parent::getCollectionTypeGuesser($collection, $type);
	}
	
}

// SERIE;ASSORTIMENTO;TECNOLOGIA;PEZZO;FORMATO;COLORE_SERIE;COD_9;COD_ART;ARTICOLO;UDM;EUR01>PL (2016);EUR01<PL (2016);MQ_PALLET;KG_PALLET;SCATOLE_EURO;PEZZI_SCATOLA;MQ_SCATOLA;KG_SCATOLA_LORDO

class DimPropertyGuesser extends BaseDimensionPropertyGuesser
{

	public function getTypeColumn()
	{
		return 'FORMATO';
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

class ItemPropertyGuesser extends TileItemPropertyGuesser
{

	public function getTypeColumn()
	{
		return 'PEZZO';
	}

}

class ColorPropertyGuesser extends TileItemPropertyGuesser
{
	public function __construct(AttributeType $type, VariantPropertyManager $variantPropertyManager, AttributeOptionManager $attributeOptionManager)
	{
		parent::__construct($type, $variantPropertyManager, $attributeOptionManager);
		$this->autoAddOptions = true;
	}

	public function getDefaultStandardName()
	{
		return 'Caesar';
	}

	public function getTypeColumn()
	{
		return 'COLORE_SERIE';
	}

}

class FinPropertyGuesser extends TileFPropertyGuesser
{

	public function getDefaultStandardName()
	{
		return 'Superficie Piastrella';
	}

}