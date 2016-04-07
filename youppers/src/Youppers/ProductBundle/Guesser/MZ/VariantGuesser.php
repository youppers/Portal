<?php
namespace Youppers\ProductBundle\Guesser\MZ;

use Youppers\ProductBundle\Entity\AttributeType;
use Youppers\ProductBundle\Entity\ProductCollection;
use Youppers\ProductBundle\Guesser\BasePropertyGuesser;
use Youppers\ProductBundle\Guesser\BaseVariantGuesser;

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

    public function getDefaultStandardName()
    {
        return 'Lato x Lato in mm';
    }

}
