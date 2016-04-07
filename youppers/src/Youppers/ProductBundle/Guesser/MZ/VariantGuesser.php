<?php
namespace Youppers\ProductBundle\Guesser\MZ;

use Youppers\ProductBundle\Entity\AttributeType;
use Youppers\ProductBundle\Entity\ProductCollection;
use Youppers\ProductBundle\Entity\ProductVariant;
use Youppers\ProductBundle\Guesser\BasePropertyGuesser;
use Youppers\ProductBundle\Guesser\BaseVariantGuesser;
use Youppers\ProductBundle\Manager\AttributeOptionManager;
use Youppers\ProductBundle\Manager\VariantPropertyManager;

class VariantGuesser extends BaseVariantGuesser
{
    protected function getCollectionTypeGuesser(ProductCollection $collection, AttributeType $type)
    {
        if ($type->getCode() == 'DIM') {
            return new DimPropertyGuesser($type, $this->variantPropertyManager, $this->attributeOptionManager);
        }
        return parent::getCollectionTypeGuesser($collection, $type);
    }

}

class DimPropertyGuesser extends BasePropertyGuesser
{

    public function __construct(AttributeType $type, VariantPropertyManager $variantPropertyManager, AttributeOptionManager $attributeOptionManager)
    {
        parent::__construct($type, $variantPropertyManager, $attributeOptionManager);
        $this->autoAddOptions = true;
    }

    public function getDefaultStandardName()
    {
        return 'Lato x Lato in mm';
    }

    public function guessProperty(ProductVariant $variant, &$text, AttributeType $type, $textIsValue = false)
    {
        if (preg_match("/^([0-9]{3,4}X[0-9]{3,4})/",$text,$matches)) {
            $value = $matches[1];
            return parent::guessProperty($variant,$value,$type,true);
        } else {
            return parent::guessProperty($variant,$text,$type,$textIsValue);
        }
    }

}
