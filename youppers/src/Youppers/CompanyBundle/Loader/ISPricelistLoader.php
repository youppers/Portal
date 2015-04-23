<?php
namespace Youppers\CompanyBundle\Loader;

use Youppers\CompanyBundle\Entity\Brand;
class ISPricelistLoader extends AbstractPricelistCollectionLoader
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
			$this->newCollectionProductType= $this->getProductTypeRepository()
			->findOneBy(array('code' => 'FULLBATH'));
		}
		return $this->newCollectionProductType;
	}
	
}