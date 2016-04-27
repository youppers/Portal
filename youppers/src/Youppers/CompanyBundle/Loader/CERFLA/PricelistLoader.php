<?php
namespace Youppers\CompanyBundle\Loader\CERFLA;

use Youppers\CompanyBundle\Entity\Product;
use Youppers\CompanyBundle\Loader\AbstractPricelistLoader;
use Youppers\CompanyBundle\Loader\LoaderMapper;

class PricelistLoader extends AbstractPricelistLoader
{
	public function createMapper()
	{
		$mapping = array(
            self::FIELD_COLLECTION_CODE => function($row) {
				return substr(trim($row['ART']),0,2);
			},
			self::FIELD_CODE => 'ART',
			self::FIELD_GTIN => 'EAN',
			self::FIELD_NAME => 'DESCRIPTION',
			self::FIELD_PRICE => 'EURO',
		);
		$mapper = new LoaderMapper($mapping);
		return $mapper;
	}

	protected function getProductType(Product $product, $collectionCode) {
		return $this->findProductType('FULLBATH');
	}

}