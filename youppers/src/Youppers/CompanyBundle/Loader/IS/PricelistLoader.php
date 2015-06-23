<?php
namespace Youppers\CompanyBundle\Loader\IS;

use Youppers\CompanyBundle\Entity\Brand;
use Youppers\CompanyBundle\Loader\AbstractPricelistLoader;
use Youppers\CompanyBundle\Loader\LoaderMapper;

class PricelistLoader extends AbstractPricelistLoader
{
	public function createMapper()
	{
		$mapping = array(
			'code' => 'Material Code',
			'brand' => function($row) { $matches=preg_split("/\-/",$row['CDS Italia']); if (array_key_exists(1,$matches)) return $matches[1]; },
			'name' => 'Description',
			'gtin' => 'EAN13',
			'price' => 'Price',
			'collection' => 'Suite',
			'type' => 'Categoria',	
			'uom' => function($row) { return 'PCE'; }
		);
		$mapper = new LoaderMapper($mapping);
		return $mapper;
	}
	
	private $newCollectionProductType;
	
	protected function getNewCollectionProductType(Brand $brand, $code)
	{
		if (!isset($this->newCollectionProductType)) {
			$this->newCollectionProductType= $this->productTypeManager
				->findOneBy(array('code' => 'FULLBATH'));
		}
		return $this->newCollectionProductType;
	}
	
}