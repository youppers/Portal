<?php
namespace Youppers\CompanyBundle\Loader\BISAZZA;

use Youppers\CompanyBundle\Entity\Product;
use Youppers\CompanyBundle\Loader\AbstractPricelistLoader;
use Youppers\CompanyBundle\Loader\LoaderMapper;

class PricelistLoader extends AbstractPricelistLoader
{
	public function createMapper()
	{
		// Collection;Item;Item code without Installation Kit;Item code with Installation Kit;UM;Without Installation Kit EUR;Installation Kit included EUR;Unità d'ordine ;UM Unità d'Ordine
		$mapping = array(
			self::FIELD_COLLECTION => 'Collection',
			self::FIELD_CODE => function ($row) {
				if ($row['kit']) {
					return $row['Item code with Installation Kit'];
				} else {
					return $row['Item code without Installation Kit'];
				}
			},
			self::FIELD_NAME => 'Item',
			self::FIELD_UOM => 'UM',
			self::FIELD_PRICE => function ($row) {
				if ($row['kit']) {
					return $row['Installation Kit included EUR'];
				} else {
					return $row['Without Installation Kit EUR'];
				}
			},
            self::FIELD_SURFACE => 'Unità d\'ordine ',
		);
		$mapper = new LoaderMapper($mapping);
		return $mapper;
	}

	public function handleRow($row)
	{
		$row['kit'] = false;
		if (!empty($row['Item code without Installation Kit'])) {
            parent::handleRow($row);
        }
		$row['kit'] = true;
        if (!empty($row['Item code with Installation Kit'])) {
            parent::handleRow($row);
        }
	}

    protected function normalizeUom($uom)
    {
        if ($uom == 'LM') { // refuso del listino?
            $uom = 'MQ';
        }
        return parent::normalizeUom($uom);
    }

    protected function getProductType(Product $product, $collectionCode)
	{
		if ($collectionCode == 'COLLANTIEATTREZZI') {
			return $this->findProductType('ALTRI');
		} else {
			return $this->findProductType('TILE');
		}
	}

}