<?php
namespace Youppers\CompanyBundle\Loader\SAGOSTINO;

use Youppers\CompanyBundle\Entity\Brand;
use Youppers\CompanyBundle\Loader\AbstractPricelistLoader;
use Youppers\CompanyBundle\Loader\LoaderMapper;

class PricelistLoader extends AbstractPricelistLoader
{
	/**
	 * @return LoaderMapper
	 */
	public function createMapper()
	{
		$mapping = array(
			self::FIELD_CODE => 'Codice Articolo',
			self::FIELD_NAME => 'Prodotto',
			self::FIELD_COLLECTION => 'Serie',
			self::FIELD_UOM => 'Vendita',
			self::FIELD_QUANTITY => 'N.PZ CRT',
			self::FIELD_SURFACE => 'MQ CRT',
			self::FIELD_PRICE => 'Prezzo €',
		);
		$mapper = new LoaderMapper($mapping);
		return $mapper;
	}
	
	private $newCollectionProductType;
	
	protected function getNewCollectionProductType(Brand $brand, $code)
	{
		if (!isset($this->newCollectionProductType)) {
			$this->newCollectionProductType= $this->getProductTypeManager()
				->findOneBy(array('code' => 'TILE'));
		}
		return $this->newCollectionProductType;
	}
	
}